<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\SubscriptionApplication;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        if (!$application->isPending()) {
            return response()->json(['message' => 'Заявка уже обработана'], 422);
        }
        if ($application->isExpired()) {
            return response()->json(['message' => 'Срок действия заявки истёк'], 422);
        }

        $planId = $request->input('plan_id');
        if (!$planId) {
            $planId = Plan::where('name', 'standard')->value('id');
        }
        $plan = Plan::find($planId);
        if (!$plan || !$plan->is_active) {
            return response()->json(['message' => 'Неверный или неактивный план'], 422);
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
