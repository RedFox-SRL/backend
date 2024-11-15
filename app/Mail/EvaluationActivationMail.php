<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\StudentEvaluation;

class EvaluationActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $evaluations;

    public function __construct($evaluations)
    {
        $this->evaluations = $evaluations;
    }

    public function build()
    {
        return $this->view('emails.evaluation-activation')
                    ->subject('Evaluaciones Activadas');
    }
}
