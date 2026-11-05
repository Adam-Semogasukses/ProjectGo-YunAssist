<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function index()
    {
        return Folder::orderBy('name')->get();
    }

    public function store(Request $request)
    {
        return response()->json(
            Folder::create($request->validate(['name' => 'required|string|max:255'])),
            201
        );
    }

    public function update(Request $request, Folder $folder)
    {
        $folder->update($request->validate(['name' => 'required|string|max:255']));
        return $folder->fresh();
    }

    // Proyek di dalam folder tidak ikut terhapus (folder_id jadi null).
    public function destroy(Folder $folder)
    {
        $folder->delete();
        return response()->noContent();
    }
}
