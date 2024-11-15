<?php

namespace App\Services;

use App\Mail\EvaluationActivationMail;
use App\Mail\EvaluationReminderMail;
use App\Mail\TeacherSummaryMail;
use App\Models\Sprint;
use App\Models\EvaluationPeriod;
use App\Models\StudentEvaluation;
use App\Models\PeerEvaluationAssignment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EvaluationService
{
    public function createEvaluationPeriods(Sprint $sprint)
    {
        $management = $sprint->group->management;
        $selfTemplate = $management->evaluationTemplates()->where('type', 'self')->first();
        $peerTemplate = $management->evaluationTemplates()->where('type', 'peer')->first();

        $startDate = $sprint->end_date;
        $endDate = $startDate->copy()->addDays(4);

        DB::transaction(function () use ($sprint, $selfTemplate, $peerTemplate, $startDate, $endDate) {
            if ($selfTemplate) {
                $this->createEvaluationPeriod($sprint, $selfTemplate, 'self', $startDate, $endDate);
            }

            if ($peerTemplate) {
                $this->createEvaluationPeriod($sprint, $peerTemplate, 'peer', $startDate, $endDate);
            }
        });
    }

    private function createEvaluationPeriod(Sprint $sprint, $template, $type, $startDate, $endDate)
    {
        $period = EvaluationPeriod::create([
            'sprint_id' => $sprint->id,
            'evaluation_template_id' => $template->id,
            'type' => $type,
            'starts_at' => $startDate,
            'ends_at' => $endDate,
            'is_active' => true,
        ]);

        $students = $sprint->group->students;

        foreach ($students as $student) {
            if ($type === 'self') {
                $this->createStudentEvaluation($period, $student, $student);
            } else {
                $this->assignPeerEvaluation($period, $students, $student);
            }
        }
    }

    public function sendActivationNotifications(Sprint $sprint)
    {
        $students = $sprint->group->students;
        foreach ($students as $student) {
            $evaluations = StudentEvaluation::where('evaluator_id', $student->id)
                ->whereHas('evaluationPeriod', function ($query) use ($sprint) {
                    $query->where('sprint_id', $sprint->id);
                })
                ->get();

            Mail::to($student->user)->send(new EvaluationActivationMail($evaluations));
        }
    }

    public function sendReminders()
    {
        $now = Carbon::now();

        StudentEvaluation::where('is_completed', false)
            ->whereHas('evaluationPeriod', function ($query) use ($now) {
                $query->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now)
                    ->where('is_active', true);
            })
            ->each(function ($evaluation) use ($now) {
                if ($evaluation->evaluationPeriod->starts_at->isToday() ||
                    $evaluation->evaluationPeriod->starts_at->addDays(3)->isToday()) {
                    Mail::to($evaluation->evaluator->user)->send(new EvaluationReminderMail($evaluation));
                }
            });
    }

    private function createStudentEvaluation(EvaluationPeriod $period, Student $evaluator, Student $evaluated = null)
    {
        StudentEvaluation::create([
            'evaluation_period_id' => $period->id,
            'evaluator_id' => $evaluator->id,
            'evaluated_id' => $evaluated ? $evaluated->id : null,
        ]);
    }

    private function assignPeerEvaluation(EvaluationPeriod $period, $students, Student $evaluator)
    {
        $availableStudents = $students->where('id', '!=', $evaluator->id)->pluck('id')->toArray();
        $evaluatedId = $availableStudents[array_rand($availableStudents)];

        PeerEvaluationAssignment::create([
            'evaluation_period_id' => $period->id,
            'evaluator_id' => $evaluator->id,
            'evaluated_id' => $evaluatedId,
        ]);

        $this->createStudentEvaluation($period, $evaluator, Student::find($evaluatedId));
    }

    public function sendTeacherSummary(Sprint $sprint)
    {
        $teacher = $sprint->group->teacher;
        Mail::to($teacher->user)->send(new TeacherSummaryMail($sprint));
    }

    public function getActiveEvaluations(Student $student)
    {
        $now = Carbon::now();

        return StudentEvaluation::where('evaluator_id', $student->id)
            ->whereHas('evaluationPeriod', function ($query) use ($now) {
                $query->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now)
                    ->where('is_active', true);
            })
            ->with(['evaluationPeriod.evaluationTemplate.sections.criteria', 'evaluated'])
            ->get();
    }

    public function submitEvaluation(StudentEvaluation $evaluation, array $responses)
    {
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
    }
}
