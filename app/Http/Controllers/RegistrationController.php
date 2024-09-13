<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use Illuminate\Http\Request;
use App\Models\User;

class RegistrationController extends Controller
{
    public function register(RegistrationRequest $request)
    {
        User::create($request->getAttributes());

        return $this->respondWithMessage('User registered successfully');
    }
}
