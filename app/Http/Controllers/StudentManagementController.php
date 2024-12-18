<?php

namespace App\Http\Controllers;

use App\Models\Management;
use App\Models\StudentManagement;
use App\Http\Requests\JoinManagementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\ApiCode;

class StudentManagementController extends Controller
{
    public function join(JoinManagementRequest $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        if (!$user->student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $student = $user->student;

        $management = Management::where('code', $request->management_code)->first();

        if (!$management) {
            return $this->respondNotFound(ApiCode::MANAGEMENT_NOT_FOUND);
        }

        if (!$management->is_code_active) {
            return $this->respondBadRequest(ApiCode::MANAGEMENT_CODE_INACTIVE);
        }

        $existingManagement = StudentManagement::where('student_id', $student->id)->first();

        if ($existingManagement) {
            return $this->respondBadRequest(ApiCode::ALREADY_ENROLLED);
        }

        StudentManagement::create([
            'student_id' => $student->id,
            'management_id' => $management->id,
        ]);

        return $this->respondWithMessage('Te has unido exitosamente a la gestión.');
    }

    public function leaveManagement()
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        if (!$user->student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $student = $user->student;

        $studentManagement = StudentManagement::where('student_id', $student->id)->first();

        if (!$studentManagement) {
            return $this->respondBadRequest(ApiCode::NOT_PART_OF_MANAGEMENT);
        }

        $group = $student->groups()->first();
        if ($group) {
            if ($group->creator_id === $student->id) {
                $group->delete();
            } else {
                $group->students()->detach($student->id);
            }
        }

        $studentManagement->delete();

        return $this->respondWithMessage('Te has salido exitosamente de la gestión y del grupo.');
    }
}
