<?php

namespace App\Http\Controllers;

use App\Models\Management;
use App\Models\ScoreConfiguration;
use Illuminate\Http\Request;
use App\ApiCode;

class ScoreConfigurationController extends Controller
{
    public function store(Request $request, $managementId)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        $management = Management::find($managementId);
        if (!$management) {
            return $this->respondNotFound(ApiCode::MANAGEMENT_NOT_FOUND);
        }

        if ($management->teacher_id !== $teacher->id) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        if ($management->scoreConfiguration) {
            return $this->respondBadRequest(ApiCode::SCORE_CONFIGURATION_ALREADY_EXISTS);
        }

        $validated = $request->validate([
            'sprint_points' => 'required|integer|min:0',
            'cross_evaluation_points' => 'required|integer|min:0',
            'proposal_points' => 'required|integer|min:0',
        ]);

        $totalPoints = $validated['sprint_points'] + $validated['cross_evaluation_points'] + $validated['proposal_points'];

        if ($totalPoints !== 100) {
            return $this->respondBadRequest(ApiCode::INVALID_TOTAL_POINTS);
        }

        $scoreConfiguration = $management->scoreConfiguration()->create($validated);

        return $this->respond([
            'message' => 'Configuración de puntos creada con éxito.',
            'data' => $scoreConfiguration
        ]);
    }

    public function show($managementId)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        $management = Management::find($managementId);
        if (!$management) {
            return $this->respondNotFound(ApiCode::MANAGEMENT_NOT_FOUND);
        }

        if ($management->teacher_id !== $teacher->id) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        $scoreConfiguration = $management->scoreConfiguration;

        if (!$scoreConfiguration) {
            return $this->respondNotFound(ApiCode::SCORE_CONFIGURATION_NOT_FOUND);
        }

        return $this->respond($scoreConfiguration);
    }
}
