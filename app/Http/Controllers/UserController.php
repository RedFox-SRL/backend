<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use App\ApiCode;

class UserController extends Controller
{
    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        $user->update($request->validated());

        return $this->respond(['user' => $user], 'Profile updated successfully.');
    }
}

