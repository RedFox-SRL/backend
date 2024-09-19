<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\StudentManagementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['api'])->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me'])->middleware('log.route');

    Route::post('register', [RegistrationController::class, 'register']);
    Route::get('email/verify/{id}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::get('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

    Route::post('password/email', [ForgotPasswordController::class, 'forgot']);
    Route::post('password/reset', [ForgotPasswordController::class, 'reset']);

    Route::put('profile', [UserController::class, 'update']);

    Route::get('managements', [ManagementController::class, 'index']);
    Route::post('managements', [ManagementController::class, 'create']);
    Route::get('student/management', [ManagementController::class, 'getStudentManagement']);
    Route::put('managements/{management}/toggle-code', [ManagementController::class, 'toggleCode']);
    Route::put('managements/{management}/update-group-limit', [ManagementController::class, 'updateGroupLimit']);
    Route::post('managements/join', [StudentManagementController::class, 'join']);
    Route::post('managements/leave', [StudentManagementController::class, 'leaveManagement']);
    Route::post('groups', [GroupController::class, 'create']);
    Route::get('groups/details', [GroupController::class, 'getGroupDetails']);
    Route::post('groups/{group}/leave', [GroupController::class, 'leaveGroup']);
    Route::get('managements/{management}/groups', [GroupController::class, 'getGroupsByManagement']);
    Route::post('groups/join', [GroupController::class, 'joinGroup']);
});
