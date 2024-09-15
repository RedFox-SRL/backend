<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function register(RegistrationRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create($request->getAttributes());
            $user->sendEmailVerificationNotification();

            if ($user->role == 'student') {
                Student::create(['user_id' => $user->id]);
            } elseif ($user->role == 'teacher') {
                Teacher::create(['user_id' => $user->id]);
            }
        });

        return $this->respondWithMessage('User registered successfully');
    }
}
