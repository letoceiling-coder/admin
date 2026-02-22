<?php

use App\Http\Controllers\Api\Admin\CommercialProposalController as AdminCommercialProposalController;
use App\Http\Controllers\Api\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Api\Admin\SubscriberController as AdminSubscriberController;
use App\Http\Controllers\Api\Admin\SubscriptionApplicationController as AdminSubscriptionApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeployController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Telegram\CallRequestController as TelegramCallRequestController;
use App\Http\Controllers\Api\Telegram\CaseController as TelegramCaseController;
use App\Http\Controllers\Api\Telegram\EventController as TelegramEventController;
use App\Http\Controllers\Api\Telegram\FaqController as TelegramFaqController;
use App\Http\Controllers\Api\Telegram\LeadController as TelegramLeadController;
use App\Http\Controllers\Api\Telegram\ReviewController as TelegramReviewController;
use App\Http\Controllers\Api\Telegram\ServiceCategoryController as TelegramServiceCategoryController;
use App\Http\Controllers\Api\Telegram\SettingsController as TelegramSettingsController;
use App\Http\Controllers\Api\Telegram\TicketController as TelegramTicketController;
use App\Http\Controllers\Api\V1\SubscriptionApplicationController;
use App\Http\Controllers\Api\SubscriptionController;
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
    // Получение информации о подписке (для CRM)
    Route::get('subscription', [SubscriptionController::class, 'show']);
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
    Route::post('plans', [AdminPlanController::class, 'store']);
    Route::get('plans/{plan}', [AdminPlanController::class, 'show']);
    Route::put('plans/{plan}', [AdminPlanController::class, 'update']);
    Route::delete('plans/{plan}', [AdminPlanController::class, 'destroy']);
    Route::get('subscribers', [AdminSubscriberController::class, 'index']);
    Route::get('subscribers/{subscriber}', [AdminSubscriberController::class, 'show']);
    Route::put('subscribers/{subscriber}', [AdminSubscriberController::class, 'update']);
    Route::get('subscription-applications', [AdminSubscriptionApplicationController::class, 'index']);
    Route::post('subscription-applications/{application}/approve', [AdminSubscriptionApplicationController::class, 'approve']);
    Route::post('subscription-applications/{application}/reject', [AdminSubscriptionApplicationController::class, 'reject']);
    Route::get('commercial-proposal/preview', [AdminCommercialProposalController::class, 'preview']);
    Route::get('commercial-proposal/mailings', [AdminCommercialProposalController::class, 'index']);
    Route::post('commercial-proposal/send', [AdminCommercialProposalController::class, 'send']);
    Route::post('commercial-proposal/resend', [AdminCommercialProposalController::class, 'resend']);
});

/* Публичная отписка от рассылки КП (подписанная ссылка из письма) */
Route::get('commercial-proposal/unsubscribe', [AdminCommercialProposalController::class, 'unsubscribe'])
    ->name('api.commercial-proposal.unsubscribe')
    ->middleware('throttle:30,1');

/* Маршрут для деплоя (защищен токеном) */
Route::post('/deploy', [DeployController::class, 'deploy'])
    ->middleware('deploy.token');

/* /api/telegram/* — для микросервиса бота: Bearer CRM_BOT_API_TOKEN + rate limit */
Route::prefix('telegram')->middleware(['telegram.bot.token', 'throttle:telegram-bot'])->group(function () {
    Route::get('settings', [TelegramSettingsController::class, 'index']);
    Route::get('services/categories', [TelegramServiceCategoryController::class, 'categories']);
    Route::get('services', [TelegramServiceCategoryController::class, 'services']);
    Route::get('cases', [TelegramCaseController::class, 'index']);
    Route::get('cases/{id}', [TelegramCaseController::class, 'show']);
    Route::get('reviews', [TelegramReviewController::class, 'index']);
    Route::get('faq', [TelegramFaqController::class, 'index']);
    Route::post('leads', [TelegramLeadController::class, 'store']);
    Route::post('call-requests', [TelegramCallRequestController::class, 'store']);
    Route::post('tickets', [TelegramTicketController::class, 'store']);
    Route::post('reviews', [TelegramReviewController::class, 'store']);
    Route::post('events', [TelegramEventController::class, 'store']);
});
