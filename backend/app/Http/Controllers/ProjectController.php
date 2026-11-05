<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        return Project::orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'folder_id' => 'nullable|exists:folders,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color_code' => 'sometimes|string|max:16',
        ]);
        return response()->json(Project::create($data), 201);
    }

    public function update(Request $request, Project $project)
    {
        $project->update($request->validate([
            'folder_id' => 'sometimes|nullable|exists:folders,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'color_code' => 'sometimes|string|max:16',
        ]));
        return $project->fresh();
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return response()->noContent();
    }
}
