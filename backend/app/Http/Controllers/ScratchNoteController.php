<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SyncsTags;
use App\Models\ScratchNote;
use Illuminate\Http\Request;

class ScratchNoteController extends Controller
{
    use SyncsTags;

    public function index(Request $request)
    {
        return ScratchNote::where('is_archived', $request->boolean('archived'))
            ->orderByDesc('id')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate(['content' => 'required|string']);
        $note = ScratchNote::create($data);
        $this->syncTags($note, $request);
        return response()->json($note->fresh(), 201);
    }

    public function update(Request $request, ScratchNote $scratchNote)
    {
        $data = $request->validate([
            'content' => 'sometimes|string',
            'is_archived' => 'sometimes|boolean',
        ]);
        $scratchNote->update($data);
        $this->syncTags($scratchNote, $request);
        return $scratchNote->fresh();
    }

    // Catatan tidak pernah dihapus otomatis; hapus hanya atas permintaan eksplisit (mis. Yuna).
    public function destroy(ScratchNote $scratchNote)
    {
        $scratchNote->delete();
        return response()->noContent();
    }
}
