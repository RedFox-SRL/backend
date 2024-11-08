<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Group;
use App\Models\Student;
use App\Http\Requests\SendInvitationRequest;
use App\Mail\GroupInvitation;
use App\Models\StudentManagement;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\ApiCode;

class InvitationController extends Controller
{
    public function send(SendInvitationRequest $request)
    {
        $student = auth()->user()->student;

        // Find the group where the student is the creator
        $group = Group::where('creator_id', $student->id)->first();

        if (!$group) {
            return $this->respondUnAuthorizedRequest(ApiCode::NOT_GROUP_REPRESENTATIVE);
        }

        $invitedStudent = Student::whereHas('user', function ($query) use ($request) {
            $query->where('email', $request->email);
        })->first();

        if (!$invitedStudent) {
            return $this->respondBadRequest(ApiCode::STUDENT_NOT_FOUND);
        }

        if ($invitedStudent->groups()->exists()) {
            return $this->respondBadRequest(ApiCode::STUDENT_ALREADY_IN_GROUP);
        }

        // Check if the invited student is in the same management as the group
        $invitedStudentManagement = StudentManagement::where('student_id', $invitedStudent->id)
            ->where('management_id', $group->management_id)
            ->exists();

        if (!$invitedStudentManagement) {
            return $this->respondBadRequest(ApiCode::STUDENT_NOT_IN_SAME_MANAGEMENT);
        }

        if ($group->students()->count() >= $group->management->group_limit) {
            return $this->respondBadRequest(ApiCode::GROUP_FULL);
        }

        $availableInvitations = $group->management->group_limit - $group->students()->count();
        $pendingInvitations = $group->invitations()->where('status', 'pending')->count();

        if ($pendingInvitations >= $availableInvitations) {
            return $this->respondBadRequest(ApiCode::MAX_INVITATIONS_REACHED);
        }

        $existingInvitation = Invitation::where('group_id', $group->id)
            ->where('invited_student_id', $invitedStudent->id)
            ->where('status', 'pending')
            ->first();

        if ($existingInvitation) {
            return $this->respondBadRequest(ApiCode::INVITATION_ALREADY_SENT);
        }

        $invitation = Invitation::create([
            'group_id' => $group->id,
            'invited_by' => $student->id,
            'invited_student_id' => $invitedStudent->id,
            'token' => Str::random(32),
            'expires_at' => Carbon::now()->addHours(6),
        ]);

        Mail::to($invitedStudent->user->email)->send(new GroupInvitation($invitation));

        return $this->respond(['invitation' => $invitation], 'Invitation sent successfully.');
    }


    public function accept($token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            return view('invitations.canceled');
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);
            return view('invitations.expired');
        }

        if ($invitation->status !== 'pending') {
            return view('invitations.already-processed');
        }

        $group = $invitation->group;

        if ($group->students()->count() >= $group->management->group_limit) {
            $invitation->update(['status' => 'rejected']);
            return view('invitations.group-full');
        }

        $invitation->update(['status' => 'accepted']);
        $group->students()->attach($invitation->invited_student_id);

        Invitation::where('invited_student_id', $invitation->invited_student_id)
            ->where('id', '!=', $invitation->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        return view('invitations.accepted');
    }

    public function reject($token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            return view('invitations.canceled');
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);
            return view('invitations.expired');
        }

        if ($invitation->status !== 'pending') {
            return view('invitations.already-processed');
        }

        $invitation->update(['status' => 'rejected']);

        return view('invitations.rejected');
    }

    public function cancel($id)
    {
        $invitation = Invitation::findOrFail($id);

        if ($invitation->invited_by !== auth()->user()->student->id) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        if ($invitation->status !== 'pending') {
            return $this->respondBadRequest(ApiCode::INVITATION_ALREADY_PROCESSED);
        }

        $invitation->delete();

        return $this->respondWithMessage('Invitation cancelled successfully.');
    }

    public function listForGroup($groupId)
    {
        $group = Group::findOrFail($groupId);

        if ($group->creator_id !== auth()->user()->student->id) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        $invitations = $group->invitations()->with('invitedStudent.user')->get();

        return $this->respond(['invitations' => $invitations]);
    }

    public function listForStudent()
    {
        $student = auth()->user()->student;
        $invitations = Invitation::where('invited_student_id', $student->id)
            ->where('status', 'pending')
            ->with('group')
            ->get();

        return $this->respond(['invitations' => $invitations]);
    }
}
