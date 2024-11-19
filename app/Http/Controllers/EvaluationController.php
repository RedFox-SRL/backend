<?php

namespace App\Http\Controllers;

use App\Services\EvaluationService;
use App\Models\StudentEvaluation;
use Illuminate\Http\Request;
use App\ApiCode;

class EvaluationController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function getActiveEvaluations()
    {
        $student = auth()->user()->student;
        $evaluations = $this->evaluationService->getActiveEvaluations($student);

        return $this->respond(['evaluations' => $evaluations]);
    }

    public function submitEvaluation(Request $request, StudentEvaluation $evaluation)
    {
        $request->validate([
            'responses' => 'required|array',
            'responses.*' => 'required|integer|min:0|max:5',
        ]);

        if ($evaluation->is_completed) {
            return $this->respondBadRequest(ApiCode::EVALUATION_ALREADY_COMPLETED);
        }

        if ($evaluation->evaluator_id !== auth()->user()->student->id) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        $result = $this->evaluationService->submitEvaluation($evaluation, $request->responses);

        if (!$result['success']) {
            return $this->respondBadRequest($result['error']);
        }

        return $this->respondWithMessage($result['message']);
    }
}
