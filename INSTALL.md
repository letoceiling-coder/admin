# Инструкция по установке проекта на сервере

Этот документ описывает процесс первоначальной установки проекта на сервере.

## Быстрая установка

### Вариант 1: Автоматическая установка (рекомендуется)

```bash
# Сделать скрипт исполняемым
chmod +x install.sh

# Запустить установку
./install.sh
```

### Вариант 2: Установка с клонированием из git

```bash
# Клонировать и установить
./install.sh --git-url=https://github.com/your-username/admin.git --branch=main
```

### Вариант 3: Неинтерактивная установка

```bash
# Установка без запросов (для CI/CD)
./install.sh --no-interaction --skip-build
```

## Опции скрипта install.sh

- `--git-url=URL` - URL git репозитория для клонирования
- `--branch=BRANCH` - Ветка для клонирования (по умолчанию: main)
- `--skip-git` - Пропустить клонирование (если проект уже клонирован)
- `--skip-build` - Пропустить сборку фронтенда
- `--with-seed` - Выполнить seeders базы данных
- `--no-interaction` - Неинтерактивный режим (без запросов)

## Ручная установка

Если автоматический скрипт не подходит, выполните шаги вручную:

### 1. Клонирование проекта

```bash
git clone https://github.com/your-username/admin.git
cd admin
```

### 2. Установка Composer

```bash
# Создать директорию bin
mkdir -p bin

# Скачать и установить composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=bin --filename=composer
rm composer-setup.php
chmod +x bin/composer
```

### 3. Установка PHP зависимостей

```bash
php bin/composer install --no-dev --optimize-autoloader
```

### 4. Настройка окружения

```bash
# Копировать .env.example в .env
cp .env.example .env

# Сгенерировать APP_KEY
php artisan key:generate
```

### 5. Настройка базы данных

Отредактируйте `.env` файл:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_admin
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Выполнение миграций

```bash
php artisan migrate --force
```

### 7. Выполнение seeders (опционально)

```bash
php artisan db:seed --force
```

### 8. Установка npm зависимостей и сборка фронтенда

```bash
npm install
npm run build
```

### 9. Настройка прав доступа

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 10. Очистка и оптимизация

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 11. Создание администратора

```bash
php artisan user:create
```

## Требования

- PHP >= 8.1
- Composer
- Node.js >= 18 (для сборки фронтенда)
- MySQL >= 8.0
- Git

## Проверка установки

После установки проверьте:

1. **Проверка API:**
   ```bash
   curl http://your-domain.com/api/v1/ping
   # Ожидается: {"ok":true,"db":"laravel_admin"}
   ```

2. **Проверка конфигурации:**
   ```bash
   php artisan config:show
   ```

3. **Проверка маршрутов:**
   ```bash
   php artisan route:list
   ```

## Настройка веб-сервера

### Apache

Создайте виртуальный хост:

```apache
<VirtualHost *:80>
    ServerName admin.yourdomain.com
    DocumentRoot /path/to/admin/public

    <Directory /path/to/admin/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx

```nginx
server {
    listen 80;
    server_name admin.yourdomain.com;
    root /path/to/admin/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## После установки

После успешной установки проект готов к работе. Для дальнейших обновлений используйте:

```bash
php artisan deploy
```

Эта команда автоматически:
- Соберет фронтенд
- Закоммитит изменения
- Отправит на сервер
- Выполнит деплой на сервере

## Решение проблем

### Ошибка прав доступа

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Ошибка подключения к БД

Проверьте настройки в `.env` и убедитесь, что база данных создана:

```bash
mysql -u root -p
CREATE DATABASE laravel_admin;
```

### Composer не найден

Убедитесь, что composer установлен в `bin/composer` или глобально:

```bash
which composer
```

### Node.js не найден

Установите Node.js или используйте `--skip-build` для пропуска сборки фронтенда.

## Поддержка

При возникновении проблем:
1. Проверьте логи: `storage/logs/laravel.log`
2. Проверьте конфигурацию: `php artisan config:show`
3. Проверьте права доступа к `storage` и `bootstrap/cache`
