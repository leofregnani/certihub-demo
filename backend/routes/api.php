<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PublicCertificateController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
});

Route::get('public/certificates/{code}', [PublicCertificateController::class, 'show'])
    ->middleware('throttle:30,1');

/*
|--------------------------------------------------------------------------
| Authenticated routes (admin and regular users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::get('dashboard', DashboardController::class);

    Route::apiResource('participants', ParticipantController::class)->except('destroy');
    Route::apiResource('courses', CourseController::class)->except('destroy');

    Route::get('certificates', [CertificateController::class, 'index']);
    Route::post('certificates', [CertificateController::class, 'store']);
    Route::get('certificates/{certificate}', [CertificateController::class, 'show']);
    Route::get('certificates/{certificate}/pdf', [CertificateController::class, 'pdf']);

    /*
    |----------------------------------------------------------------------
    | Admin-only routes
    |----------------------------------------------------------------------
    */
    Route::middleware(EnsureAdmin::class)->group(function () {
        Route::delete('participants/{participant}', [ParticipantController::class, 'destroy']);
        Route::delete('courses/{course}', [CourseController::class, 'destroy']);
        Route::post('certificates/{certificate}/revoke', [CertificateController::class, 'revoke']);
        Route::delete('certificates/{certificate}', [CertificateController::class, 'destroy']);

        Route::apiResource('users', UserController::class);
        Route::get('logs', [ActivityLogController::class, 'index']);
    });
});
