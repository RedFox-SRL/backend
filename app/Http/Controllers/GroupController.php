<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupName;
use App\Models\StudentManagement;
use App\Http\Requests\CreateGroupRequest;
use App\Http\Requests\JoinGroupRequest;
use Illuminate\Support\Facades\Auth;
use App\ApiCode;

class GroupController extends Controller
{
    public function create(CreateGroupRequest $request)
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
            return $this->respondBadRequest(ApiCode::STUDENT_NOT_IN_MANAGEMENT);
        }

        $management = $studentManagement->management;

        if ($this->isGroupNameTaken($request->short_name, $request->long_name)) {
            return $this->respondBadRequest(ApiCode::GROUP_ALREADY_EXISTS);
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $group = Group::create([
            'management_id' => $management->id,
            'code' => Group::generateUniqueCode(),
            'short_name' => $request->short_name,
            'long_name' => $request->long_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'logo' => $logoPath,
            'max_members' => $management->group_limit,
        ]);

        GroupName::create([
            'short_name' => $request->short_name,
            'long_name' => $request->long_name,
        ]);

        $group->students()->attach($student->id);

        return $this->respond(['group' => $group], 'Group created successfully.');
    }

    public function joinGroup(JoinGroupRequest $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        if (!$user->student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $student = $user->student;

        if ($this->isStudentInAnyGroup($student->id)) {
            return $this->respondBadRequest(ApiCode::ALREADY_ENROLLED_GROUP);
        }

        $group = Group::where('code', $request->group_code)->first();

        if (!$group) {
            return $this->respondNotFound(ApiCode::GROUP_NOT_FOUND);
        }

        if (!$this->isStudentInManagement($student->id, $group->management_id)) {
            return $this->respondBadRequest(ApiCode::STUDENT_NOT_IN_MANAGEMENT);
        }

        if ($this->isStudentInGroup($student->id, $group->id)) {
            return $this->respondBadRequest(ApiCode::STUDENT_ALREADY_IN_GROUP);
        }

        if ($this->isGroupFull($group)) {
            return $this->respondBadRequest(ApiCode::GROUP_FULL);
        }

        $group->students()->attach($student->id);

        return $this->respond(['group' => $group], 'You have successfully joined the group.');
    }

    private function isStudentInAnyGroup($studentId)
    {
        return Group::whereHas('students', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })->exists();
    }

    private function isStudentInManagement($studentId, $managementId)
    {
        return StudentManagement::where('student_id', $studentId)
            ->where('management_id', $managementId)
            ->exists();
    }

    private function isGroupNameTaken($shortName, $longName)
    {
        return GroupName::whereRaw('LOWER(short_name) = ?', [strtolower($shortName)])->exists() ||
            GroupName::whereRaw('LOWER(long_name) = ?', [strtolower($longName)])->exists();
    }

    private function isStudentInGroup($studentId, $groupId)
    {
        return Group::find($groupId)->students()->where('student_id', $studentId)->exists();
    }

    private function isGroupFull($group)
    {
        return $group->students()->count() >= $group->max_members;
    }

    public function getGroupDetails()
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        if (!$user->student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $student = $user->student;
        $group = $student->groups()->with('students', 'creator.user')->first();

        if (!$group) {
            return $this->respondNotFound(ApiCode::GROUP_NOT_FOUND);
        }

        $representative = [
            'id' => $group->creator->id,
            'name' => $group->creator->user->name,
            'last_name' => $group->creator->user->last_name,
        ];

        $members = $group->students->filter(function ($student) use ($group) {
            return $student->id !== $group->creator->id;
        })->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'last_name' => $student->user->last_name,
            ];
        });

        return $this->respond([
            'group' => [
                'short_name' => $group->short_name,
                'long_name' => $group->long_name,
                'contact_email' => $group->contact_email,
                'contact_phone' => $group->contact_phone,
                'logo' => asset('storage/' . $group->logo),
                'representative' => $representative,
                'members' => $members,
            ]
        ]);
    }

    public function leaveGroup()
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        if (!$user->student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $student = $user->student;
        $group = $student->groups()->first();

        if (!$group) {
            return $this->respondNotFound(ApiCode::GROUP_NOT_FOUND);
        }

        $group->students()->detach($student->id);

        return $this->respondWithMessage('You have successfully left the group.');
    }

    public function getGroupsByManagement($managementId)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        $management = Management::with('groups.creator.user', 'groups.students.user')->find($managementId);

        if (!$management) {
            return $this->respondNotFound(ApiCode::MANAGEMENT_NOT_FOUND);
        }

        if ($management->groups->isEmpty()) {
            return $this->respondNotFound(ApiCode::GROUP_NOT_FOUND);
        }

        $groups = $management->groups->map(function ($group) {
            $representative = [
                'id' => $group->creator->id,
                'name' => $group->creator->user->name,
                'last_name' => $group->creator->user->last_name,
            ];

            $members = $group->students->filter(function ($student) use ($group) {
                return $student->id !== $group->creator->id;
            })->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'last_name' => $student->user->last_name,
                ];
            });

            return [
                'short_name' => $group->short_name,
                'long_name' => $group->long_name,
                'contact_email' => $group->contact_email,
                'contact_phone' => $group->contact_phone,
                'logo' => asset('storage/' . $group->logo),
                'representative' => $representative,
                'members' => $members,
            ];
        });

        return $this->respond(['groups' => $groups]);
    }
}