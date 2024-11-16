<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EvaluationService;
use Illuminate\Support\Facades\Hash;

class CronController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function checkSprints(Request $request)
    {
        // Verificar token secreto
        if (!$this->validateCronRequest($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $today = now()->startOfDay();
        $sprints = \App\Models\Sprint::where('end_date', $today)->get();

        foreach ($sprints as $sprint) {
            \App\Jobs\ActivateSprintEvaluations::dispatch($sprint);
        }

        return response()->json(['message' => 'Sprints checked successfully']);
    }

    public function sendReminders(Request $request)
    {
        // Verificar token secreto
        if (!$this->validateCronRequest($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $this->evaluationService->sendReminders();

        return response()->json(['message' => 'Reminders sent successfully']);
    }

    protected function validateCronRequest(Request $request)
    {
        $token = $request->header('X-Cron-Token');
        return $token && $token === env('CRON_SECRET');
    }
}
