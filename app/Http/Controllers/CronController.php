<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EvaluationService;
use Illuminate\Support\Facades\Hash;
use App\Models\Sprint;
use Carbon\Carbon;

class CronController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function checkSprints(Request $request)
    {
        if (!$this->validateCronRequest($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $today = now()->startOfDay();
        $sprints = Sprint::where('end_date', $today)
            ->whereDoesntHave('evaluationPeriods')
            ->whereHas('weeklyEvaluations')
            ->get();

        foreach ($sprints as $sprint) {
            $this->evaluationService->createAndActivateEvaluations($sprint);
        }

        return response()->json(['mensaje' => 'Sprints verificados exitosamente'], 200);
    }

    public function sendReminders(Request $request)
    {
        if (!$this->validateCronRequest($request)) {
            return response()->json(['mensaje' => 'No autorizado'], 200);
        }

        $this->evaluationService->sendReminders();

        return response()->json(['mensaje' => 'Recordatorios enviados exitosamente'], 200);
    }

    protected function validateCronRequest(Request $request)
    {
        $token = $request->header('X-Cron-Token');
        return $token && $token === env('CRON_SECRET');
    }
}