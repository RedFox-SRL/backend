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
                'studentName' => $this->evaluation->evaluator->user->name . ' ' . $this->evaluation->evaluator->user->last_name,
                'evaluation' => $this->evaluation,
                'sprint' => $sprint,
                'groupName' => $group->short_name,
                'evaluationType' => ucfirst($this->evaluation->evaluationPeriod->type)
            ])
            ->subject('Recordatorio: Evaluación ' . $this->translateEvaluationType($this->evaluation->evaluationPeriod->type) . ' pendiente para Sprint ' . $sprint->title . ' - Grupo ' . $group->short_name);
    }

    private function translateEvaluationType($type)
    {
        $translations = [
            'self' => 'Autoevaluación',
            'peer' => 'Evaluación entre pares',
        ];

        return $translations[strtolower($type)] ?? ucfirst($type);
    }
}
