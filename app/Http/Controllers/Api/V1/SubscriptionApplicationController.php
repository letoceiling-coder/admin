<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubscriptionApplicationStoreRequest;
use App\Models\Role;
use App\Models\SubscriptionApplication;
use App\Models\User;
use App\Notifications\NewSubscriptionApplicationNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class SubscriptionApplicationController extends Controller
{
    public function store(SubscriptionApplicationStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $token = SubscriptionApplication::generateToken();
        $expiresAt = Carbon::now()->addDays(3);

        $application = SubscriptionApplication::create([
            'domain' => $data['domain'],
            'name' => $data['name'],
            'email' => $data['email'],
            'api_token' => $token,
            'expires_at' => $expiresAt,
            'status' => SubscriptionApplication::STATUS_PENDING,
        ]);

        $users = User::whereHas('role', function ($q) {
            $q->whereIn('name', [Role::MANAGER, Role::ADMINISTRATOR]);
        })->get();

        foreach ($users as $user) {
            $user->notify(new NewSubscriptionApplicationNotification($application));
        }

        return response()->json([
            'message' => 'Заявка создана',
            'data' => [
                'id' => $application->id,
                'domain' => $application->domain,
                'email' => $application->email,
                'api_token' => $application->api_token,
                'expires_at' => $application->expires_at->toIso8601String(),
            ],
        ], 201);
    }
}
