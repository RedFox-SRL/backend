<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\CrossEvaluation;
use App\Models\Student;
use Carbon\Carbon;

class CrossEvaluationActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $crossEvaluation;
    public $student;
    public $deadlineFormatted;

    public function __construct(CrossEvaluation $crossEvaluation, Student $student)
    {
        $this->crossEvaluation = $crossEvaluation;
        $this->student = $student;
        $this->deadlineFormatted = Carbon::parse($crossEvaluation->created_at)->addWeek()->format('d/m/Y H:i:s');
    }

    public function build()
    {

        $isRepresentative = $this->student->groups()->where('creator_id', $this->student->id)->exists();

        return $this->view('emails.cross-evaluation-activation')
            ->with([
                'studentName' => $this->student->user->name . ' ' . $this->student->user->last_name,
                'groupName' => $this->crossEvaluation->evaluatorGroup->short_name,
                'evaluatedGroupName' => $this->crossEvaluation->evaluatedGroup->short_name,
                'deadlineFormatted' => $this->deadlineFormatted,
                'isRepresentative' => $isRepresentative,
            ])
            ->subject('Activación de evaluación cruzada');
    }
}
