<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Management;
use App\Http\Requests\CreateManagementRequest;
use App\Http\Requests\UpdateGroupLimitRequest;
use App\Http\Requests\UpdateProjectDeliveryDateRequest;
use Illuminate\Support\Facades\Auth;
use App\ApiCode;
use App\Models\StudentManagement;
use Carbon\Carbon;

class ManagementController extends Controller
{
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

    public function create(CreateManagementRequest $request)
    {
        $teacher = $this->getAuthenticatedTeacher();
        if ($teacher instanceof \Illuminate\Http\JsonResponse) {
            return $teacher;
        }

        $requestedYear = $request->input('year');
        $requestedSemester = $request->input('semester');

        if ($this->managementExistsForTeacher($teacher->id, $requestedSemester, $requestedYear)) {
            return $this->respondBadRequest(ApiCode::MANAGEMENT_ALREADY_EXISTS);
        }

        $currentDate = Carbon::now();
        $currentYear = $currentDate->year;
        $currentSemester = $currentDate->month <= 6 ? 'first' : 'second';

        if ($requestedYear < $currentYear ||
            ($requestedYear == $currentYear && $requestedSemester == 'first' && $currentSemester == 'second')) {
            return $this->respondBadRequest(ApiCode::MANAGEMENT_DATE_IN_PAST);
        }

        if ($requestedYear > $currentYear ||
            ($requestedYear == $currentYear && $requestedSemester == 'second' && $currentSemester == 'first')) {
            return $this->respondBadRequest(ApiCode::MANAGEMENT_DATE_IN_FUTURE);
        }

        $dates = Management::calculateDates($requestedSemester, $requestedYear);

        $management = Management::create([
            'teacher_id' => $teacher->id,
            'code' => Management::generateUniqueCode(),
            'semester' => $requestedSemester,
            'start_date' => $dates['start_date'],
            'end_date' => $dates['end_date'],
            'group_limit' => 1,
            'is_code_active' => true,
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
        $teacher = $this->getAuthenticatedTeacher();
        if ($teacher instanceof \Illuminate\Http\JsonResponse) {
            return $teacher;
        }

        $management = $this->getManagement($managementId);
        if ($management instanceof \Illuminate\Http\JsonResponse) {
            return $management;
        }

        if ($management->teacher_id !== $teacher->id) {
            return $this->respondUnAuthorizedRequest(ApiCode::MANAGEMENT_ACCESS_DENIED);
        }

        $validatedData = $request->validated();
        $management->group_limit = $validatedData['group_limit'];
        $management->save();

        return $this->respond(['management' => $management], 'Group limit updated successfully.');
    }

    public function updateProjectDeliveryDate(UpdateProjectDeliveryDateRequest $request, $managementId)
    {
        $teacher = $this->getAuthenticatedTeacher();
        if ($teacher instanceof \Illuminate\Http\JsonResponse) {
            return $teacher;
        }

        $management = $this->getManagement($managementId);
        if ($management instanceof \Illuminate\Http\JsonResponse) {
            return $management;
        }

        if ($management->teacher_id !== $teacher->id) {
            return $this->respondForbidden(ApiCode::MANAGEMENT_ACCESS_DENIED);
        }

        $projectDeliveryDate = Carbon::parse($request->input('project_delivery_date'));

        if ($projectDeliveryDate->lt($management->start_date)) {
            return $this->respondBadRequest(ApiCode::PROJECT_DELIVERY_DATE_BEFORE_START);
        }

        if ($projectDeliveryDate->gt($management->end_date)) {
            return $this->respondBadRequest(ApiCode::PROJECT_DELIVERY_DATE_AFTER_END);
        }

        $management->project_delivery_date = $projectDeliveryDate;
        $management->save();

        return $this->respond(['management' => $management], 'Project delivery date updated successfully.');
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
            return $this->respondNotFound(ApiCode::MANAGEMENT_NOT_FOUND);
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

        return $this->respond([
            'teacher' => $teacher,
            'students' => $students,
        ]);
    }

    private function managementExistsForTeacher($teacherId, $semester, $year)
    {
        $startDate = Carbon::create($year, $semester === 'first' ? 1 : 7, 1)->startOfDay();
        $endDate = Carbon::create($year, $semester === 'first' ? 6 : 12, 31)->endOfDay();

        return Management::where('teacher_id', $teacherId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
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

    public function updateProposalDeadlines(Request $request, $managementId)
    {
        $request->validate([
            'proposal_part_a_deadline' => 'required|date',
            'proposal_part_b_deadline' => 'required|date',
        ]);

        $management = Management::find($managementId);
        if (!$management) {
            return $this->respondNotFound(ApiCode::MANAGEMENT_NOT_FOUND);
        }

        if ($management->teacher_id !== auth()->user()->teacher->id) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        $management->update($request->only(['proposal_part_a_deadline', 'proposal_part_b_deadline']));

        return $this->respond(['management' => $management], 'Proposal deadlines updated successfully.');
    }
}
