<?php

namespace App\Http\Controllers;

use App\Models\CrossEvaluation;
use App\Services\CrossEvaluationService;
use Illuminate\Http\Request;
use App\ApiCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CrossEvaluationController extends Controller
{
    protected $crossEvaluationService;

    public function __construct(CrossEvaluationService $crossEvaluationService)
    {
        $this->crossEvaluationService = $crossEvaluationService;
    }

    public function getActiveCrossEvaluation(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        if (!$user->student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $group = $user->student->groups()->first();

        if (!$group) {
            return $this->respondNotFound(ApiCode::GROUP_NOT_FOUND);
        }

        $crossEvaluation = $this->crossEvaluationService->getActiveCrossEvaluation($group);

        if (!$crossEvaluation) {
            return $this->respondNotFound(ApiCode::CROSS_EVALUATION_NOT_FOUND);
        }

        $evaluatedGroup = $crossEvaluation->evaluatedGroup;
        $representative = $evaluatedGroup->creator->user;

        $response = [
            'cross_evaluation_id' => $crossEvaluation->id,
            'evaluated_group' => [
                'id' => $evaluatedGroup->id,
                'name' => $evaluatedGroup->long_name,
                'short_name' => $evaluatedGroup->short_name,
                'contact_email' => $evaluatedGroup->contact_email,
                'contact_phone' => $evaluatedGroup->contact_phone,
                'representative' => [
                    'name' => $representative->name,
                    'last_name' => $representative->last_name,
                    'email' => $representative->email,
                ],
                'links' => $evaluatedGroup->projectLinks->map(function ($link) {
                    return [
                        'url' => $link->url,
                        'category' => $link->category,
                        'description' => $link->description,
                    ];
                }),
            ],
            'deadline' => $crossEvaluation->created_at->addWeek(),
        ];

        if ($user->student->id === $group->creator_id) {
            $response['template'] = $crossEvaluation->evaluationTemplate;
        }

        return $this->respond($response);
    }

    public function submitCrossEvaluation(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        if (!$user->student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $group = $user->student->groups()->first();

        if (!$group) {
            return $this->respondNotFound(ApiCode::GROUP_NOT_FOUND);
        }

        if ($user->student->id !== $group->creator_id) {
            return $this->respondBadRequest(ApiCode::NOT_GROUP_REPRESENTATIVE);
        }

        $crossEvaluation = $this->crossEvaluationService->getActiveCrossEvaluation($group);

        if (!$crossEvaluation) {
            return $this->respondNotFound(ApiCode::CROSS_EVALUATION_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'responses' => 'required|array',
            'responses.*' => 'required|integer|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return $this->respondBadRequest(ApiCode::VALIDATION_ERROR);
        }

        $result = $this->crossEvaluationService->submitCrossEvaluation($crossEvaluation, $request->responses);

        return $this->respond($result, 'Evaluación cruzada enviada con éxito.');
    }
}
