<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotService;
use App\Models\TelegramBotServiceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    /**
     * Список категорий, сортировка по sort_order, затем id.
     */
    public function categories(): JsonResponse
    {
        $items = TelegramBotServiceCategory::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        return response()->json(['data' => $items]);
    }

    /**
     * Список услуг. Если передан category_id — фильтр по категории.
     */
    public function services(Request $request): JsonResponse
    {
        $query = TelegramBotService::query()->with('category')->orderBy('sort_order')->orderBy('id');
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        $items = $query->get();
        return response()->json(['data' => $items]);
    }
}
