<?php

namespace App\Http\Controllers;

use App\Models\Sprint;
use App\Models\SprintEvaluation;
use App\Http\Requests\CreateSprintEvaluationRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\ApiCode;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SprintEvaluationController extends Controller
{
    public function getEvaluationTemplate($sprintId)
    {
        try {
            $sprint = $this->getSprintWithRelations($sprintId);

            if ($sprint->sprintEvaluation) {
                return $this->respondBadRequest(ApiCode::SPRINT_EVALUATION_ALREADY_EXISTS);
            }

            $template = $this->buildEvaluationTemplate($sprint);
            return $this->respond(['template' => $template], 'Evaluation template retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound(ApiCode::SPRINT_NOT_FOUND);
        } catch (Exception $e) {
            return $this->respondBadRequest(ApiCode::EVALUATION_TEMPLATE_RETRIEVAL_FAILED);
        }
    }

    public function create(CreateSprintEvaluationRequest $request, $sprintId)
    {
        DB::beginTransaction();
        try {
            $sprint = Sprint::findOrFail($sprintId);

            if ($sprint->sprintEvaluation) {
                return $this->respondBadRequest(ApiCode::SPRINT_EVALUATION_ALREADY_EXISTS);
            }

            $now = Carbon::now();
            $sprintEndDate = Carbon::parse($sprint->end_date);
            $evaluationStartDate = $sprintEndDate->copy()->subDays(4);

            if ($now->lt($evaluationStartDate)) {
                return $this->respondBadRequest(ApiCode::SPRINT_EVALUATION_TOO_EARLY);
            }

            if ($sprint->weeklyEvaluations()->count() === 0) {
                return $this->respondBadRequest(ApiCode::NO_WEEKLY_EVALUATIONS);
            }

            $sprintEvaluation = SprintEvaluation::create([
                'sprint_id' => $sprint->id,
                'summary' => $request->summary,
            ]);

            foreach ($request->student_grades as $gradeData) {
                $student = $sprint->group->students()->find($gradeData['student_id']);
                if (!$student) {
                    return $this->respondBadRequest(ApiCode::STUDENT_NOT_IN_GROUP);
                }

                if ($gradeData['grade'] > $sprint->percentage) {
                    return $this->respondBadRequest(ApiCode::GRADE_EXCEEDS_SPRINT_PERCENTAGE);
                }

                $completedTasksCount = $sprint->tasks()
                    ->where('status', 'done')
                    ->whereHas('assignedTo', function ($query) use ($student) {
                        $query->where('student_id', $student->id);
                    })->count();

                if ($completedTasksCount === 0) {
                    return $this->respondBadRequest(ApiCode::STUDENT_NO_COMPLETED_TASKS);
                }

                $sprintEvaluation->studentGrades()->create([
                    'student_id' => $gradeData['student_id'],
                    'grade' => $gradeData['grade'],
                    'comments' => $gradeData['comments'] ?? null,
                ]);
            }

            foreach ($request->strengths as $strength) {
                $sprintEvaluation->points()->create([
                    'type' => 'strength',
                    'description' => $strength,
                ]);
            }

            foreach ($request->weaknesses as $weakness) {
                $sprintEvaluation->points()->create([
                    'type' => 'weakness',
                    'description' => $weakness,
                ]);
            }

            DB::commit();
            return $this->respond(
                ['evaluation' => $sprintEvaluation->load('studentGrades', 'points')],
                'Sprint evaluation created successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondBadRequest(ApiCode::EVALUATION_CREATION_FAILED);
        }
    }

    public function getFinalEvaluation($sprintId)
    {
        try {
            $sprint = $this->getSprintWithRelations($sprintId);

            $user = auth()->user();
            $canView = ($user->teacher && $sprint->group->management->teacher_id === $user->teacher->id) ||
                ($user->student && $sprint->group->students->contains($user->student->id));

            if (!$canView) {
                return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
            }

            if (!$sprint->sprintEvaluation) {
                return $this->respondNotFound(ApiCode::SPRINT_EVALUATION_NOT_FOUND);
            }

            $evaluation = $this->buildFinalEvaluation($sprint);
            return $this->respond(['evaluation' => $evaluation], 'Final evaluation retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound(ApiCode::SPRINT_NOT_FOUND);
        } catch (Exception $e) {
            return $this->respondBadRequest(ApiCode::EVALUATION_RETRIEVAL_FAILED);
        }
    }

    private function getSprintWithRelations($sprintId)
    {
        return Sprint::with([
            'group.students.user',
            'weeklyEvaluations.tasks',
            'tasks.assignedTo.user',
            'tasks.weeklyEvaluations',
            'tasks.links',
            'sprintEvaluation.studentGrades.student.user',
            'sprintEvaluation.points'
        ])->findOrFail($sprintId);
    }

    private function buildEvaluationTemplate($sprint)
    {
        return [
            'sprint_id' => $sprint->id,
            'sprint_title' => $sprint->title,
            'start_date' => $sprint->start_date,
            'end_date' => $sprint->end_date,
            'percentage' => $sprint->percentage,
            'planned_features' => $sprint->features,
            'overall_progress' => $this->getOverallProgress($sprint),
            'student_summaries' => $this->getStudentSummaries($sprint),
            'weekly_evaluations_summary' => $this->getWeeklyEvaluationsSummary($sprint),
        ];
    }

    private function buildFinalEvaluation($sprint)
    {
        $evaluation = $sprint->sprintEvaluation;
        $evaluation->planned_features = $sprint->features;
        $evaluation->overall_progress = $this->getOverallProgress($sprint);
        $evaluation->student_summaries = $this->getStudentSummaries($sprint);
        $evaluation->weekly_evaluations_summary = $this->getWeeklyEvaluationsSummary($sprint);
        $evaluation->strengths = $evaluation->points->where('type', 'strength')->pluck('description');
        $evaluation->weaknesses = $evaluation->points->where('type', 'weakness')->pluck('description');

        return $evaluation;
    }

    private function getOverallProgress($sprint)
    {
        $totalTasks = $sprint->tasks->count();
        $completedTasks = $sprint->tasks->where('status', 'done')->count();

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $sprint->tasks->where('status', 'in_progress')->count(),
            'todo_tasks' => $sprint->tasks->where('status', 'todo')->count(),
            'completion_percentage' => $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0,
        ];
    }

    private function getStudentSummaries($sprint)
    {
        return $sprint->group->students->map(function ($student) use ($sprint) {
            $studentTasks = $sprint->tasks->filter(function ($task) use ($student) {
                return $task->assignedTo->contains($student);
            });

            $weeklyEvaluations = $sprint->weeklyEvaluations->flatMap->tasks->filter(function ($task) use ($student) {
                return $task->assignedTo->contains($student);
            });

            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'last_name' => $student->user->last_name,
                'tasks_summary' => $this->getTasksSummary($studentTasks),
                'satisfaction_levels' => $this->getSatisfactionLevels($weeklyEvaluations),
                'weekly_performance' => $this->getStudentWeeklyPerformance($student, $sprint),
                'task_details' => $this->getStudentTaskDetails($student, $studentTasks),
            ];
        });
    }

    private function getTasksSummary($tasks)
    {
        return [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', 'done')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'todo' => $tasks->where('status', 'todo')->count(),
        ];
    }

    private function getSatisfactionLevels($evaluations)
    {
        return [
            'average' => $evaluations->avg('pivot.satisfaction_level'),
            'min' => $evaluations->min('pivot.satisfaction_level'),
            'max' => $evaluations->max('pivot.satisfaction_level'),
        ];
    }

    private function getStudentWeeklyPerformance($student, $sprint)
    {
        return $sprint->weeklyEvaluations->map(function ($weeklyEval) use ($student) {
            $studentTasksInWeek = $weeklyEval->tasks->filter(function ($task) use ($student) {
                return $task->assignedTo->contains($student);
            });
            return [
                'week_number' => $weeklyEval->week_number,
                'tasks_evaluated' => $studentTasksInWeek->count(),
                'average_satisfaction' => $studentTasksInWeek->avg('pivot.satisfaction_level'),
            ];
        });
    }

    private function getStudentTaskDetails($student, $tasks)
    {
        return $tasks->map(function ($task) {
            $weeklyEval = $task->weeklyEvaluations->sortByDesc('evaluation_date')->first();
            return [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'satisfaction_level' => $weeklyEval ? $weeklyEval->pivot->satisfaction_level : null,
                'comments' => $weeklyEval ? $weeklyEval->pivot->comments : null,
                'links' => $task->links->map(function ($link) {
                    return [
                        'url' => $link->url,
                        'description' => $link->description,
                    ];
                }),
            ];
        });
    }

    private function getWeeklyEvaluationsSummary($sprint)
    {
        return $sprint->weeklyEvaluations->map(function ($weeklyEval) {
            return [
                'week_number' => $weeklyEval->week_number,
                'evaluation_date' => $weeklyEval->evaluation_date,
                'tasks_evaluated' => $weeklyEval->tasks->count(),
                'average_satisfaction' => $weeklyEval->tasks->avg('pivot.satisfaction_level'),
                'min_satisfaction' => $weeklyEval->tasks->min('pivot.satisfaction_level'),
                'max_satisfaction' => $weeklyEval->tasks->max('pivot.satisfaction_level'),
            ];
        });
    }
}
