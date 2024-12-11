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
    public $summary;

    public function __construct(Sprint $sprint, array $summary)
    {
        $this->sprint = $sprint;
        $this->summary = $summary;
    }

    public function build()
    {
        return $this->view('emails.teacher-summary')
            ->subject('Resumen detallado de evaluaciones del sprint ' . $this->sprint->title);
    }
}
