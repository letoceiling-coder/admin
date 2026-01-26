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
}
