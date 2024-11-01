<?php

namespace App\Http\Controllers;

use App\Models\Sprint;
use App\Models\WeeklyEvaluation;
use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\ApiCode;

class WeeklyEvaluationController extends Controller
{
    public function getEvaluationTemplate($sprintId)
    {
        $sprint = Sprint::with(['tasks' => function ($query) {
            $query->where('status', 'done')
                ->whereDoesntHave('weeklyEvaluations', function ($query) {
                    $query->where('week_number', $this->getCurrentWeekNumber());
                });
        }, 'tasks.assignedTo.user'])
            ->findOrFail($sprintId);

        $startDate = Carbon::parse($sprint->start_date);
        $endDate = Carbon::parse($sprint->end_date);
        $currentDate = Carbon::now();

        $fullWeeks = $startDate->diffInWeeks($endDate);
        $remainingDays = $startDate->addWeeks($fullWeeks)->diffInDays($endDate);
        $totalEvaluations = $fullWeeks + ($remainingDays > 0 ? 1 : 0);

        $weekNumber = min($startDate->diffInWeeks($currentDate) + 1, $totalEvaluations);

        $template = [
            'sprint_id' => $sprint->id,
            'sprint_title' => $sprint->title,
            'week_number' => $weekNumber,
            'total_evaluations' => $totalEvaluations,
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

        return response()->json($template);
    }

    public function create(Request $request, $sprintId)
    {
        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.comments' => 'required|string',
            'tasks.*.satisfaction_level' => 'required|integer|min:1|max:5',
        ]);

        $sprint = Sprint::findOrFail($sprintId);

        if ($sprint->group->management->teacher_id !== auth()->user()->teacher->id) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        $startDate = Carbon::parse($sprint->start_date);
        $endDate = Carbon::parse($sprint->end_date);
        $currentDate = Carbon::now();

        $fullWeeks = $startDate->diffInWeeks($endDate);
        $remainingDays = $startDate->addWeeks($fullWeeks)->diffInDays($endDate);
        $totalEvaluations = $fullWeeks + ($remainingDays > 0 ? 1 : 0);

        $weekNumber = min($startDate->diffInWeeks($currentDate) + 1, $totalEvaluations);

        $existingEvaluation = WeeklyEvaluation::where('sprint_id', $sprintId)
            ->where('week_number', $weekNumber)
            ->first();

        if ($existingEvaluation) {
            return $this->respondBadRequest(ApiCode::EVALUATION_ALREADY_EXISTS);
        }

        if ($weekNumber > $totalEvaluations) {
            return $this->respondBadRequest(ApiCode::EVALUATION_PERIOD_ENDED);
        }

        DB::beginTransaction();

        try {
            $weeklyEvaluation = WeeklyEvaluation::create([
                'sprint_id' => $sprint->id,
                'week_number' => $weekNumber,
                'evaluation_date' => $currentDate,
            ]);

            foreach ($request->tasks as $taskData) {
                $task = Task::findOrFail($taskData['id']);

                if ($task->sprint_id !== $sprint->id || $task->status !== 'done') {
                    throw new \Exception('Invalid task for evaluation');
                }

                $weeklyEvaluation->tasks()->attach($task->id, [
                    'comments' => $taskData['comments'],
                    'satisfaction_level' => $taskData['satisfaction_level'],
                ]);
            }

            DB::commit();

            return $this->respond([
                'message' => 'Weekly evaluation created successfully',
                'evaluation' => $weeklyEvaluation->load('tasks')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondBadRequest(ApiCode::EVALUATION_CREATION_FAILED, $e->getMessage());
        }
    }

    public function getWeeklyEvaluations($sprintId)
    {
        $sprint = Sprint::findOrFail($sprintId);

        if (!$this->userCanViewEvaluations($sprint)) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        $evaluations = WeeklyEvaluation::where('sprint_id', $sprintId)
            ->with(['tasks' => function ($query) {
                $query->select('tasks.id', 'title', 'description')
                    ->withPivot('comments', 'satisfaction_level');
            }])
            ->orderBy('week_number')
            ->get();

        return $this->respond(['evaluations' => $evaluations]);
    }

    private function getCurrentWeekNumber()
    {
        return Carbon::now()->weekOfYear;
    }

    private function userCanViewEvaluations($sprint)
    {
        $user = auth()->user();

        if ($user->teacher && $sprint->group->management->teacher_id === $user->teacher->id) {
            return true;
        }

        if ($user->student && $sprint->group->students->contains($user->student->id)) {
            return true;
        }

        return false;
    }
}
