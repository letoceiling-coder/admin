<?php

namespace App\Http\Controllers\Api\Crm\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotFaq;
use App\Http\Requests\Crm\Telegram\StoreFaqRequest;
use App\Http\Requests\Crm\Telegram\UpdateFaqRequest;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function index(): JsonResponse
    {
        $items = TelegramBotFaq::query()->orderBy('sort_order')->orderBy('id')->get();
        return response()->json(['data' => $items]);
    }

    public function store(StoreFaqRequest $request): JsonResponse
    {
        $item = TelegramBotFaq::create($request->validated());
        return response()->json(['data' => $item], 201);
    }

    public function update(UpdateFaqRequest $request, int $id): JsonResponse
    {
        $item = TelegramBotFaq::findOrFail($id);
        $item->update($request->validated());
        return response()->json(['data' => $item]);
    }

    public function destroy(int $id): JsonResponse
    {
        $item = TelegramBotFaq::findOrFail($id);
        $item->delete();
        return response()->json(['data' => ['id' => $id]]);
    }
}
