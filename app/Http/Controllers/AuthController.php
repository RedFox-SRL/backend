<?php

namespace App\Http\Controllers;

use App\ApiCode;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return $this->respondUnAuthenticated(ApiCode::INVALID_CREDENTIALS);
        }

        $user = auth()->user();
        return $this->respondWithTokenAndRole($token, $user->role);
    }

    private function respondWithToken($token)
    {
        return $this->respond([
            'token' => $token,
            'access_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL(),
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return $this->respondWithMessage('Cierre de sesión exitoso');
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
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
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'role' => $user->role,
        ];

        if ($user->role === 'teacher' && $user->teacher) {
            $data['teacher_id'] = $user->teacher->id;
        } elseif ($user->role === 'student' && $user->student) {
            $data['student_id'] = $user->student->id;
        }

        return $this->respond(['item' => $data]);
    }

    private function respondWithTokenAndRole($token, $role)
    {
        return $this->respond([
            'token' => $token,
            'access_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL(),
            'role' => $role
        ]);
    }
}
