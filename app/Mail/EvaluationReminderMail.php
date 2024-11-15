<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\StudentEvaluation;

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
        return $this->view('emails.student-reminder')
                    ->subject('Recordatorio de Evaluación Pendiente');
    }
}
