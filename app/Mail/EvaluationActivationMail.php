<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\StudentEvaluation;
use App\Models\Student;

class EvaluationActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $evaluations;
    public $student;

    public function __construct($evaluations, Student $student)
    {
        $this->evaluations = $evaluations;
        $this->student = $student;
    }

    public function build()
    {
        return $this->view('emails.evaluation-activation')
            ->with([
                'studentName' => $this->student->user->name,
                'evaluations' => $this->evaluations
            ])
            ->subject('Evaluaciones Activadas');
    }
}
