<?php

namespace App\Http\Controllers;

use App\Models\ProjectLink;
use Illuminate\Http\Request;
use App\ApiCode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProjectLinkController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        if (!$user->student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $group = $user->student->groups()->first();

        if (!$group) {
            return $this->respondNotFound(ApiCode::GROUP_NOT_FOUND);
        }

        if ($group->creator_id !== $user->student->id) {
            return $this->respondBadRequest(ApiCode::NOT_GROUP_REPRESENTATIVE);
        }

        $validator = Validator::make($request->all(), [
            'links' => 'required|array',
            'links.*.url' => [
                'required',
                'url',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidUrl($value)) {
                        $fail($attribute . ' is not a valid URL.');
                    }
                },
            ],
            'links.*.category' => 'required|string|in:documentation,source_code,deployment,design,presentation,report,credentials,other',
            'links.*.description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->respondBadRequest(ApiCode::VALIDATION_ERROR, $validator->errors());
        }

        $createdLinks = [];

        foreach ($request->links as $linkData) {
            $createdLinks[] = $group->projectLinks()->create($linkData);
        }

        return $this->respond(['links' => $createdLinks], 'Project links have been added successfully.');
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        $group = $user->student->groups()->first();

        if (!$group) {
            return $this->respondNotFound(ApiCode::GROUP_NOT_FOUND);
        }

        $links = $group->projectLinks;

        return $this->respond(['links' => $links]);
    }

    public function update(Request $request, $linkId)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        if (!$user->student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $group = $user->student->groups()->first();

        if (!$group) {
            return $this->respondNotFound(ApiCode::GROUP_NOT_FOUND);
        }

        if ($group->creator_id !== $user->student->id) {
            return $this->respondBadRequest(ApiCode::NOT_GROUP_REPRESENTATIVE);
        }

        $link = ProjectLink::find($linkId);

        if (!$link) {
            return $this->respondNotFound(ApiCode::LINK_NOT_FOUND);
        }

        if ($link->group_id !== $group->id) {
            return $this->respondBadRequest(ApiCode::LINK_NOT_BELONG_TO_GROUP);
        }

        $validator = Validator::make($request->all(), [
            'url' => [
                'required',
                'url',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidUrl($value)) {
                        $fail($attribute . ' is not a valid URL.');
                    }
                },
            ],
            'category' => 'required|string|in:documentation,source_code,deployment,design,presentation,report,credentials,other',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->respondBadRequest(ApiCode::VALIDATION_ERROR, $validator->errors());
        }

        $link->update($request->all());

        return $this->respond(['link' => $link], 'Project link has been updated successfully.');
    }

    public function destroy($linkId)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        if (!$user->student) {
            return $this->respondBadRequest(ApiCode::NOT_A_STUDENT);
        }

        $group = $user->student->groups()->first();

        if (!$group) {
            return $this->respondNotFound(ApiCode::GROUP_NOT_FOUND);
        }

        if ($group->creator_id !== $user->student->id) {
            return $this->respondBadRequest(ApiCode::NOT_GROUP_REPRESENTATIVE);
        }

        $link = ProjectLink::find($linkId);

        if (!$link) {
            return $this->respondNotFound(ApiCode::LINK_NOT_FOUND);
        }

        if ($link->group_id !== $group->id) {
            return $this->respondBadRequest(ApiCode::LINK_NOT_BELONG_TO_GROUP);
        }

        $link->delete();

        return $this->respond(null, 'Project link has been deleted successfully.');
    }

    private function isValidUrl($url)
    {
        $parsed = parse_url($url);
        if ($parsed === false || !isset($parsed['scheme']) || !isset($parsed['host'])) {
            return false;
        }
        return filter_var($url, FILTER_VALIDATE_URL) !== false &&
            in_array($parsed['scheme'], ['http', 'https']) &&
            checkdnsrr($parsed['host'], 'A');
    }
}
