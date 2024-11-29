<?php

namespace App\Http\Controllers;

use App\Models\CrossEvaluation;
use App\Services\CrossEvaluationService;
use Illuminate\Http\Request;

class CrossEvaluationController extends Controller
{
    protected $crossEvaluationService;

    public function __construct(CrossEvaluationService $crossEvaluationService)
    {
        $this->crossEvaluationService = $crossEvaluationService;
    }

    public function getActiveCrossEvaluation(Request $request)
    {
        $student = $request->user()->student;
        if (!$student || !$student->group) {
            return response()->json(['message' => 'Estudiante o grupo no encontrado.'], 404);
        }

        $crossEvaluation = $this->crossEvaluationService->getActiveCrossEvaluation($student->group);

        if (!$crossEvaluation) {
            return response()->json(['message' => 'No hay evaluación cruzada activa.'], 404);
        }

        $response = [
            'cross_evaluation_id' => $crossEvaluation->id,
            'evaluated_group' => [
                'name' => $crossEvaluation->evaluatedGroup->name,
                'short_name' => $crossEvaluation->evaluatedGroup->short_name,
                'links' => $crossEvaluation->evaluatedGroup->links ?? [],
            ],
            'template' => $crossEvaluation->evaluationTemplate,
            'deadline' => $crossEvaluation->created_at->addWeek(),
        ];

        if ($student->is_representative) {
            $response['questions'] = $crossEvaluation->evaluationTemplate->sections->flatMap->criteria;
        }

        return response()->json($response);
    }

    public function submitCrossEvaluation(Request $request)
    {
        $student = $request->user()->student;
        if (!$student->is_representative) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $crossEvaluation = $this->crossEvaluationService->getActiveCrossEvaluation($student->group);
        if (!$crossEvaluation) {
            return response()->json(['error' => 'No hay evaluación cruzada activa'], 404);
        }

        $request->validate([
            'responses' => 'required|array',
            'responses.*' => 'required|integer|min:0|max:5',
        ]);

        $result = $this->crossEvaluationService->submitCrossEvaluation($crossEvaluation, $request->responses);

        return response()->json($result);
    }
}
