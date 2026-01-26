<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => 'Ошибка валидации', 'errors' => $v->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'), (bool) $request->input('remember', false))) {
            return response()->json(['message' => 'Неверный email или пароль.'], 401);
        }

        $request->session()->regenerate();
        $user = Auth::user()->load('role');

        return response()->json([
            'user' => $user,
            'message' => 'Успешный вход.',
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => 'Ошибка валидации', 'errors' => $v->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => Role::where('name', Role::USER)->value('id') ?: 1,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $user->load('role');

        return response()->json([
            'user' => $user,
            'message' => 'Регистрация успешна.',
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Выход выполнен.']);
    }
}
