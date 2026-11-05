<?php

namespace App\Http\Controllers;

use App\Models\WorkLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WorkLogController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', Carbon::now('Asia/Jakarta')->toDateString());
        return WorkLog::where('date', $date)
            ->orderByRaw('COALESCE(start_time, end_time), id')
            ->get();
    }

    /** Sesi aktif (end_time null) dari hari mana pun, untuk status & banner. */
    public function active()
    {
        return response()->json(WorkLog::whereNull('end_time')->orderByDesc('id')->first());
    }

    /** Tombol "Mulai Kerja". */
    public function start()
    {
        if ($existing = WorkLog::whereNull('end_time')->orderByDesc('id')->first()) {
            return response()->json($existing);
        }
        $now = Carbon::now();
        return response()->json(WorkLog::create([
            'date' => $now->copy()->setTimezone('Asia/Jakarta')->toDateString(),
            'start_time' => $now,
            'activity' => 'Sesi kerja',
            'type' => 'WORK',
        ]), 201);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'activity' => 'required|string|max:255',
            'type' => 'sometimes|in:WORK,ACTIVITY',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $now = Carbon::now();
        // Waktu mulai kosong -> ambil waktu selesai baris sebelumnya hari ini.
        $start = isset($data['start_time']) ? Carbon::parse($data['start_time']) : null;
        $end = array_key_exists('end_time', $data) && $data['end_time']
            ? Carbon::parse($data['end_time']) : $now;
        $date = ($end ?? $now)->copy()->setTimezone('Asia/Jakarta')->toDateString();

        if (! $start) {
            $start = WorkLog::where('date', $date)->whereNotNull('end_time')
                ->orderByDesc('end_time')->orderByDesc('id')->value('end_time');
        }

        return response()->json(WorkLog::create([
            'date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'activity' => $data['activity'],
            'type' => $data['type'] ?? 'ACTIVITY',
            'notes' => $data['notes'] ?? null,
        ]), 201);
    }

    public function update(Request $request, WorkLog $workLog)
    {
        $data = $request->validate([
            'activity' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:WORK,ACTIVITY',
            'start_time' => 'sometimes|nullable|date',
            'end_time' => 'sometimes|nullable|date',
            'notes' => 'sometimes|nullable|string',
        ]);
        $workLog->update([...$data, 'is_manually_edited' => true]);
        return response()->json($workLog->fresh());
    }

    public function destroy(WorkLog $workLog)
    {
        $workLog->delete();
        return response()->noContent();
    }
}
