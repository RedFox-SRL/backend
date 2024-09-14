<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ApiCode;
use App\Models\User;

class VerificationController extends Controller
{
    public function verify($user_id, Request $request)
    {
        if (!$request->hasValidSignature()) {
            return $this->respondUnAuthorizedRequest(ApiCode::INVALID_EMAIL_VERIFICATION_URL);
        }

        $user = User::findOrFail($user_id);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->to('/');

    }

    public function resend()
    {
        if (auth()->user()->hasVerifiedEmail()) {
            return $this->respondBadRequest(ApiCode::EMAIL_ALREADY_VERIFIED);
        }

        auth()->user()->sendEmailVerificationNotification();

        return $this->respondWithMessage('Email verification link sent on your email id');
    }
}
