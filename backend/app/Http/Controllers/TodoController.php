<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SyncsTags;
use App\Models\Todo;
use App\Models\WorkLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TodoController extends Controller
{
    use SyncsTags;

    public function index(Request $request)
    {
        $q = Todo::query()->orderByRaw("status = 'done'")->orderBy('id', 'desc');
        if ($request->filled('project_id')) {
            $q->where('project_id', $request->integer('project_id'));
        }
        if ($request->filled('tag')) {
            $q->whereHas('tags', fn ($t) => $t->where('slug', $request->query('tag')));
        }
        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'priority' => 'sometimes|in:primary,secondary,tertiary',
        ]);
        $todo = Todo::create($data);
        $this->syncTags($todo, $request);
        return response()->json($todo->fresh(), 201);
    }

    public function update(Request $request, Todo $todo): JsonResponse
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'priority' => 'sometimes|in:primary,secondary,tertiary',
            'status' => 'sometimes|in:pending,done',
        ]);

        $this->syncTags($todo, $request);

        $newStatus = $data['status'] ?? $todo->status;

        // Uncheck setelah Done: hapus log terkait, wajib konfirmasi.
        if ($todo->status === 'done' && $newStatus === 'pending') {
            $log = WorkLog::where('todo_id', $todo->id)->first();
            if ($log && ! $request->boolean('confirm')) {
                return response()->json([
                    'message' => 'Baris log terkait akan dihapus. Kirim confirm=true untuk melanjutkan.',
                    'requires_confirmation' => true,
                ], 409);
            }
            DB::transaction(function () use ($todo, $data, $log) {
                $log?->delete();
                $todo->update([...$data, 'completed_at' => null]);
            });
            return response()->json($todo->fresh());
        }

        // Pending -> Done: auto-sync ke work log.
        if ($todo->status === 'pending' && $newStatus === 'done') {
            DB::transaction(function () use ($todo, $data) {
                $now = Carbon::now();
                $todo->update([...$data, 'status' => 'done', 'completed_at' => $now]);
                $this->syncToLog($todo, $now);
            });
            return response()->json($todo->fresh());
        }

        $todo->update($data);
        return response()->json($todo->fresh());
    }

    public function destroy(Todo $todo)
    {
        $todo->delete();
        return response()->noContent();
    }

    private function syncToLog(Todo $todo, Carbon $now): void
    {
        $today = $now->copy()->setTimezone('Asia/Jakarta')->toDateString();

        // Jika ada sesi aktif (--.--) yang belum diedit manual, tutup sesi itu
        // jadi baris todo ini (start = waktu Mulai Kerja).
        $active = WorkLog::whereNull('end_time')->where('is_manually_edited', false)
            ->whereNull('todo_id')->where('date', $today)->orderByDesc('id')->first();

        if ($active) {
            $active->update([
                'todo_id' => $todo->id,
                'activity' => $todo->title,
                'type' => 'WORK',
                'end_time' => $now,
            ]);
            return;
        }

        $prev = WorkLog::where('date', $today)->whereNotNull('end_time')
            ->orderByDesc('end_time')->orderByDesc('id')->first();

        WorkLog::create([
            'todo_id' => $todo->id,
            'date' => $today,
            'start_time' => $prev?->end_time, // null jika log kosong / tanpa sesi
            'end_time' => $now,
            'activity' => $todo->title,
            'type' => 'WORK',
        ]);
    }
}
