<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class UserCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     * Laravel Artisan: https://laravel.com/docs/10.x/artisan#defining-input-expectations
     */
    protected $signature = 'user:create
                            {--email= : Email пользователя}
                            {--password= : Пароль}
                            {--name= : Имя пользователя}
                            {--role= : Роль (user|manager|administrator|developer)}';

    /**
     * The console command description.
     */
    protected $description = 'Создать или обновить пользователя. Без параметров — создать/обновить администратора по умолчанию.';

    /**
     * Дефолтный администратор при вызове без параметров.
     */
    private const DEFAULT_EMAIL = 'dsc-23@yandex.ru';
    private const DEFAULT_PASSWORD = '123123123';
    private const DEFAULT_NAME = 'Джон Уик';
    private const DEFAULT_ROLE = 'administrator';

    /**
     * Execute the console command.
     * https://laravel.com/docs/10.x/artisan#writing-commands
     */
    public function handle(): int
    {
        $useDefaults = $this->isDefaultMode();

        if ($useDefaults) {
            $email = self::DEFAULT_EMAIL;
            $password = self::DEFAULT_PASSWORD;
            $name = self::DEFAULT_NAME;
            $roleName = self::DEFAULT_ROLE;
        } else {
            $email = $this->option('email');
            $password = $this->option('password');
            $name = $this->option('name');
            $roleName = $this->option('role') ?? Role::USER;

            if (empty($email)) {
                $this->error('Укажите --email при использовании параметров.');
                return self::FAILURE;
            }
            if (empty($password)) {
                $this->error('Укажите --password при использовании параметров.');
                return self::FAILURE;
            }
            if (empty($name)) {
                $this->error('Укажите --name при использовании параметров.');
                return self::FAILURE;
            }
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error("Роль «{$roleName}» не найдена. Доступны: user, manager, administrator, developer.");
            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role_id' => $role->id,
            ]
        );

        $action = $user->wasRecentlyCreated ? 'Создан' : 'Обновлён';
        $this->info("{$action} пользователь: {$user->email} ({$user->name}), роль: {$role->name}.");

        return self::SUCCESS;
    }

    /**
     * Режим по умолчанию: не передано ни одной опции.
     */
    private function isDefaultMode(): bool
    {
        return $this->option('email') === null
            && $this->option('password') === null
            && $this->option('name') === null
            && $this->option('role') === null;
    }
}
