<?php

namespace App\Console;

use App\Models\Sprint;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Http;
use App\Services\EvaluationService;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $today = now()->startOfDay();
            $sprints = Sprint::where('end_date', $today)->get();

            foreach ($sprints as $sprint) {
                Http::post("/api/sprints/{$sprint->id}/finish");
            }
        })->everyMinute();

        // Enviar recordatorios de evaluaciones
        $schedule->call(function () {
            $evaluationService = app(EvaluationService::class);
            $evaluationService->sendReminders();
        })->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
