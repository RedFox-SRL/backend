<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Management;
use App\Http\Requests\CreateManagementRequest;
use App\Http\Requests\UpdateGroupLimitRequest;
use Illuminate\Support\Facades\Auth;
use App\ApiCode;

class ManagementController extends Controller
{
    // Método para crear una nueva gestión
    public function create(CreateManagementRequest $request)
    {
        $teacher = $this->getAuthenticatedTeacher();
        if ($teacher instanceof \Illuminate\Http\JsonResponse) {
            return $teacher;
        }

        if ($this->gestionExistsForTeacher($teacher->id, $request->input('semester'), $request->input('start_date'))) {
            return $this->respondBadRequest(ApiCode::GESTION_ALREADY_EXISTS);
        }

        $gestion = Management::create($request->validated() + [
            'teacher_id' => $teacher->id,
            'code' => Management::generateUniqueCode(),
        ]);

        return $this->respond(['gestion' => $gestion], 'Management created successfully.');
    }

    // Método para activar/desactivar el código
    public function toggleCode($gestionId)
    {
        $gestion = $this->getGestionForAuthenticatedTeacher($gestionId);
        if ($gestion instanceof \Illuminate\Http\JsonResponse) {
            return $gestion;
        }

        $gestion->is_code_active = !$gestion->is_code_active;
        $gestion->save();

        return $this->respond(['gestion' => $gestion], 'Management code status updated successfully.');
    }

    public function updateGroupLimit(UpdateGroupLimitRequest $request, $gestionId)
    {
        $gestion = $this->getGestionForAuthenticatedTeacher($gestionId);
        if ($gestion instanceof \Illuminate\Http\JsonResponse) {
            return $gestion;
        }

        $gestion->group_limit = $request->input('group_limit');
        $gestion->save();

        return $this->respond(['gestion' => $gestion], 'Group limit updated successfully.');
    }

    public function index()
    {
        $teacher = $this->getAuthenticatedTeacher();
        if ($teacher instanceof \Illuminate\Http\JsonResponse) {
            return $teacher;
        }

        $gestiones = Management::where('teacher_id', $teacher->id)->get();
        return $this->respond($gestiones);
    }

    private function gestionExistsForTeacher($teacherId, $semester, $startDate)
    {
        return Management::where('teacher_id', $teacherId)
            ->where('semester', $semester)
            ->whereYear('start_date', date('Y', strtotime($startDate)))
            ->exists();
    }

    private function getAuthenticatedTeacher()
    {
        $user = Auth::user();
        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        $teacher = $user->teacher;
        if (!$teacher) {
            return $this->respondUnAuthenticated(ApiCode::USER_NOT_TEACHER);
        }

        return $teacher;
    }

    private function getGestion($gestionId)
    {
        $gestion = Management::find($gestionId);
        if (!$gestion) {
            return $this->respondNotFound(ApiCode::GESTION_NOT_FOUND);
        }

        return $gestion;
    }

    // Método auxiliar para validar que el docente autenticado sea el propietario de la gestión
    private function validateTeacherForGestion($gestion, $teacher)
    {
        if ($gestion->teacher_id !== $teacher->id) {
            return $this->respondUnAuthenticated(ApiCode::GESTION_ACCESS_DENIED);
        }

        return true;
    }

    private function getGestionForAuthenticatedTeacher($gestionId)
    {
        $gestion = $this->getGestion($gestionId);
        if ($gestion instanceof \Illuminate\Http\JsonResponse) {
            return $gestion;
        }

        $teacher = $this->getAuthenticatedTeacher();
        if ($teacher instanceof \Illuminate\Http\JsonResponse) {
            return $teacher;
        }

        $isValid = $this->validateTeacherForGestion($gestion, $teacher);
        if ($isValid instanceof \Illuminate\Http\JsonResponse) {
            return $isValid;
        }

        return $gestion;
    }
}
