<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Management;
use App\Http\Requests\CreateManagementRequest;
use App\Http\Requests\UpdateGroupLimitRequest;
use Illuminate\Support\Facades\Auth;
use App\ApiCode;
use App\Models\StudentManagement;

class ManagementController extends Controller
{
    public function create(CreateManagementRequest $request)
    {
        $teacher = $this->getAuthenticatedTeacher();
        if ($teacher instanceof \Illuminate\Http\JsonResponse) {
            return $teacher;
        }

        if ($this->managementExistsForTeacher($teacher->id, $request->input('semester'), $request->input('start_date'))) {
            return $this->respondBadRequest(ApiCode::MANAGEMENT_ALREADY_EXISTS);
        }

        $management = Management::create($request->validated() + [
                'teacher_id' => $teacher->id,
                'code' => Management::generateUniqueCode(),
            ]);

        return $this->respond(['management' => $management], 'Management created successfully.');
    }

    public function toggleCode($managementId)
    {
        $management = $this->getManagementForAuthenticatedTeacher($managementId);
        if ($management instanceof \Illuminate\Http\JsonResponse) {
            return $management;
        }

        $management->is_code_active = !$management->is_code_active;
        $management->save();

        return $this->respond(['management' => $management], 'Management code status updated successfully.');
    }

    public function updateGroupLimit(UpdateGroupLimitRequest $request, $managementId)
    {
        $management = $this->getManagementForAuthenticatedTeacher($managementId);
        if ($management instanceof \Illuminate\Http\JsonResponse) {
            return $management;
        }

        $management->group_limit = $request->input('group_limit');
        $management->save();

        return $this->respond(['management' => $management], 'Group limit updated successfully.');
    }

    public function index()
    {
        $teacher = $this->getAuthenticatedTeacher();
        if ($teacher instanceof \Illuminate\Http\JsonResponse) {
            return $teacher;
        }

        $managements = Management::where('teacher_id', $teacher->id)->get();
        if ($managements->isEmpty()) {
            return $this->respondNotFound(ApiCode::MANAGEMENT_NOT_FOUND);
        }

        return $this->respond($managements);
    }

    private function managementExistsForTeacher($teacherId, $semester, $startDate)
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

    private function getManagement($managementId)
    {
        $management = Management::find($managementId);
        if (!$management) {
            return $this->respondNotFound(ApiCode::MANAGEMENT_NOT_FOUND);
        }

        return $management;
    }

    private function validateTeacherForManagement($management, $teacher)
    {
        if ($management->teacher_id !== $teacher->id) {
            return $this->respondUnAuthenticated(ApiCode::MANAGEMENT_ACCESS_DENIED);
        }

        return true;
    }

    private function getManagementForAuthenticatedTeacher($managementId)
    {
        $management = $this->getManagement($managementId);
        if ($management instanceof \Illuminate\Http\JsonResponse) {
            return $management;
        }

        $teacher = $this->getAuthenticatedTeacher();
        if ($teacher instanceof \Illuminate\Http\JsonResponse) {
            return $teacher;
        }

        $isValid = $this->validateTeacherForManagement($management, $teacher);
        if ($isValid instanceof \Illuminate\Http\JsonResponse) {
            return $isValid;
        }

        return $management;
    }

    public function getStudentManagement()
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        if (!$user->student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $student = $user->student;
        $studentManagement = StudentManagement::where('student_id', $student->id)->with('management')->first();

        if (!$studentManagement) {
            return $this->respondNotFound(ApiCode::MANAGEMENT_NOT_FOUND);
        }

        return $this->respond(['management' => $studentManagement->management], 'Management details retrieved successfully.');
    }

    public function getManagementDetails($id)
    {
        $management = Management::with(['teacher.user'])->find($id);

        if (!$management) {
            return response()->json(['message' => 'Management not found'], 404);
        }

        $teacher = [
            'id' => $management->teacher->id,
            'name' => $management->teacher->user->name,
            'last_name' => $management->teacher->user->last_name,
            'email' => $management->teacher->user->email,
        ];

        $students = StudentManagement::where('management_id', $id)
            ->with('student.user')
            ->get()
            ->map(function ($studentManagement) {
                return [
                    'id' => $studentManagement->student->id,
                    'name' => $studentManagement->student->user->name,
                    'last_name' => $studentManagement->student->user->last_name,
                    'email' => $studentManagement->student->user->email,
                ];
            });

        return response()->json([
            'teacher' => $teacher,
            'students' => $students,
        ]);
    }
}
