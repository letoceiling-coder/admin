<?php

namespace App\Http\Controllers\Api\Crm\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotCaseMedia;
use App\Http\Requests\Crm\Telegram\UpdateCaseMediaRequest;
use Illuminate\Http\JsonResponse;

class CaseMediaController extends Controller
{
    public function update(UpdateCaseMediaRequest $request, int $id): JsonResponse
    {
        $item = TelegramBotCaseMedia::findOrFail($id);
        $item->update($request->validated());
        return response()->json(['data' => $item]);
    }

    public function destroy(int $id): JsonResponse
    {
        $item = TelegramBotCaseMedia::findOrFail($id);
        $item->delete();
        return response()->json(['data' => ['id' => $id]]);
    }
}
