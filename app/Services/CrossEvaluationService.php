<?php

namespace App\Services;

use App\Models\CrossEvaluation;
use App\Models\Group;
use App\Models\Management;
use App\Models\Student;
use App\Mail\CrossEvaluationActivationMail;
use App\Mail\CrossEvaluationReminderMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CrossEvaluationService
{
    public function activateAndAssignCrossEvaluations(Management $management)
    {
        $eligibleGroups = $this->getEligibleGroups($management);

        if ($eligibleGroups->count() < 2) {
            return;
        }

        $template = $management->evaluationTemplates()->where('type', 'cross')->first();

        if (!$template) {
            return;
        }

        DB::transaction(function () use ($eligibleGroups, $management, $template) {
            $groupsArray = $eligibleGroups->shuffle()->values()->all();
            $groupCount = count($groupsArray);

            for ($i = 0; $i < $groupCount; $i++) {
                $evaluatorGroup = $groupsArray[$i];
                $evaluatedGroup = $groupsArray[($i + 1) % $groupCount];

                $crossEvaluation = CrossEvaluation::create([
                    'evaluator_group_id' => $evaluatorGroup->id,
                    'evaluated_group_id' => $evaluatedGroup->id,
                    'management_id' => $management->id,
                    'evaluation_template_id' => $template->id,
                ]);

                $this->sendActivationNotifications($crossEvaluation);
            }
        });
    }

    private function getEligibleGroups(Management $management)
    {
        return Group::where('management_id', $management->id)
            ->whereHas('sprints', function ($query) {
                $query->whereHas('sprintEvaluation')
                    ->select('group_id')
                    ->selectRaw('SUM(percentage) as total_percentage')
                    ->groupBy('group_id')
                    ->havingRaw('SUM(percentage) >= ?', [90]);
            })
            ->get();
    }

    public function getActiveCrossEvaluation(Group $group)
    {
        return CrossEvaluation::where('evaluator_group_id', $group->id)
            ->where('is_completed', false)
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->with(['evaluatedGroup', 'evaluationTemplate.sections.criteria'])
            ->first();
    }

    public function submitCrossEvaluation(CrossEvaluation $evaluation, array $responses)
    {
        if (Carbon::now()->gt($evaluation->created_at->addWeek())) {
            return ['success' => false, 'message' => 'El período de evaluación ha expirado'];
        }

        $template = $evaluation->evaluationTemplate;
        $validCriteriaIds = $template->sections->flatMap->criteria->pluck('id')->toArray();

        if (count($responses) !== count($validCriteriaIds) || array_diff_key($responses, array_flip($validCriteriaIds))) {
            return ['success' => false, 'message' => 'Respuestas inválidas'];
        }

        DB::transaction(function () use ($evaluation, $responses) {
            foreach ($responses as $criterionId => $score) {
                $evaluation->responses()->create([
                    'template_criterion_id' => $criterionId,
                    'score' => $score,
                ]);
            }

            $evaluation->update([
                'is_completed' => true,
                'completed_at' => Carbon::now(),
            ]);
        });

        return ['success' => true, 'message' => 'Evaluación cruzada enviada con éxito'];
    }

    private function sendActivationNotifications(CrossEvaluation $crossEvaluation)
    {
        $group = $crossEvaluation->evaluatorGroup;
        foreach ($group->students as $student) {
            Mail::to($student->user->email)
                ->send(new CrossEvaluationActivationMail($crossEvaluation, $student));
        }
    }

    public function sendCrossEvaluationReminders()
    {
        $now = Carbon::now();
        $remindersToSend = CrossEvaluation::where('is_completed', false)
            ->where('created_at', '<=', $now->copy()->subDay())
            ->where('created_at', '>', $now->copy()->subWeek())
            ->with(['evaluatorGroup.students.user'])
            ->get();

        foreach ($remindersToSend as $evaluation) {
            $activationDate = $evaluation->created_at;
            $daysSinceActivation = $now->diffInDays($activationDate);

            if ($daysSinceActivation > 0 && $daysSinceActivation <= 6) {
                foreach ($evaluation->evaluatorGroup->students as $student) {
                    Mail::to($student->user->email)
                        ->send(new CrossEvaluationReminderMail($evaluation, $student));
                }
            }
        }
    }
}
