<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TelegramBotTokenAuth
{
    /**
     * Проверка Bearer-токена для API Telegram-бота (CRM_BOT_API_TOKEN).
     * Токен не логируется.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth = $request->header('Authorization');
        if (!$auth || !str_starts_with($auth, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = trim(substr($auth, 7));
        $expected = config('services.telegram.bot_api_token');
        if (!$expected || $token !== $expected) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
