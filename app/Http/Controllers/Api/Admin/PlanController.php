<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Список планов (все, включая неактивные). Для выбора в формах — фильтр ?active_only=1.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Plan::query()->orderBy('id');
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }
        $plans = $query->get();

        return response()->json(['data' => $plans]);
    }

    public function show(Plan $plan): JsonResponse
    {
        return response()->json(['data' => $plan]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:plans,name',
            'cost' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'limits' => 'nullable|array',
        ], [
            'name.required' => 'Укажите название плана.',
            'name.unique' => 'План с таким названием уже существует.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['limits'] = $validated['limits'] ?? null;
        $plan = Plan::create($validated);

        return response()->json(['data' => $plan], 201);
    }

    public function update(Request $request, Plan $plan): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:plans,name,' . $plan->id,
            'cost' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'limits' => 'nullable|array',
        ], [
            'name.required' => 'Укажите название плана.',
            'name.unique' => 'План с таким названием уже существует.',
        ]);

        $plan->update([
            'name' => $validated['name'],
            'cost' => $validated['cost'],
            'is_active' => $request->boolean('is_active', true),
            'limits' => $validated['limits'] ?? $plan->limits,
        ]);

        return response()->json(['data' => $plan->fresh()]);
    }

    public function destroy(Plan $plan): JsonResponse
    {
        $count = $plan->subscribers()->count();
        if ($count > 0) {
            return response()->json([
                'message' => 'Невозможно удалить план: к нему привязаны подписчики.',
                'subscribers_count' => $count,
            ], 422);
        }

        $plan->delete();
        return response()->json(['message' => 'План удалён.']);
    }
}
