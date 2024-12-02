<?php

namespace App\Http\Controllers;

use App\ApiCode;
use App\Models\Management;
use App\Models\Sprint;
use App\Models\StudentSprintGrade;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GradeSummaryController extends Controller
{
    public function getGradeSummary(): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->teacher) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $managements = Management::where('teacher_id', $user->teacher->id)
            ->with([
                'groups.students.user',
                'groups.sprints.sprintEvaluation.studentGrades',
                'groups.proposalSubmission',
                'groups.crossEvaluationsAsEvaluated.responses',
                'scoreConfiguration'
            ])
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
            $groupScores = $this->calculateGroupScores($group, $scoreConfig);

            return [
                'group_id' => $group->id,
                'group_name' => $group->long_name,
                'score_configuration' => [
                    'sprints' => $scoreConfig->sprint_points,
                    'proposal' => $scoreConfig->proposal_points,
                    'cross_evaluation' => $scoreConfig->cross_evaluation_points,
                ],
                'group_scores' => $groupScores,
                'students' => $this->getStudentsSummary($group, $scoreConfig, $groupScores),
            ];
        });
    }

    private function calculateGroupScores($group, $scoreConfig)
    {
        $sprintsScore = $this->calculateGroupSprintsScore($group, $scoreConfig);
        $proposalScore = $this->calculateProposalScore($group, $scoreConfig);
        $crossEvalScore = $this->calculateCrossEvalScore($group, $scoreConfig);

        return [
            'sprints' => round($sprintsScore, 2),
            'proposal' => round($proposalScore, 2),
            'cross_evaluation' => round($crossEvalScore, 2),
        ];
    }

    private function calculateGroupSprintsScore($group, $scoreConfig)
    {
        $totalPercentage = $group->sprints->sum('percentage');
        if ($totalPercentage == 0) {
            return 0;
        }

        $totalScore = $group->sprints->sum(function ($sprint) use ($group, $scoreConfig) {
            $sprintEvaluation = $sprint->sprintEvaluation;
            if (!$sprintEvaluation) {
                return 0;
            }

            $avgGrade = $sprintEvaluation->studentGrades->avg(function ($grade) use ($scoreConfig) {
                $teacherScore = $grade->grade ?? 0;
                $selfScore = $grade->self_evaluation_grade ?? 0;
                $peerScore = $grade->peer_evaluation_grade ?? 0;

                $teacherPercentage = $scoreConfig->sprint_teacher_percentage / 100;
                $selfPercentage = $scoreConfig->sprint_self_evaluation_percentage / 100;
                $peerPercentage = $scoreConfig->sprint_peer_evaluation_percentage / 100;

                return ($teacherScore * $teacherPercentage) +
                    ($selfScore * $selfPercentage) +
                    ($peerScore * $peerPercentage);
            }) ?? 0;

            return $avgGrade * ($sprint->percentage / 100);
        });

        return ($totalScore / $totalPercentage) * $scoreConfig->sprint_points;
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
        $crossEvaluation = $group->crossEvaluationsAsEvaluated->first();
        if (!$crossEvaluation) {
            return 0;
        }
        $avgScore = $crossEvaluation->responses->avg('score') ?? 0;
        return ($avgScore / 5) * $scoreConfig->cross_evaluation_points;
    }

    private function getStudentsSummary($group, $scoreConfig, $groupScores)
    {
        return $group->students->map(function ($student) use ($group, $scoreConfig, $groupScores) {
            $studentSprintsScore = $this->calculateStudentSprintsScore($student, $group, $scoreConfig);

            return [
                'student_id' => $student->id,
                'name' => $student->user->name,
                'last_name' => $student->user->last_name,
                'sprint_final_score' => round($studentSprintsScore, 2),
                'proposal_score' => round($groupScores['proposal'], 2),
                'cross_evaluation_score' => round($groupScores['cross_evaluation'], 2),
                'final_score' => round($studentSprintsScore + $groupScores['proposal'] + $groupScores['cross_evaluation'], 2),
                'sprints_detail' => $this->getSprintsDetail($student, $group, $scoreConfig),
            ];
        });
    }

    private function calculateStudentSprintsScore($student, $group, $scoreConfig)
    {
        $totalPercentage = $group->sprints->sum('percentage');
        if ($totalPercentage == 0) {
            return 0;
        }

        $totalScore = $group->sprints->sum(function ($sprint) use ($student, $scoreConfig) {
            $sprintEvaluation = $sprint->sprintEvaluation;
            if (!$sprintEvaluation) {
                return 0;
            }

            $studentGrade = $sprintEvaluation->studentGrades->where('student_id', $student->id)->first();
            if (!$studentGrade) {
                return 0;
            }

            $teacherScore = $studentGrade->grade ?? 0;
            $selfScore = $studentGrade->self_evaluation_grade ?? 0;
            $peerScore = $studentGrade->peer_evaluation_grade ?? 0;

            $teacherPercentage = $scoreConfig->sprint_teacher_percentage / 100;
            $selfPercentage = $scoreConfig->sprint_self_evaluation_percentage / 100;
            $peerPercentage = $scoreConfig->sprint_peer_evaluation_percentage / 100;

            $weightedScore = ($teacherScore * $teacherPercentage) +
                ($selfScore * $selfPercentage) +
                ($peerScore * $peerPercentage);

            return $weightedScore * ($sprint->percentage / 100);
        });

        return ($totalScore / $totalPercentage) * $scoreConfig->sprint_points;
    }

    private function getSprintsDetail($student, $group, $scoreConfig)
    {
        return $group->sprints->map(function ($sprint) use ($student, $scoreConfig) {
            $sprintEval = $sprint->sprintEvaluation;
            $studentGrade = $sprintEval ? $sprintEval->studentGrades->where('student_id', $student->id)->first() : null;

            $teacherGrade = $studentGrade ? ($studentGrade->grade ?? 0) : 0;
            $selfEvaluationGrade = $studentGrade ? ($studentGrade->self_evaluation_grade ?? 0) : 0;
            $peerEvaluationGrade = $studentGrade ? ($studentGrade->peer_evaluation_grade ?? 0) : 0;

            $teacherPercentage = $scoreConfig->sprint_teacher_percentage / 100;
            $selfPercentage = $scoreConfig->sprint_self_evaluation_percentage / 100;
            $peerPercentage = $scoreConfig->sprint_peer_evaluation_percentage / 100;

            $weightedScore = ($teacherGrade * $teacherPercentage) +
                ($selfEvaluationGrade * $selfPercentage) +
                ($peerEvaluationGrade * $peerPercentage);

            $sprintScore = $weightedScore * ($sprint->percentage / 100) * ($scoreConfig->sprint_points / 100);

            return [
                'sprint_id' => $sprint->id,
                'title' => $sprint->title,
                'percentage' => $sprint->percentage,
                'teacher_grade' => $teacherGrade,
                'self_evaluation_grade' => $selfEvaluationGrade,
                'peer_evaluation_grade' => $peerEvaluationGrade,
                'teacher_percentage' => $scoreConfig->sprint_teacher_percentage,
                'self_evaluation_percentage' => $scoreConfig->sprint_self_evaluation_percentage,
                'peer_evaluation_percentage' => $scoreConfig->sprint_peer_evaluation_percentage,
                'weighted_score' => round($weightedScore, 2),
                'sprint_score' => round($sprintScore, 2),
            ];
        });
    }
}
