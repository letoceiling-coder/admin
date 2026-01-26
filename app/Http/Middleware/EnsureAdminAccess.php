<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    /**
     * Доступ к /admin/* только у manager и administrator.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$request->user()->hasAdminPanelAccess()) {
            return response()->json([
                'message' => 'Доступ запрещён. Требуется роль менеджера или администратора.',
            ], 403);
        }

        return $next($request);
    }
}
