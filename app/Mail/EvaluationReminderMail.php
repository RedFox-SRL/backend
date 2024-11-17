<?php

namespace App\Mail;

use App\Models\StudentEvaluation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EvaluationReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $evaluation;

    public function __construct(StudentEvaluation $evaluation)
    {
        $this->evaluation = $evaluation;
    }

    public function build()
    {
        $sprint = $this->evaluation->evaluationPeriod->sprint;
        $group = $sprint->group;

        return $this->view('emails.student-reminder')
            ->with([
                'studentName' => $this->evaluation->evaluator->user->name,
                'evaluation' => $this->evaluation,
                'sprint' => $sprint,
                'groupName' => $group->short_name
            ])
            ->subject('Recordatorio: Evaluación pendiente para Sprint ' . $sprint->name . ' - Grupo ' . $group->short_name);
    }
}
