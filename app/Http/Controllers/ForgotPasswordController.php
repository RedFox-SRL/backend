<?php

namespace App\Http\Controllers;

use App\ApiCode;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function forgot()
    {
        $credentials = request()->validate(['email' => 'required|email']);

        $response = Password::sendResetLink($credentials);

        if ($response == Password::INVALID_USER) {
            return $this->respondBadRequest(ApiCode::EMAIL_NOT_FOUND);
        }

        return $this->respondWithMessage('Enlace para restablecer la contraseña enviado a tu correo electrónico.');
    }

    public function reset(ResetPasswordRequest $request)
    {
        $email_password_status = Password::reset($request->validated(), function ($user, $password) {
            $user->password = $password;
            $user->save();
        });

        if ($email_password_status == Password::INVALID_TOKEN) {
            return $this->respondBadRequest(ApiCode::INVALID_RESET_PASSWORD_TOKEN);
        }

        return $this->respondWithMessage("Contraseña cambiada con éxito");
    }
}
