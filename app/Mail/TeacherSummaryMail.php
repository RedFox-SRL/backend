<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Sprint;

class TeacherSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sprint;

    public function __construct(Sprint $sprint)
    {
        $this->sprint = $sprint;
    }

    public function build()
    {
        return $this->view('emails.teacher-summary')
                    ->subject('Resumen de Evaluaciones del Sprint');
    }
}
