<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    public function register(RegistrationRequest $request)
    {
        $user = null;

        DB::transaction(function () use ($request, &$user) {
            $user = User::create($request->getAttributes());

            if ($user->role == 'student') {
                Student::create(['user_id' => $user->id]);
            } elseif ($user->role == 'teacher') {
                Teacher::create(['user_id' => $user->id]);
            }

            Mail::to($user->email)->send(new WelcomeEmail($user));
        });

        return $this->respondWithMessage('User registered successfully');
    }
}
