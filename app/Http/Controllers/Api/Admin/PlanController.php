<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::where('is_active', true)->orderBy('id')->get(['id', 'name', 'cost']);

        return response()->json(['data' => $plans]);
    }
}
