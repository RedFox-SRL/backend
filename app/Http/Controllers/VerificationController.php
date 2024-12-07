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
        $user = auth()->user();

        if (!$user) {
            return $this->respondUnAuthorizedRequest(ApiCode::INVALID_CREDENTIALS);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->respondBadRequest(ApiCode::EMAIL_ALREADY_VERIFIED);
        }

        $user->sendEmailVerificationNotification();

        return $this->respondWithMessage('Enlace de verificación de correo electrónico enviado a tu dirección de correo electrónico');
    }
}
