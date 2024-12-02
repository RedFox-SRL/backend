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
            'sprint_teacher_percentage' => 'required|numeric|min:0|max:100',
            'sprint_self_percentage' => 'required|numeric|min:0|max:100',
            'sprint_peer_percentage' => 'required|numeric|min:0|max:100',
            'proposal_part_a_percentage' => 'required|numeric|min:0|max:100',
            'proposal_part_b_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $totalPoints = $validated['sprint_points'] + $validated['cross_evaluation_points'] + $validated['proposal_points'];

        if ($totalPoints !== 100) {
            return $this->respondBadRequest(ApiCode::INVALID_TOTAL_POINTS);
        }

        $sprintPercentageTotal = $validated['sprint_teacher_percentage'] + $validated['sprint_self_percentage'] + $validated['sprint_peer_percentage'];
        if (abs($sprintPercentageTotal - 100) > 0.01) {
            return $this->respondBadRequest(ApiCode::INVALID_SPRINT_PERCENTAGES);
        }

        $proposalPercentageTotal = $validated['proposal_part_a_percentage'] + $validated['proposal_part_b_percentage'];
        if (abs($proposalPercentageTotal - 100) > 0.01) {
            return $this->respondBadRequest(ApiCode::INVALID_PROPOSAL_PERCENTAGES);
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

    public function checkConfigurationStatus($managementId)
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

        $isComplete = $scoreConfiguration &&
            $scoreConfiguration->sprint_points !== null &&
            $scoreConfiguration->cross_evaluation_points !== null &&
            $scoreConfiguration->proposal_points !== null &&
            $scoreConfiguration->sprint_teacher_percentage !== null &&
            $scoreConfiguration->sprint_self_percentage !== null &&
            $scoreConfiguration->sprint_peer_percentage !== null &&
            $scoreConfiguration->proposal_part_a_percentage !== null &&
            $scoreConfiguration->proposal_part_b_percentage !== null;

        if ($isComplete) {
            return $this->respond([
                'is_configuration_complete' => true
            ]);
        } else {
            return $this->respondBadRequest(ApiCode::INCOMPLETE_CONFIGURATION);
        }
    }
}
