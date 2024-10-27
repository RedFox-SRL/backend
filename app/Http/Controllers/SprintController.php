<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSprintRequest;
use App\Http\Requests\UpdateSprintRequest;
use App\Models\Sprint;
use App\Models\TaskEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SprintController extends Controller
{
    public function index(Request $request)
    {
        $groupId = $request->query('group_id');
        return Sprint::where('group_id', $groupId)->with('tasks')->get();
    }

    public function store(StoreSprintRequest $request)
    {
        return Sprint::create($request->all());
    }

    public function show($id)
    {
        return Sprint::with('tasks')->findOrFail($id);
    }

    public function update(UpdateSprintRequest $request, $id)
    {
        $sprint = Sprint::findOrFail($id);
        $sprint->update($request->all());

        return $sprint;
    }

    public function destroy($id)
    {
        $sprint = Sprint::findOrFail($id);
        $sprint->delete();

        return response()->noContent();
    }


    public function getEvaluationTemplate($id)
    {
        $management = $this->getManagementForAuthenticatedTeacherBySprint($id);
        if ($management instanceof \Illuminate\Http\JsonResponse) {
            return $management;
        }

        $sprint = Sprint::with(['tasks' => function ($query) {
            $query->where('status', 'done')
                ->whereNull('deleted_at')
                ->whereDoesntHave('evaluation')
                ->with('assignedTo:id,user_id', 'assignedTo.user:id,name,last_name');
        }])->findOrFail($id);

        return response()->json($sprint);
    }

    private function getManagementForAuthenticatedTeacherBySprint($sprintId)
    {
        $sprint = Sprint::findOrFail($sprintId);
        $group = $sprint->group;
        $management = $group->management;

        $teacher = $this->getAuthenticatedTeacher();
        if ($teacher instanceof \Illuminate\Http\JsonResponse) {
            return $teacher;
        }

        $isValid = $this->validateTeacherForManagement($management, $teacher);
        if ($isValid instanceof \Illuminate\Http\JsonResponse) {
            return $isValid;
        }

        return $management;
    }

    private function getAuthenticatedTeacher()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $teacher = $user->teacher;
        if (!$teacher) {
            return response()->json(['message' => 'User is not a teacher'], 403);
        }

        return $teacher;
    }

    private function validateTeacherForManagement($management, $teacher)
    {
        if ($management->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return true;
    }

    public function getEvaluatedTasks($id)
    {
        $sprint = Sprint::with(['tasks' => function ($query) {
            $query->has('evaluation')
                ->with('evaluation.evaluatedBy:id,name,last_name', 'assignedTo:id,user_id', 'assignedTo.user:id,name,last_name');
        }])->findOrFail($id);

        return response()->json($sprint);
    }

    public function submitEvaluation(Request $request, $id)
    {
        $management = $this->getManagementForAuthenticatedTeacherBySprint($id);
        if ($management instanceof \Illuminate\Http\JsonResponse) {
            return $management;
        }

        $sprint = Sprint::findOrFail($id);
        $evaluations = $request->input('evaluations');

        DB::beginTransaction();

        try {
            foreach ($evaluations as $taskId => $evaluation) {
                TaskEvaluation::create([
                    'task_id' => $taskId,
                    'grade' => $evaluation['grade'],
                    'comment' => $evaluation['comment'],
                    'evaluated_by' => auth()->id()
                ]);

                $task = $sprint->tasks()->findOrFail($taskId);
                $task->reviewed = true;
            }

            DB::commit();
            return response()->json(['message' => 'Evaluations submitted successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error submitting evaluations'], 500);
        }
    }
}
