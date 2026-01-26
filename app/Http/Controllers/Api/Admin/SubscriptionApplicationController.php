<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\SubscriptionApplication;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = SubscriptionApplication::query();

        if ($request->filled('domain')) {
            $q->where('domain', 'like', '%' . $request->input('domain') . '%');
        }
        if ($request->filled('status')) {
            $q->where('status', $request->input('status'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $q->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function approve(Request $request, SubscriptionApplication $application): JsonResponse
    {
        Log::info('Попытка одобрения заявки', [
            'application_id' => $application->id,
            'status' => $application->status,
            'expires_at' => $application->expires_at?->toDateTimeString(),
            'user_id' => $request->user()?->id,
        ]);

        // Проверка статуса заявки
        if (!$application->isPending()) {
            Log::warning('Попытка одобрить уже обработанную заявку', [
                'application_id' => $application->id,
                'current_status' => $application->status,
            ]);
            return response()->json([
                'message' => 'Заявка уже обработана',
                'errors' => [
                    'status' => ['Текущий статус заявки: ' . $application->status]
                ]
            ], 422);
        }
        
        // Проверка срока действия
        if ($application->isExpired()) {
            Log::warning('Попытка одобрить истёкшую заявку', [
                'application_id' => $application->id,
                'expires_at' => $application->expires_at?->toDateTimeString(),
            ]);
            return response()->json([
                'message' => 'Срок действия заявки истёк',
                'errors' => [
                    'expires_at' => ['Срок действия истёк: ' . ($application->expires_at ? $application->expires_at->format('Y-m-d H:i:s') : 'не установлен')]
                ]
            ], 422);
        }

        // Получение плана
        $planId = $request->input('plan_id');
        if (!$planId) {
            $planId = Plan::where('name', 'standard')->value('id');
            if (!$planId) {
                return response()->json([
                    'message' => 'План "standard" не найден в базе данных',
                    'errors' => [
                        'plan' => ['Создайте план "standard" в базе данных или укажите plan_id в запросе']
                    ]
                ], 422);
            }
        }
        
        $plan = Plan::find($planId);
        if (!$plan) {
            return response()->json([
                'message' => 'План не найден',
                'errors' => [
                    'plan_id' => ['План с ID ' . $planId . ' не существует']
                ]
            ], 422);
        }
        
        if (!$plan->is_active) {
            return response()->json([
                'message' => 'План неактивен',
                'errors' => [
                    'plan' => ['План "' . $plan->name . '" неактивен. Активируйте план или выберите другой.']
                ]
            ], 422);
        }

        $start = now();
        $end = $start->copy()->addYear();

        $subscriber = Subscriber::create([
            'domain' => $application->domain,
            'login' => $application->email,
            'subscription_start' => $start,
            'subscription_end' => $end,
            'is_active' => true,
            'plan_id' => $plan->id,
            'api_token' => $application->api_token,
            'payment_data' => null,
        ]);

        $application->update(['status' => SubscriptionApplication::STATUS_APPROVED]);

        $subscriber->load('plan');

        return response()->json([
            'message' => 'Заявка одобрена',
            'data' => [
                'application' => $application->fresh(),
                'subscriber' => $subscriber,
            ],
        ]);
    }

    public function reject(SubscriptionApplication $application): JsonResponse
    {
        if (!$application->isPending()) {
            return response()->json(['message' => 'Заявка уже обработана'], 422);
        }

        $application->update(['status' => SubscriptionApplication::STATUS_REJECTED]);

        return response()->json([
            'message' => 'Заявка отклонена',
            'data' => ['application' => $application->fresh()],
        ]);
    }
}
