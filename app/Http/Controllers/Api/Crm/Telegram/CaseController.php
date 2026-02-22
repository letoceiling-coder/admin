<?php

namespace App\Http\Controllers\Api\Crm\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotCase;
use App\Models\TelegramBotCaseMedia;
use App\Http\Requests\Crm\Telegram\StoreCaseRequest;
use App\Http\Requests\Crm\Telegram\UpdateCaseRequest;
use App\Http\Requests\Crm\Telegram\StoreCaseMediaRequest;
use App\Http\Requests\Crm\Telegram\UpdateCaseMediaRequest;
use Illuminate\Http\JsonResponse;

class CaseController extends Controller
{
    public function index(): JsonResponse
    {
        $items = TelegramBotCase::query()->orderBy('sort_order')->orderBy('id')->get();
        return response()->json(['data' => $items]);
    }

    public function store(StoreCaseRequest $request): JsonResponse
    {
        $item = TelegramBotCase::create($request->validated());
        return response()->json(['data' => $item], 201);
    }

    public function update(UpdateCaseRequest $request, int $id): JsonResponse
    {
        $item = TelegramBotCase::findOrFail($id);
        $item->update($request->validated());
        return response()->json(['data' => $item]);
    }

    public function destroy(int $id): JsonResponse
    {
        $item = TelegramBotCase::findOrFail($id);
        $item->delete();
        return response()->json(['data' => ['id' => $id]]);
    }

    public function media(int $id): JsonResponse
    {
        $case = TelegramBotCase::findOrFail($id);
        $items = $case->media()->orderBy('sort_order')->orderBy('id')->get();
        return response()->json(['data' => $items]);
    }

    public function storeMedia(StoreCaseMediaRequest $request, int $id): JsonResponse
    {
        $case = TelegramBotCase::findOrFail($id);
        $item = $case->media()->create(array_merge($request->validated(), ['sort_order' => $request->input('sort_order', 0)]));
        return response()->json(['data' => $item], 201);
    }
}
