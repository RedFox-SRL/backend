<?php

namespace App\Jobs;

use App\Models\Sprint;
use App\Services\EvaluationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ActivateSprintEvaluations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sprint;

    public function __construct(Sprint $sprint)
    {
        $this->sprint = $sprint;
    }

    public function handle(EvaluationService $evaluationService)
    {
        $evaluationService->createEvaluationPeriods($this->sprint);
        $evaluationService->sendActivationNotifications($this->sprint);
    }
}
