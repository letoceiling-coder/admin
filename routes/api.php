<?php

use App\Http\Controllers\Api\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Api\Admin\SubscriberController as AdminSubscriberController;
use App\Http\Controllers\Api\Admin\SubscriptionApplicationController as AdminSubscriptionApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeployController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\V1\SubscriptionApplicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->load('role');
});

/* API v1 — публичный приём заявок от CRM */
Route::prefix('v1')->middleware('throttle:30,1')->group(function () {
    Route::get('ping', function () {
        return response()->json([
            'ok' => true,
            'db' => config('database.connections.mysql.database'),
        ]);
    });
    Route::post('subscription-applications', [SubscriptionApplicationController::class, 'store']);
});

/* Auth — всегда доступны (без auth middleware) */
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);
});

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->get('/notifications', [NotificationController::class, 'index']);
Route::middleware('auth:sanctum')->patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

/* /admin/* API — только manager и administrator */
Route::middleware(['auth:sanctum', 'admin.access'])->prefix('admin')->group(function () {
    Route::get('plans', [AdminPlanController::class, 'index']);
    Route::get('subscribers', [AdminSubscriberController::class, 'index']);
    Route::get('subscribers/{subscriber}', [AdminSubscriberController::class, 'show']);
    Route::get('subscription-applications', [AdminSubscriptionApplicationController::class, 'index']);
    Route::post('subscription-applications/{application}/approve', [AdminSubscriptionApplicationController::class, 'approve']);
    Route::post('subscription-applications/{application}/reject', [AdminSubscriptionApplicationController::class, 'reject']);
});

/* Маршрут для деплоя (защищен токеном) */
Route::post('/deploy', [DeployController::class, 'deploy'])
    ->middleware('deploy.token');
