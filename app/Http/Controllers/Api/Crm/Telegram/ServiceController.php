<?php

namespace App\Http\Controllers\Api\Crm\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotService;
use App\Http\Requests\Crm\Telegram\StoreServiceRequest;
use App\Http\Requests\Crm\Telegram\UpdateServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TelegramBotService::query()->with('category')->orderBy('sort_order')->orderBy('id');
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        $items = $query->get();
        return response()->json(['data' => $items]);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $item = TelegramBotService::create($request->validated());
        return response()->json(['data' => $item->load('category')], 201);
    }

    public function update(UpdateServiceRequest $request, int $id): JsonResponse
    {
        $item = TelegramBotService::findOrFail($id);
        $item->update($request->validated());
        return response()->json(['data' => $item->load('category')]);
    }

    public function destroy(int $id): JsonResponse
    {
        $item = TelegramBotService::findOrFail($id);
        $item->delete();
        return response()->json(['data' => ['id' => $id]]);
    }
}
