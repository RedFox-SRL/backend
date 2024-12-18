<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;

class Authenticate extends Middleware
{
    protected function unauthenticated($request, array $guards)
    {
        abort(response()->json(['message' => 'Unauthenticated.'], 401));
    }
}
