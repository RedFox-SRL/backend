<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\StudentEvaluation;
use App\Models\Student;
use App\Models\Sprint;

class EvaluationActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $evaluations;
    public $student;
    public $sprint;

    public function __construct($evaluations, Student $student, Sprint $sprint)
    {
        $this->evaluations = $evaluations;
        $this->student = $student;
        $this->sprint = $sprint;
    }

    public function build()
    {
        return $this->view('emails.evaluation-activation')
            ->with([
                'studentName' => $this->student->user->name . ' ' . $this->student->user->last_name,
                'evaluations' => $this->evaluations,
                'sprint' => $this->sprint,
                'groupName' => $this->sprint->group->short_name
            ])
            ->subject('Evaluaciones Activadas para Sprint ' . $this->sprint->title . ' - Grupo ' . $this->sprint->group->short_name);
    }
}
