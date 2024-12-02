<?php

namespace App\Http\Controllers;

use App\ApiCode;
use App\Models\Group;
use App\Models\Sprint;
use App\Models\Task;
use App\Http\Requests\StoreSprintRequest;
use App\Http\Requests\UpdateSprintRequest;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    public function index(Request $request)
    {
        $groupId = $request->query('group_id');
        $sprints = Sprint::where('group_id', $groupId)
            ->with(['tasks', 'weeklyEvaluations', 'sprintEvaluation'])
            ->get();
        return response()->json($sprints);
    }

    public function store(StoreSprintRequest $request)
    {
        $group = Group::findOrFail($request->group_id);

        $totalPercentage = $group->sprints()->sum('percentage') + $request->percentage;

        if ($totalPercentage > 100) {
            return $this->respondBadRequest(ApiCode::SPRINT_PERCENTAGE_EXCEEDED);
        }

        $sprint = Sprint::create($request->validated());
        return response()->json($sprint, 201);
    }

    public function show($id)
    {
        $sprint = Sprint::with(['tasks', 'weeklyEvaluations', 'sprintEvaluation'])
            ->findOrFail($id);
        return response()->json($sprint);
    }

    public function update(UpdateSprintRequest $request, $id)
    {
        $sprint = Sprint::findOrFail($id);
        $sprint->update($request->validated());
        return response()->json($sprint);
    }

    public function destroy($id)
    {
        $sprint = Sprint::findOrFail($id);
        $sprint->delete();
        return response()->json(null, 204);
    }

    public function getTasks($id)
    {
        $sprint = Sprint::findOrFail($id);
        $tasks = $sprint->tasks()
            ->with(['weeklyEvaluations' => function ($query) {
                $query->orderBy('evaluation_date', 'desc');
            }])
            ->get();
        return response()->json($tasks);
    }

    public function getEvaluationSummary($id)
    {
        $sprint = Sprint::with([
            'weeklyEvaluations.tasks',
            'sprintEvaluation.studentGrades.student.user'
        ])->findOrFail($id);

        $summary = [
            'sprint' => $sprint->only(['id', 'title', 'start_date', 'end_date', 'percentage', 'features']),
            'weekly_evaluations' => $sprint->weeklyEvaluations->map(function ($weeklyEval) {
                return [
                    'week_number' => $weeklyEval->week_number,
                    'evaluation_date' => $weeklyEval->evaluation_date,
                    'tasks' => $weeklyEval->tasks->map(function ($task) use ($weeklyEval) {
                        return [
                            'id' => $task->id,
                            'title' => $task->title,
                            'comments' => $task->pivot->comments,
                            'satisfaction_level' => $task->pivot->satisfaction_level,
                        ];
                    }),
                ];
            }),
            'final_evaluation' => $sprint->sprintEvaluation ? [
                'summary' => $sprint->sprintEvaluation->summary,
                'student_grades' => $sprint->sprintEvaluation->studentGrades->map(function ($grade) {
                    return [
                        'student_name' => $grade->student->user->name . ' ' . $grade->student->user->last_name,
                        'grade' => $grade->grade,
                        'comments' => $grade->comments,
                    ];
                }),
            ] : null,
        ];

        return response()->json($summary);
    }

    public function finishSprint(Sprint $sprint)
    {
        if ($sprint->end_date->isFuture()) {
            return $this->respondBadRequest(ApiCode::SPRINT_NOT_ENDED);
        }

        return $this->respondWithMessage('Sprint finished and evaluations activated.');
    }
}
