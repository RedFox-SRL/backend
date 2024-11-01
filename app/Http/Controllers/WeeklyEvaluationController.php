<?php

namespace App\Http\Controllers;

use App\Models\Sprint;
use App\Models\WeeklyEvaluation;
use App\Models\Task;
use App\Http\Requests\CreateWeeklyEvaluationRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\ApiCode;
use Illuminate\Support\Facades\Auth;

class WeeklyEvaluationController extends Controller
{
    public function getEvaluationTemplate($sprintId)
    {
        try {
            $sprint = Sprint::with(['tasks' => function ($query) {
                $query->where('status', 'done')
                    ->whereDoesntHave('weeklyEvaluations');
            }, 'tasks.assignedTo.user'])
                ->findOrFail($sprintId);

            $template = $this->buildEvaluationTemplate($sprint);

            return $this->respond(['template' => $template]);
        } catch (\Exception $e) {
            return $this->respondBadRequest(ApiCode::EVALUATION_TEMPLATE_RETRIEVAL_FAILED);
        }
    }

    public function create(CreateWeeklyEvaluationRequest $request, $sprintId)
    {
        try {
            $sprint = Sprint::with('group.management')->findOrFail($sprintId);
            $weekNumber = $sprint->getCurrentWeekNumber();

            if ($this->evaluationExists($sprintId, $weekNumber)) {
                return $this->respondBadRequest(ApiCode::EVALUATION_ALREADY_EXISTS);
            }

            $tasksToEvaluate = collect($request->tasks)->pluck('id');
            $sprintTasks = $this->getSprintTasks($sprint->id, $tasksToEvaluate);

            DB::beginTransaction();

            try {
                $weeklyEvaluation = $this->createWeeklyEvaluation($sprint->id, $weekNumber);
                $result = $this->processTasksForEvaluation($weeklyEvaluation, $sprintTasks, $request->tasks, $weekNumber);

                DB::commit();

                return $this->respond([
                    'evaluation' => $weeklyEvaluation->load('tasks'),
                    'processed_tasks' => $result['processed_tasks'],
                    'skipped_tasks' => $result['skipped_tasks']
                ], 'Weekly evaluation created successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->respondBadRequest(ApiCode::EVALUATION_CREATION_FAILED);
            }
        } catch (\Exception $e) {
            return $this->respondBadRequest(ApiCode::SOMETHING_WENT_WRONG);
        }
    }

    public function getWeeklyEvaluations($sprintId)
    {
        try {
            $sprint = Sprint::findOrFail($sprintId);

            if (!$this->userCanViewEvaluations($sprint)) {
                return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
            }

            $evaluations = $this->fetchWeeklyEvaluations($sprintId);

            return $this->respond(['evaluations' => $evaluations]);
        } catch (\Exception $e) {
            return $this->respondBadRequest(ApiCode::EVALUATION_RETRIEVAL_FAILED);
        }
    }

    private function buildEvaluationTemplate($sprint)
    {
        return [
            'sprint_id' => $sprint->id,
            'sprint_title' => $sprint->title,
            'week_number' => $sprint->getCurrentWeekNumber(),
            'total_evaluations' => $sprint->max_evaluations,
            'tasks' => $sprint->tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'assigned_to' => $task->assignedTo->map(function ($student) {
                        return [
                            'id' => $student->id,
                            'name' => $student->user->name,
                            'last_name' => $student->user->last_name,
                        ];
                    }),
                ];
            }),
        ];
    }

    private function evaluationExists($sprintId, $weekNumber)
    {
        return WeeklyEvaluation::where('sprint_id', $sprintId)
            ->where('week_number', $weekNumber)
            ->exists();
    }

    private function getSprintTasks($sprintId, $taskIds)
    {
        return Task::where('sprint_id', $sprintId)
            ->whereIn('id', $taskIds)
            ->get();
    }

    private function createWeeklyEvaluation($sprintId, $weekNumber)
    {
        return WeeklyEvaluation::create([
            'sprint_id' => $sprintId,
            'week_number' => $weekNumber,
            'evaluation_date' => Carbon::now(),
        ]);
    }

    private function processTasksForEvaluation($weeklyEvaluation, $sprintTasks, $requestTasks, $weekNumber)
    {
        $processedTasks = [];
        $skippedTasks = [];

        foreach ($requestTasks as $taskData) {
            $task = $sprintTasks->firstWhere('id', $taskData['id']);

            if ($task->weeklyEvaluations()->exists()) {
                $skippedTasks[] = [
                    'id' => $task->id,
                    'title' => $task->title,
                    'reason' => 'Already reviewed in a previous week'
                ];
                continue;
            }

            $weeklyEvaluation->tasks()->attach($task->id, [
                'comments' => $taskData['comments'],
                'satisfaction_level' => $taskData['satisfaction_level'],
            ]);

            $task->update(['reviewed' => true]);

            $processedTasks[] = [
                'id' => $task->id,
                'title' => $task->title,
                'comments' => $taskData['comments'],
                'satisfaction_level' => $taskData['satisfaction_level']
            ];
        }

        return [
            'processed_tasks' => $processedTasks,
            'skipped_tasks' => $skippedTasks
        ];
    }

    private function fetchWeeklyEvaluations($sprintId)
    {
        return WeeklyEvaluation::where('sprint_id', $sprintId)
            ->with(['tasks' => function ($query) {
                $query->select('tasks.id', 'title', 'description')
                    ->withPivot('comments', 'satisfaction_level');
            }])
            ->orderBy('week_number')
            ->get();
    }

    private function userCanViewEvaluations($sprint)
    {
        $user = Auth::user();

        if ($user->teacher && $sprint->group->management->teacher_id === $user->teacher->id) {
            return true;
        }

        if ($user->student && $sprint->group->students->contains($user->student->id)) {
            return true;
        }

        return false;
    }
}
