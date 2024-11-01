<?php

namespace App\Http\Controllers;

use App\Models\Sprint;
use App\Models\SprintEvaluation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\ApiCode;

class SprintEvaluationController extends Controller
{
    public function getEvaluationTemplate($sprintId)
    {
        $sprint = Sprint::with(['group.students.user', 'weeklyEvaluations.tasks'])
            ->findOrFail($sprintId);

        $template = [
            'sprint_id' => $sprint->id,
            'sprint_title' => $sprint->title,
            'features' => $sprint->features,
            'percentage' => $sprint->percentage,
            'group_members' => $sprint->group->students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'last_name' => $student->user->last_name,
                ];
            }),
            'weekly_evaluations_summary' => $sprint->weeklyEvaluations->map(function ($weeklyEval) {
                return [
                    'week_number' => $weeklyEval->week_number,
                    'tasks_evaluated' => $weeklyEval->tasks->count(),
                    'average_satisfaction' => $weeklyEval->tasks->avg('pivot.satisfaction_level'),
                ];
            }),
        ];

        return response()->json($template);
    }

    public function create(Request $request, $sprintId)
    {
        $request->validate([
            'summary' => 'required|string',
            'student_grades' => 'required|array',
            'student_grades.*.student_id' => 'required|exists:students,id',
            'student_grades.*.grade' => 'required|numeric|min:0',
            'student_grades.*.comments' => 'nullable|string',
        ]);

        $sprint = Sprint::findOrFail($sprintId);

        if ($sprint->group->management->teacher_id !== auth()->user()->teacher->id) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        if ($sprint->sprintEvaluation) {
            return $this->respondBadRequest(ApiCode::EVALUATION_ALREADY_EXISTS);
        }

        $maxGrade = $sprint->percentage;

        DB::beginTransaction();

        try {
            $sprintEvaluation = SprintEvaluation::create([
                'sprint_id' => $sprint->id,
                'summary' => $request->summary,
            ]);

            foreach ($request->student_grades as $gradeData) {
                if ($gradeData['grade'] > $maxGrade) {
                    throw new \Exception("Grade cannot exceed the sprint's percentage value of {$maxGrade}");
                }

                $sprintEvaluation->studentGrades()->create([
                    'student_id' => $gradeData['student_id'],
                    'grade' => $gradeData['grade'],
                    'comments' => $gradeData['comments'] ?? null,
                ]);
            }

            DB::commit();

            return $this->respond([
                'message' => 'Sprint evaluation created successfully',
                'evaluation' => $sprintEvaluation->load('studentGrades')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondBadRequest(ApiCode::EVALUATION_CREATION_FAILED, $e->getMessage());
        }
    }

    public function getFinalEvaluation($sprintId)
    {
        $sprint = Sprint::with('sprintEvaluation.studentGrades.student.user')
            ->findOrFail($sprintId);

        if (!$this->userCanViewEvaluation($sprint)) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        if (!$sprint->sprintEvaluation) {
            return $this->respondNotFound(ApiCode::EVALUATION_NOT_FOUND);
        }

        return $this->respond(['evaluation' => $sprint->sprintEvaluation]);
    }

    private function userCanViewEvaluation($sprint)
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
