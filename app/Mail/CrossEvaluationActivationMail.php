<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\CrossEvaluation;
use App\Models\Student;

class CrossEvaluationActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $crossEvaluation;
    public $student;

    public function __construct(CrossEvaluation $crossEvaluation, Student $student)
    {
        $this->crossEvaluation = $crossEvaluation;
        $this->student = $student;
    }

    public function build()
    {
        return $this->view('emails.cross-evaluation-activation')
                    ->subject('Activación de Evaluación Cruzada');
    }
}
