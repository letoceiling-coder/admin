<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Subscriber::query()->with('plan');

        if ($request->filled('domain')) {
            $q->where('domain', 'like', '%' . $request->input('domain') . '%');
        }
        if ($request->filled('plan_id')) {
            $q->where('plan_id', $request->input('plan_id'));
        }
        if ($request->has('is_active')) {
            $q->where('is_active', $request->boolean('is_active'));
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

    public function show(Subscriber $subscriber): JsonResponse
    {
        $subscriber->load('plan');
        $subscriber->makeVisible('api_token');

        return response()->json(['data' => $subscriber]);
    }

    /**
     * Обновление диапазона подписки (начало, конец) и активности.
     */
    public function update(Request $request, Subscriber $subscriber): JsonResponse
    {
        $validated = $request->validate([
            'subscription_start' => 'nullable|date',
            'subscription_end' => [
                'nullable',
                'date',
                function (string $attr, $value, \Closure $fail) use ($request): void {
                    $start = $request->input('subscription_start');
                    if ($value && $start && strtotime($value) < strtotime($start)) {
                        $fail('Конец подписки должен быть не раньше начала.');
                    }
                },
            ],
            'is_active' => 'boolean',
        ]);

        if (array_key_exists('subscription_start', $validated)) {
            $subscriber->subscription_start = $validated['subscription_start'];
        }
        if (array_key_exists('subscription_end', $validated)) {
            $subscriber->subscription_end = $validated['subscription_end'];
        }
        if (array_key_exists('is_active', $validated)) {
            $subscriber->is_active = $request->boolean('is_active');
        }
        $subscriber->save();

        $subscriber->load('plan');
        $subscriber->makeVisible('api_token');

        return response()->json(['data' => $subscriber]);
    }
}
