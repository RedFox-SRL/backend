<?php

namespace App\Http\Controllers;

use App\Models\Management;
use App\Models\Group;
use App\Models\ProposalSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\ApiCode;

class ProposalSubmissionController extends Controller
{
    public function submitPartA(Request $request)
    {
        return $this->submitProposal($request, 'a');
    }

    public function submitPartB(Request $request)
    {
        return $this->submitProposal($request, 'b');
    }

    private function submitProposal(Request $request, $part)
    {
        $request->validate([
            'file' => 'required|file|mimes:doc,docx,pdf|max:10240', // 10MB max
        ]);

        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $group = $student->groups()->first();

        if (!$group || $group->creator_id !== $student->id) {
            return $this->respondUnAuthorizedRequest(ApiCode::NOT_GROUP_REPRESENTATIVE);
        }

        $management = $group->management;

        $deadlineField = "proposal_part_{$part}_deadline";
        $fileField = "part_{$part}_file";
        $submittedAtField = "part_{$part}_submitted_at";

        if (!$management->$deadlineField) {
            return $this->respondBadRequest(ApiCode::SUBMISSION_NOT_AVAILABLE);
        }

        $now = Carbon::now();
        if ($now->gt($management->$deadlineField)) {
            return $this->respondBadRequest(ApiCode::SUBMISSION_DEADLINE_PASSED);
        }

        $submission = ProposalSubmission::firstOrCreate(['group_id' => $group->id]);

        if ($submission->$fileField) {
            return $this->respondBadRequest(ApiCode::SUBMISSION_ALREADY_MADE);
        }

        $file = $request->file('file');
        $path = Storage::putFile('proposal_submissions', $file);

        $submission->$fileField = $path;
        $submission->$submittedAtField = $now;
        $submission->save();

        return $this->respond([
            'message' => "Proposal part {$part} submitted successfully",
            'file_url' => Storage::url($path),
            'submitted_at' => $now
        ]);
    }

    public function getGroupSubmission()
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $group = $student->groups()->first();

        if (!$group) {
            return $this->respondNotFound(ApiCode::GROUP_NOT_FOUND);
        }

        $management = $group->management;
        $submission = ProposalSubmission::where('group_id', $group->id)->first();

        $response = [
            'part_a' => $this->getSubmissionStatus($submission, $management, 'a'),
            'part_b' => $this->getSubmissionStatus($submission, $management, 'b'),
        ];

        return $this->respond($response);
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
