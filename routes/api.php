<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\StudentManagementController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\WeeklyEvaluationController;
use App\Http\Controllers\SprintEvaluationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::get('managements/{id}/students', [ManagementController::class, 'getManagementDetails']);
    Route::put('managements/{management}/toggle-code', [ManagementController::class, 'toggleCode']);
    Route::put('managements/{management}/update-group-limit', [ManagementController::class, 'updateGroupLimit']);
    Route::post('managements/join', [StudentManagementController::class, 'join']);
    Route::post('managements/leave', [StudentManagementController::class, 'leaveManagement']);

    Route::post('groups', [GroupController::class, 'create']);
    Route::get('groups/details', [GroupController::class, 'getGroupDetails']);
    Route::post('groups/leave', [GroupController::class, 'leaveGroup']);
    Route::get('managements/{management}/groups', [GroupController::class, 'getGroupsByManagement']);
    Route::post('groups/join', [GroupController::class, 'joinGroup']);
    Route::post('groups/remove-member/{memberId}', [GroupController::class, 'removeMember']);
    Route::post('groups/assign-role/{memberId}', [GroupController::class, 'assignRole']);
    Route::get('groups/{groupId}/members', [GroupController::class, 'getGroupMembersWithRoles']);
    Route::put('groups/contact-info', [GroupController::class, 'updateContactInfo']);
    Route::get('/group-names', [GroupController::class, 'getGroupNames']);

    Route::get('/sprints', [SprintController::class, 'index']);
    Route::post('/sprints', [SprintController::class, 'store']);
    Route::get('/sprints/{id}', [SprintController::class, 'show']);
    Route::put('/sprints/{id}', [SprintController::class, 'update']);
    Route::delete('/sprints/{id}', [SprintController::class, 'destroy']);
    Route::get('/sprints/{id}/tasks', [SprintController::class, 'getTasks']);
    Route::get('/sprints/{id}/evaluation-summary', [SprintController::class, 'getEvaluationSummary']);

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    Route::post('/announcements', [AnnouncementController::class, 'store']);
    Route::get('/management/{managementId}/announcements', [AnnouncementController::class, 'index']);

    Route::get('/sprints/{id}/weekly-evaluation-template', [WeeklyEvaluationController::class, 'getEvaluationTemplate']);
    Route::post('/sprints/{id}/weekly-evaluation', [WeeklyEvaluationController::class, 'create']);
    Route::get('/sprints/{id}/weekly-evaluations', [WeeklyEvaluationController::class, 'getWeeklyEvaluations']);

    Route::get('/sprints/{id}/sprint-evaluation-template', [SprintEvaluationController::class, 'getEvaluationTemplate']);
    Route::post('/sprints/{id}/sprint-evaluation', [SprintEvaluationController::class, 'create']);
    Route::get('/sprints/{id}/sprint-evaluation', [SprintEvaluationController::class, 'getFinalEvaluation']);
});
