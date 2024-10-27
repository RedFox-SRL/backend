<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $sprintId = $request->query('sprint_id');
        return Task::where('sprint_id', $sprintId)->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'sprint_id' => 'required|exists:sprints,id',
            'assigned_to' => 'nullable|exists:students,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,done',
        ]);

        return Task::create($request->all());
    }

    public function show($id)
    {
        return Task::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        if ($task->reviewed) {
            return response()->json(['message' => 'This task has been reviewed and cannot be edited'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,done',
        ]);
        $task->update($request->all());

        return $task;
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        if ($task->reviewed) {
            return response()->json(['message' => 'This task has been reviewed and cannot be deleted'], 403);
        }

        $task->delete();

        return response()->noContent();
    }
}
