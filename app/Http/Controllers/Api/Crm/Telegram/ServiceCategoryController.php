<?php

namespace App\Http\Controllers\Api\Crm\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotServiceCategory;
use App\Http\Requests\Crm\Telegram\StoreServiceCategoryRequest;
use App\Http\Requests\Crm\Telegram\UpdateServiceCategoryRequest;
use Illuminate\Http\JsonResponse;

class ServiceCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $items = TelegramBotServiceCategory::query()->orderBy('sort_order')->orderBy('id')->get();
        return response()->json(['data' => $items]);
    }

    public function store(StoreServiceCategoryRequest $request): JsonResponse
    {
        $item = TelegramBotServiceCategory::create($request->validated());
        return response()->json(['data' => $item], 201);
    }

    public function update(UpdateServiceCategoryRequest $request, int $id): JsonResponse
    {
        $item = TelegramBotServiceCategory::findOrFail($id);
        $item->update($request->validated());
        return response()->json(['data' => $item]);
    }

    public function destroy(int $id): JsonResponse
    {
        $item = TelegramBotServiceCategory::findOrFail($id);
        $item->delete();
        return response()->json(['data' => ['id' => $id]]);
    }
}
