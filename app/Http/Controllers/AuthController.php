<?php

namespace App\Http\Controllers;

use App\ApiCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeEmail;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'verifyCode', 'checkToken']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->respondBadRequest(ApiCode::USER_NOT_FOUND);
        }

        $user->generateVerificationCode();
        if (!$user->isTestEmail()) {
            Mail::to($user->email)->send(new VerificationCodeEmail($user));
        }

        return $this->respondWithMessage('Código de verificación enviado al correo electrónico');
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('verification_code', $request->verification_code)
            ->where('verification_code_expires_at', '>', now())
            ->first();

        if (!$user) {
            return $this->respondBadRequest(ApiCode::INVALID_VERIFICATION_CODE);
        }

        $token = auth()->login($user);
        return $this->respondWithTokenAndRole($token, $user->role);
    }

    public function logout()
    {
        auth()->logout();
        return $this->respondWithMessage('Cierre de sesión exitoso');
    }

    public function refresh()
    {
        return $this->respondWithTokenAndRole(auth()->refresh());
    }

    public function me()
    {
        $user = auth()->user();

        if (!$user) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $user->role,
        ];

        if ($user->role === 'teacher' && $user->teacher) {
            $data['teacher_id'] = $user->teacher->id;
        } elseif ($user->role === 'student' && $user->student) {
            $data['student_id'] = $user->student->id;
        }

        return $this->respond(['item' => $data]);
    }

    private function respondWithTokenAndRole($token, $role): \Symfony\Component\HttpFoundation\Response
    {
        return $this->respond([
            'token' => $token,
            'access_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL(),
            'role' => $role
        ]);
    }

    public function checkToken(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json(['valid' => true]);
        } catch (JWTException $e) {
            return response()->json(['valid' => false], 401);
        }
    }
}
