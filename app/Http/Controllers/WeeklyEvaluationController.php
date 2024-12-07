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
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WeeklyEvaluationController extends Controller
{
    public function getEvaluationTemplate($sprintId)
    {
        try {
            $sprint = $this->getSprintWithTasks($sprintId);
            $template = $this->buildEvaluationTemplate($sprint);
            return $this->respond(['template' => $template], 'Evaluation template retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound(ApiCode::SPRINT_NOT_FOUND);
        } catch (Exception $e) {
            return $this->respondBadRequest(ApiCode::EVALUATION_TEMPLATE_RETRIEVAL_FAILED);
        }
    }

    public function create(CreateWeeklyEvaluationRequest $request, $sprintId)
    {
        DB::beginTransaction();
        try {
            $sprint = Sprint::with('group.management')->findOrFail($sprintId);
            $weekNumber = $sprint->getCurrentWeekNumber();

            $this->validateEvaluationCreation($sprint, $weekNumber);

            $sprintTasks = $this->getSprintTasks($sprint->id, collect($request->tasks)->pluck('id'));
            $weeklyEvaluation = $this->createWeeklyEvaluation($sprint->id, $weekNumber);
            $result = $this->processTasksForEvaluation($weeklyEvaluation, $sprintTasks, $request->tasks);

            DB::commit();

            return $this->respond([
                'evaluation' => $this->formatEvaluation($weeklyEvaluation->load(['tasks', 'evaluator:id,name,last_name,email,role'])),
                'processed_tasks' => $result['processed_tasks'],
                'skipped_tasks' => $result['skipped_tasks']
            ], 'Evaluación semanal creada con éxito');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->respondNotFound(ApiCode::SPRINT_NOT_FOUND);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->respondBadRequest(ApiCode::SOMETHING_WENT_WRONG);
        }
    }

    public function getWeeklyEvaluations($sprintId)
    {
        try {
            $sprint = Sprint::findOrFail($sprintId);
            $this->authorizeViewEvaluations($sprint);
            $evaluations = $this->fetchWeeklyEvaluations($sprintId);
            return $this->respond(['evaluations' => $this->formatEvaluations($evaluations)], 'Evaluaciones semanales recuperadas con éxito');
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound(ApiCode::SPRINT_NOT_FOUND);
        } catch (Exception $e) {
            if ($e->getCode() == ApiCode::UNAUTHORIZED) {
                return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
            }
            return $this->respondBadRequest(ApiCode::EVALUATION_RETRIEVAL_FAILED);
        }
    }

    private function getSprintWithTasks($sprintId)
    {
        return Sprint::with(['tasks' => function ($query) {
            $query->where('status', 'done')
                ->whereDoesntHave('weeklyEvaluations');
        }, 'tasks.assignedTo.user', 'tasks.links'])
            ->findOrFail($sprintId);
    }

    private function buildEvaluationTemplate($sprint)
    {
        return [
            'sprint_id' => $sprint->id,
            'sprint_title' => $sprint->title,
            'week_number' => $sprint->getCurrentWeekNumber(),
            'total_evaluations' => $sprint->max_evaluations,
            'features' => $sprint->features,
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
                            'email' => $student->user->email,
                        ];
                    }),
                    'links' => $task->links->map(function ($link) {
                        return [
                            'id' => $link->id,
                            'url' => $link->url,
                            'description' => $link->description,
                        ];
                    }),
                ];
            }),
        ];
    }

    private function validateEvaluationCreation($sprint, $weekNumber)
    {
        if ($this->evaluationExists($sprint->id, $weekNumber)) {
            throw new Exception(ApiCode::EVALUATION_ALREADY_EXISTS);
        }

        if ($weekNumber > $sprint->max_evaluations) {
            throw new Exception(ApiCode::MAX_EVALUATIONS_REACHED);
        }

        if (Carbon::now()->gt($sprint->end_date)) {
            throw new Exception(ApiCode::SPRINT_ENDED);
        }
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
            ->with(['assignedTo.user', 'links'])
            ->get();
    }

    private function createWeeklyEvaluation($sprintId, $weekNumber)
    {
        $user = Auth::user();
        if ($user->role !== 'teacher' || !$user->teacher) {
            throw new Exception(ApiCode::UNAUTHORIZED);
        }

        return WeeklyEvaluation::create([
            'sprint_id' => $sprintId,
            'evaluator_id' => $user->id,
            'week_number' => $weekNumber,
            'evaluation_date' => Carbon::now(),
        ]);
    }

    private function processTasksForEvaluation($weeklyEvaluation, $sprintTasks, $requestTasks)
    {
        $processedTasks = [];
        $skippedTasks = [];

        foreach ($requestTasks as $taskData) {
            $task = $sprintTasks->firstWhere('id', $taskData['id']);

            if (!$task) {
                $skippedTasks[] = $this->createSkippedTaskEntry($taskData['id'], 'Tarea no encontrada en el sprint');
                continue;
            }

            if ($task->weeklyEvaluations()->exists()) {
                $skippedTasks[] = $this->createSkippedTaskEntry($task->id, 'Ya revisado en una semana anterior');
                continue;
            }

            $this->attachTaskToEvaluation($weeklyEvaluation, $task, $taskData);
            $processedTasks[] = $this->createProcessedTaskEntry($task, $taskData);
        }

        return [
            'processed_tasks' => $processedTasks,
            'skipped_tasks' => $skippedTasks
        ];
    }

    private function createSkippedTaskEntry($taskId, $reason)
    {
        return [
            'id' => $taskId,
            'reason' => $reason
        ];
    }

    private function attachTaskToEvaluation($weeklyEvaluation, $task, $taskData)
    {
        $weeklyEvaluation->tasks()->attach($task->id, [
            'comments' => $taskData['comments'],
            'satisfaction_level' => $taskData['satisfaction_level'],
        ]);

        $task->update(['reviewed' => true]);
    }

    private function createProcessedTaskEntry($task, $taskData)
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'comments' => $taskData['comments'],
            'satisfaction_level' => $taskData['satisfaction_level']
        ];
    }

    private function fetchWeeklyEvaluations($sprintId)
    {
        return WeeklyEvaluation::where('sprint_id', $sprintId)
            ->with([
                'tasks' => function ($query) {
                    $query->select('tasks.id', 'title', 'description', 'status')
                        ->withPivot('comments', 'satisfaction_level');
                },
                'tasks.assignedTo.user:id,name,last_name,email',
                'tasks.links',
                'evaluator:id,name,last_name,email,role'
            ])
            ->orderBy('week_number')
            ->get();
    }

    private function authorizeViewEvaluations($sprint)
    {
        $user = Auth::user();

        $canView = ($user->teacher && $sprint->group->management->teacher_id === $user->teacher->id) ||
            ($user->student && $sprint->group->students->contains($user->student->id));

        if (!$canView) {
            throw new Exception(ApiCode::UNAUTHORIZED);
        }
    }

    private function formatEvaluations($evaluations)
    {
        return $evaluations->map(function ($evaluation) {
            return $this->formatEvaluation($evaluation);
        });
    }

    private function formatEvaluation($evaluation)
    {
        return [
            'id' => $evaluation->id,
            'sprint_id' => $evaluation->sprint_id,
            'week_number' => $evaluation->week_number,
            'evaluation_date' => $evaluation->evaluation_date,
            'evaluator' => [
                'id' => $evaluation->evaluator->id,
                'name' => $evaluation->evaluator->name,
                'last_name' => $evaluation->evaluator->last_name,
                'email' => $evaluation->evaluator->email,
                'role' => $evaluation->evaluator->role,
            ],
            'tasks' => $evaluation->tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'comments' => $task->pivot->comments,
                    'satisfaction_level' => $task->pivot->satisfaction_level,
                    'assigned_to' => $task->assignedTo->map(function ($student) {
                        return [
                            'id' => $student->id,
                            'name' => $student->user->name,
                            'last_name' => $student->user->last_name,
                            'email' => $student->user->email,
                        ];
                    }),
                    'links' => $task->links->map(function ($link) {
                        return [
                            'id' => $link->id,
                            'url' => $link->url,
                            'description' => $link->description,
                        ];
                    }),
                ];
            }),
        ];
    }
}
