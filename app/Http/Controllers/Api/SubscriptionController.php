<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\SubscriptionApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Получить информацию о подписке по домену или api_token
     * Используется CRM для получения актуальной информации о подписке
     */
    public function show(Request $request): JsonResponse
    {
        $domain = $request->input('domain');
        $apiToken = $request->input('api_token');

        if (!$domain && !$apiToken) {
            return response()->json([
                'success' => false,
                'message' => 'Необходимо указать domain или api_token',
            ], 400);
        }

        // Ищем подписчика (активную подписку)
        // Приоритет: сначала ищем активного, затем любого по домену
        $subscriber = null;
        if ($apiToken) {
            $subscriber = Subscriber::where('api_token', $apiToken)->first();
        } elseif ($domain) {
            // Сначала ищем активного подписчика
            $subscriber = Subscriber::where('domain', $domain)
                ->where('is_active', true)
                ->orderBy('subscription_end', 'desc')
                ->first();
            
            // Если активного нет, ищем любого (на случай если статус изменился)
            if (!$subscriber) {
                $subscriber = Subscriber::where('domain', $domain)
                    ->orderBy('subscription_end', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        }

        // Ищем заявку на подписку
        $application = null;
        if ($apiToken) {
            $application = SubscriptionApplication::where('api_token', $apiToken)->first();
        } elseif ($domain) {
            $application = SubscriptionApplication::where('domain', $domain)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        // Если есть активный подписчик - возвращаем его данные
        if ($subscriber) {
            $subscriber->load('plan');
            
            // Всегда возвращаем полный токен для синхронизации с CRM
            // CRM использует этот токен для последующих запросов
            $returnToken = $subscriber->api_token;
            
            // Определяем статус: если is_active = true, то 'active', иначе 'inactive'
            $status = $subscriber->is_active ? 'active' : 'inactive';
            
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $status,
                    'domain' => $subscriber->domain,
                    'api_token' => $returnToken,
                    'subscription_start' => $subscriber->subscription_start?->toDateString(),
                    'subscription_end' => $subscriber->subscription_end?->toDateString(),
                    'expires_at' => $subscriber->subscription_end?->toDateTimeString(),
                    'is_active' => $subscriber->is_active,
                    'plan' => $subscriber->plan ? [
                        'id' => $subscriber->plan->id,
                        'name' => $subscriber->plan->name,
                        'cost' => $subscriber->plan->cost,
                        'is_active' => $subscriber->plan->is_active,
                        'limits' => $subscriber->plan->limits,
                    ] : null,
                ],
            ]);
        }

        // Если есть заявка - возвращаем её данные
        if ($application) {
            // Если запрос с api_token, возвращаем полный токен
            $returnToken = $application->api_token;
            // Если запрос по домену без токена, возвращаем частично скрытый
            if (!$apiToken && $domain && $application->api_token) {
                $returnToken = substr($application->api_token, 0, 10) . '...';
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $application->status,
                    'domain' => $application->domain,
                    'api_token' => $returnToken,
                    'expires_at' => $application->expires_at?->toDateTimeString(),
                    'is_active' => $application->status === SubscriptionApplication::STATUS_APPROVED && !$application->isExpired(),
                    'application_id' => $application->id,
                ],
            ]);
        }

        // Если ничего не найдено
        return response()->json([
            'success' => false,
            'message' => 'Подписка не найдена',
            'data' => [
                'status' => 'not_found',
                'domain' => $domain,
                'is_active' => false,
            ],
        ], 404);
    }
}
