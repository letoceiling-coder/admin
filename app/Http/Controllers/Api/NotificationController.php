<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Уведомления пользователя (Laravel Database Notifications).
 * GET /api/notifications — список.
 */
class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $rows = $user->notifications()->limit(50)->get()->map(function ($n) {
            $data = $n->data;
            return [
                'id' => $n->id,
                'title' => $data['title'] ?? 'Уведомление',
                'message' => $data['message'] ?? '',
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at?->toIso8601String(),
                'application_id' => $data['application_id'] ?? null,
            ];
        });

        return response()->json(['data' => $rows->values()->all()]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $n = $user->notifications()->where('id', $id)->first();
        if ($n && !$n->read_at) {
            $n->markAsRead();
        }

        return response()->json(['message' => 'ok']);
    }
}
