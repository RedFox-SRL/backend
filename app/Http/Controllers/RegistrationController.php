<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\ApiCode;

class RegistrationController extends Controller
{
    public function register(RegistrationRequest $request)
    {
        $user = null;

        DB::transaction(function () use ($request, &$user) {
            $email = $request->email;
            $role = $this->determineRole($email);

            $user = User::create([
                'name' => $request->name,
                'last_name' => $request->last_name,
                'email' => $email,
                'role' => $role,
            ]);

            if ($role === 'student') {
                Student::create(['user_id' => $user->id]);
            } elseif ($role === 'teacher') {
                Teacher::create(['user_id' => $user->id]);
            }

            Mail::to($user->email)->send(new WelcomeEmail($user));
        });

        if (!$user) {
            return $this->respondBadRequest(ApiCode::USER_NOT_FOUND);
        }

        return $this->respondWithMessage('Usuario registrado exitosamente');
    }

    private function determineRole($email)
    {
        if (preg_match('/^20\d{7}@est\.umss\.edu$/', $email)) {
            return 'student';
        } elseif (preg_match('/@fcyt\.umss\.edu\.bo$/', $email)) {
            return 'teacher';
        }

        throw new \InvalidArgumentException('Correo electrónico no válido para registro');
    }
}

