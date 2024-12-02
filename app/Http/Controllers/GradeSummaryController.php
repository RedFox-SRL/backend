<?php

namespace App\Http\Controllers;

use App\ApiCode;
use App\Models\Management;
use App\Models\Group;
use App\Models\Student;
use App\Models\Sprint;
use App\Models\SprintEvaluation;
use App\Models\ProposalSubmission;
use App\Models\CrossEvaluation;
use App\Models\ScoreConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GradeSummaryController extends Controller
{
    public function getGradeSummary(): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->teacher) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $managements = Management::where('teacher_id', $user->teacher->id)
            ->with(['groups.students.user', 'groups.sprints.sprintEvaluation.studentGrades', 'groups.proposalSubmission', 'groups.crossEvaluationsAsEvaluated', 'scoreConfiguration'])
            ->get();

        $summary = $managements->map(function ($management) {
            return [
                'management_id' => $management->id,
                'semester' => $management->semester,
                'year' => $management->start_date->year,
                'groups' => $this->getGroupsSummary($management),
            ];
        });

        return response()->json($summary);
    }

    private function getGroupsSummary($management)
    {
        $scoreConfig = $management->scoreConfiguration;

        if (!$scoreConfig) {
            return $this->respondBadRequest(ApiCode::SCORE_CONFIGURATION_NOT_FOUND);
        }

        return $management->groups->map(function ($group) use ($scoreConfig) {
            $sprintsScore = $this->calculateSprintsScore($group, $scoreConfig);
            $proposalScore = $this->calculateProposalScore($group, $scoreConfig);
            $crossEvalScore = $this->calculateCrossEvalScore($group, $scoreConfig);

            return [
                'group_id' => $group->id,
                'group_name' => $group->long_name,
                'scores' => [
                    'sprints' => round($sprintsScore, 2),
                    'proposal' => round($proposalScore, 2),
                    'cross_evaluation' => round($crossEvalScore, 2)
                ],
                'total_score' => round($sprintsScore + $proposalScore + $crossEvalScore, 2),
                'students' => $this->getStudentsSummary($group, $scoreConfig),
            ];
        });
    }

    private function calculateSprintsScore($group, $scoreConfig)
    {
        $totalScore = $group->sprints->sum(function ($sprint) {
            return $sprint->sprintEvaluation ? $sprint->sprintEvaluation->studentGrades->avg('grade') : 0;
        });

        return $totalScore * ($scoreConfig->sprint_points / 100);
    }

    private function calculateProposalScore($group, $scoreConfig)
    {
        if (!$group->proposalSubmission) {
            return 0;
        }
        $partAScore = $group->proposalSubmission->part_a_score ?? 0;
        $partBScore = $group->proposalSubmission->part_b_score ?? 0;
        $totalScore = ($partAScore * $scoreConfig->proposal_part_a_percentage / 100) +
            ($partBScore * $scoreConfig->proposal_part_b_percentage / 100);
        return $totalScore * ($scoreConfig->proposal_points / 100);
    }

    private function calculateCrossEvalScore($group, $scoreConfig)
    {
        $avgScore = $group->crossEvaluationsAsEvaluated->avg(function ($crossEval) {
            return $crossEval->responses->avg('score');
        }) ?? 0;
        return $avgScore * ($scoreConfig->cross_evaluation_points / 100);
    }

    private function getStudentsSummary($group, $scoreConfig)
    {
        return $group->students->map(function ($student) use ($group, $scoreConfig) {
            $sprintsScore = $this->calculateStudentSprintsScore($student, $group, $scoreConfig);
            $proposalScore = $this->calculateProposalScore($group, $scoreConfig);
            $crossEvalScore = $this->calculateCrossEvalScore($group, $scoreConfig);

            return [
                'student_id' => $student->id,
                'name' => $student->user->name,
                'last_name' => $student->user->last_name,
                'scores' => [
                    'sprints' => round($sprintsScore, 2),
                    'proposal' => round($proposalScore, 2),
                    'cross_evaluation' => round($crossEvalScore, 2)
                ],
                'total_score' => round($sprintsScore + $proposalScore + $crossEvalScore, 2),
                'sprints_detail' => $this->getSprintsDetail($student, $group),
            ];
        });
    }

    private function calculateStudentSprintsScore($student, $group, $scoreConfig)
    {
        $totalScore = $group->sprints->sum(function ($sprint) use ($student) {
            if ($sprint->sprintEvaluation) {
                $studentGrade = $sprint->sprintEvaluation->studentGrades
                    ->where('student_id', $student->id)
                    ->first();
                return $studentGrade ? $studentGrade->grade : 0;
            }
            return 0;
        });

        return $totalScore * ($scoreConfig->sprint_points / 100);
    }

    private function getSprintsDetail($student, $group)
    {
        return $group->sprints->map(function ($sprint) use ($student) {
            $sprintEval = $sprint->sprintEvaluation;
            $studentGrade = $sprintEval ? $sprintEval->studentGrades->where('student_id', $student->id)->first() : null;

            return [
                'sprint_id' => $sprint->id,
                'title' => $sprint->title,
                'percentage' => number_format($sprint->percentage, 2),
                'grade' => $studentGrade ? number_format($studentGrade->grade, 2) : null,
                'comments' => $studentGrade ? $studentGrade->comments : null,
            ];
        });
    }
}

