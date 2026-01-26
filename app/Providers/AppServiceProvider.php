<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ResetPassword::toMailUsing(function ($user, $token) {
            $url = rtrim(config('app.frontend_url'), '/') . '/admin/reset-password?token=' . $token . '&email=' . urlencode($user->email);
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Сброс пароля')
                ->line('Вы запросили сброс пароля.')
                ->action('Сбросить пароль', $url)
                ->line('Ссылка действительна 60 минут.');
        });
    }
}
