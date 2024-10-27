<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $sprintId = $request->query('sprint_id');
        return Task::where('sprint_id', $sprintId)->with('assignedTo')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'sprint_id' => 'required|exists:sprints,id',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:students,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,done',
        ]);

        $task = Task::create($request->except('assigned_to'));

        if ($request->has('assigned_to')) {
            $task->assignedTo()->attach($request->assigned_to);
        }

        return $task->load('assignedTo');
    }

    public function show($id)
    {
        return Task::with('assignedTo')->findOrFail($id);
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
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:students,id',
        ]);

        $task->update($request->except('assigned_to'));

        if ($request->has('assigned_to')) {
            $task->assignedTo()->sync($request->assigned_to);
        }

        return $task->load('assignedTo');
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
