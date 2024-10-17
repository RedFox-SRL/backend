<?php

namespace App\Http\Controllers;

use App\ApiCode;
use App\Http\Requests\CreateGroupRequest;
use App\Http\Requests\JoinGroupRequest;
use App\Http\Requests\UpdateContactInfoRequest;
use App\Models\Calendar;
use App\Models\Group;
use App\Models\GroupName;
use App\Models\Management;
use App\Models\StudentManagement;
use Illuminate\Support\Facades\Auth;

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

        $existingGroup = Group::where('creator_id', $student->id)->first();
        if ($existingGroup) {
            return $this->respondBadRequest(ApiCode::GROUP_ALREADY_EXISTS);
        }

        $studentManagement = StudentManagement::where('student_id', $student->id)->first();
        if (!$studentManagement) {
            return $this->respondBadRequest(ApiCode::STUDENT_NOT_IN_MANAGEMENT);
        }

        $management = $studentManagement->management;

        if ($this->isGroupNameTaken($request->short_name, $request->long_name)) {
            return $this->respondBadRequest(ApiCode::GROUP_NAME_ALREADY_EXISTS);
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $group = Group::create([
            'creator_id' => $student->id,
            'management_id' => $management->id,
            'code' => Group::generateUniqueCode(),
            'short_name' => $request->short_name,
            'long_name' => $request->long_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'logo' => $logoPath,
        ]);

        Calendar::create([
            'group_id' => $group->id,
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

        $group->management->refresh();

        if ($group->students()->count() >= $group->management->group_limit) {
            return $this->respondBadRequest(ApiCode::GROUP_FULL);
        }

        $group->students()->attach($student->id);

        $group->load('students', 'creator.user');

        return $this->respond(['group' => $group->makeHidden('management')]);
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
        $group = $student->groups()->with('students', 'creator.user', 'management.teacher.user', 'calendar')->first();

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

        $management = [
            'teacher_name' => $group->management->teacher->user->name,
            'teacher_last_name' => $group->management->teacher->user->last_name,
        ];

        return $this->respond([
            'group' => [
                'id' => $group->id,
                'short_name' => $group->short_name,
                'long_name' => $group->long_name,
                'contact_email' => $group->contact_email,
                'contact_phone' => $group->contact_phone,
                'logo' => asset('storage/' . $group->logo),
                'code' => $group->code,
                'representative' => $representative,
                'members' => $members,
                'management' => $management,
                'calendar_id' => $group->calendar->id,
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

        if (!$group->students()->where('student_id', $student->id)->exists()) {
            return $this->respondBadRequest(ApiCode::GROUP_NOT_FOUND);
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

        $management = Management::with(['groups.creator.user', 'groups.students' => function ($query) {
            $query->withPivot('role')->with('user');
        }])->find($managementId);

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
                'email' => $group->creator->user->email,
            ];

            $members = $group->students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'last_name' => $student->user->last_name,
                    'email' => $student->user->email,
                    'role' => $student->pivot->role,
                ];
            });

            return [
                'id' => $group->id,
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

    public function getGroupNames()
    {
        $groupNames = GroupName::select('short_name', 'long_name')->get();
        return response()->json($groupNames);
    }

    public function removeMember($memberId)
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

        if ($group->creator_id !== $student->id) {
            return $this->respondBadRequest(ApiCode::NOT_GROUP_REPRESENTATIVE);
        }

        if ($group->creator_id == $memberId) {
            return $this->respondBadRequest(ApiCode::CANNOT_REMOVE_SELF);
        }

        $member = $group->students()->where('student_id', $memberId)->first();

        if (!$member) {
            return $this->respondNotFound(ApiCode::MEMBER_NOT_FOUND);
        }

        $group->students()->detach($memberId);

        return $this->respondWithMessage('Member removed successfully.');
    }

    public function assignRole($memberId)
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

        if ($group->creator_id !== $student->id) {
            return $this->respondBadRequest(ApiCode::NOT_GROUP_REPRESENTATIVE);
        }

        $member = $group->students()->where('student_id', $memberId)->first();

        if (!$member) {
            return $this->respondNotFound(ApiCode::MEMBER_NOT_FOUND);
        }

        $role = request()->input('role');
        $member->pivot->role = $role;
        $member->pivot->save();

        return $this->respondWithMessage('Role assigned successfully.');
    }

    public function getGroupMembersWithRoles($groupId)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        if (!$user->student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $group = Group::find($groupId);

        if (!$group) {
            return $this->respondNotFound(ApiCode::GROUP_NOT_FOUND);
        }

        $members = $group->students()->withPivot('role')->get()->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'email' => $student->user->email,
                'last_name' => $student->user->last_name,
                'role' => $student->pivot->role,
            ];
        });

        return $this->respond(['members' => $members]);
    }

    public function updateContactInfo(UpdateContactInfoRequest $request)
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

        if ($group->creator_id !== $student->id) {
            return $this->respondBadRequest(ApiCode::NOT_GROUP_REPRESENTATIVE);
        }

        $group->update($request->validated());

        return $this->respondWithMessage('Contact information updated successfully.');
    }
}
