<?php

namespace App\Http\Controllers;

use App\Models\Management;
use App\Models\Group;
use App\Models\ProposalSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\ApiCode;

class TeacherProposalController extends Controller
{
    public function getGroupSubmissions($managementId)
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

        $groups = $management->groups()->with(['creator.user', 'proposalSubmission'])->get();

        $response = $groups->map(function ($group) use ($management) {
            $submission = $group->proposalSubmission;
            return [
                'group_id' => $group->id,
                'short_name' => $group->short_name,
                'representative' => [
                    'name' => $group->creator->user->name,
                    'last_name' => $group->creator->user->last_name,
                    'email' => $group->creator->user->email,
                ],
                'part_a' => $this->getSubmissionStatus($submission, $management, 'a'),
                'part_b' => $this->getSubmissionStatus($submission, $management, 'b'),
            ];
        });

        return $this->respond($response);
    }

    public function evaluateProposal(Request $request, $groupId, $part)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        $request->validate([
            'score' => 'required|integer|min:0|max:100',
        ]);

        $group = Group::find($groupId);

        if (!$group) {
            return $this->respondNotFound(ApiCode::GROUP_NOT_FOUND);
        }

        if ($group->management->teacher_id !== $teacher->id) {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        $submission = ProposalSubmission::firstOrCreate(['group_id' => $groupId]);

        $fileField = "part_{$part}_file";
        $scoreField = "part_{$part}_score";

        if (!$submission->$fileField) {
            return $this->respondBadRequest(ApiCode::SUBMISSION_NOT_FOUND);
        }

        if ($submission->$scoreField !== null) {
            return $this->respondBadRequest(ApiCode::SCORE_ALREADY_SAVED);
        }

        $submission->$scoreField = $request->score;
        $submission->save();

        return $this->respond(['message' => "Part {$part} evaluated successfully"]);
    }

    private function getSubmissionStatus($submission, $management, $part)
    {
        $deadlineField = "proposal_part_{$part}_deadline";
        $fileField = "part_{$part}_file";
        $submittedAtField = "part_{$part}_submitted_at";
        $scoreField = "part_{$part}_score";

        $now = Carbon::now();
        $deadline = $management->$deadlineField;

        if (!$deadline) {
            return ['status' => 'not_available'];
        }

        if ($submission && $submission->$fileField) {
            $status = [
                'status' => 'submitted',
                'file_url' => Storage::url($submission->$fileField),
                'submitted_at' => $submission->$submittedAtField
            ];

            if ($submission->$scoreField !== null) {
                $status['score'] = $submission->$scoreField;
            }

            return $status;
        }

        if ($now->gt($deadline)) {
            return ['status' => 'expired'];
        }

        return ['status' => 'pending'];
    }
}
