<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EvaluationService;
use App\Services\CrossEvaluationService;
use App\Models\Sprint;
use App\Models\Management;
use Carbon\Carbon;

class CronController extends Controller
{
    protected $evaluationService;
    protected $crossEvaluationService;

    public function __construct(EvaluationService $evaluationService, CrossEvaluationService $crossEvaluationService)
    {
        $this->evaluationService = $evaluationService;
        $this->crossEvaluationService = $crossEvaluationService;
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
            $group = $sprint->group;
            if ($group->students()->count() < 2) {
                return response()->json(['mensaje' => 'Grupo con numero de miembros insuficientes'], 200);
            }
            $this->evaluationService->createAndActivateEvaluations($sprint);
        }

        return response()->json(['mensaje' => 'Sprints verificados exitosamente']);
    }

    public function checkCrossEvaluations(Request $request)
    {
        if (!$this->validateCronRequest($request)) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $completedManagements = Management::where('project_delivery_date', '<=', Carbon::now())
            ->whereDoesntHave('crossEvaluations')
            ->get();

        foreach ($completedManagements as $management) {
            $this->crossEvaluationService->activateAndAssignCrossEvaluations($management);
        }

        return response()->json(['mensaje' => 'Evaluaciones cruzadas verificadas exitosamente']);
    }

    public function sendReminders(Request $request)
    {
        if (!$this->validateCronRequest($request)) {
            return response()->json(['mensaje' => 'No autorizado'], 401);
        }

        $this->evaluationService->sendReminders();
        $this->crossEvaluationService->sendCrossEvaluationReminders();

        return response()->json(['mensaje' => 'Recordatorios enviados exitosamente']);
    }

    protected function validateCronRequest(Request $request)
    {
        $token = $request->header('X-Cron-Token');
        return $token && $token === env('CRON_SECRET');
    }
}
