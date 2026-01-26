<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    /**
     * Отправка ссылки для восстановления пароля.
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => 'Ошибка валидации', 'errors' => $v->errors()], 422);
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Ссылка для восстановления пароля отправлена на указанный email.',
            ]);
        }

        return response()->json([
            'message' => 'Не удалось отправить ссылку. Проверьте email.',
            'errors' => ['email' => [__($status)]],
        ], 422);
    }

    /**
     * Сброс пароля по токену из письма.
     */
    public function reset(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => 'Ошибка валидации', 'errors' => $v->errors()], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Пароль успешно изменён.']);
        }

        return response()->json([
            'message' => 'Не удалось сбросить пароль.',
            'errors' => ['email' => [__($status)]],
        ], 422);
    }
}
