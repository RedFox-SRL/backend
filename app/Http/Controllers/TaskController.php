<?php

namespace App\Http\Controllers;

use App\Models\Sprint;
use App\Models\Task;
use App\Models\TaskLink;
use App\Http\Requests\TaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\ApiCode;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $sprintId = $request->query('sprint_id');
        $sprintExists = Sprint::where('id', $sprintId)->exists();

        if (!$sprintId || !$sprintExists) {
            return $this->respondNotFound(ApiCode::SPRINT_NOT_FOUND);
        }

        $tasks = Task::where('sprint_id', $sprintId)->with(['assignedTo', 'links'])->get();
        return $this->respond(['items' => $tasks]);
    }

    public function store(TaskRequest $request)
    {
        $task = Task::create($request->except(['assigned_to', 'links']));

        if ($request->has('assigned_to')) {
            $task->assignedTo()->attach($request->assigned_to);
        }

        if ($request->has('links')) {
            foreach ($request->links as $link) {
                $task->links()->create($link);
            }
        }

        return $this->respond(['item' => $task->load(['assignedTo', 'links'])]);
    }

    public function show($id)
    {
        $task = Task::with(['assignedTo', 'links'])->find($id);

        if (!$task) {
            return $this->respondNotFound(ApiCode::TASK_NOT_FOUND);
        }

        return $this->respond(['item' => $task]);
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return $this->respondNotFound(ApiCode::TASK_NOT_FOUND);
        }

        if ($task->reviewed) {
            return $this->respondUnAuthorizedRequest(ApiCode::TASK_ALREADY_REVIEWED);
        }

        try {
            $task->update($request->except(['assigned_to', 'links']));

            if ($request->has('assigned_to')) {
                $task->assignedTo()->sync($request->assigned_to);
            }

            if ($request->has('links')) {
                $existingLinkIds = $task->links->pluck('id')->toArray();
                $newLinkIds = collect($request->links)->pluck('id')->filter()->toArray();
                $linksToDelete = array_diff($existingLinkIds, $newLinkIds);

                TaskLink::destroy($linksToDelete);

                foreach ($request->links as $link) {
                    if (isset($link['id'])) {
                        $task->links()->where('id', $link['id'])->update($link);
                    } else {
                        $task->links()->create($link);
                    }
                }
            }
        } catch (\Exception $e) {
            return $this->respondBadRequest(ApiCode::TASK_UPDATE_FAILED);
        }

        return $this->respond(['item' => $task->load(['assignedTo', 'links'])]);
    }

    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return $this->respondNotFound(ApiCode::TASK_NOT_FOUND);
        }

        if ($task->reviewed) {
            return $this->respondUnAuthorizedRequest(ApiCode::TASK_ALREADY_REVIEWED);
        }

        try {
            $task->delete();
        } catch (\Exception $e) {
            return $this->respondBadRequest(ApiCode::TASK_DELETE_FAILED);
        }

        return $this->respondWithMessage('Task successfully deleted');
    }
}
