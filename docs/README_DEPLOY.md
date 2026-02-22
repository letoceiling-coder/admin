# Деплой ADMIN-CRM на VPS (без Docker)

Документ описывает установку и обновление Laravel-приложения и микросервиса Telegram-бота на VPS.

---

## ⚠️ Важно: Nginx и сертификаты

- **Не затирать и не удалять** существующие конфиги других сайтов и доменов на сервере.
- Конфиг для admin.neeklo.ru (например, `tools/deploy/admin.neeklo.ru.nginx.conf`) — **только образец**. Его нужно **добавить** к уже существующим виртуальным хостам (отдельный файл в `sites-available` + симлинк в `sites-enabled`), а не подменять им все сайты.
- **Не запускать** `certbot --nginx` по всем доменам — это может переписать конфиги. Для нового домена использовать `certbot certonly --webroot -w ... -d домен`.
- Если после настройки сайтов/сертификатов другие домены или поддомены перестали работать: **docs/RESTORE_SITES_AND_CERTIFICATES.md** — пошаговое восстановление без удаления существующей конфигурации.

---

## 1. Требования

- **PHP** >= 8.2, расширения: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `redis` (для кэша/очередей и бота)
- **Composer** 2.x
- **Node.js** >= 18, npm (для сборки фронта)
- **MySQL** >= 8.0
- **Redis** (для Laravel cache/session и для бота: FSM, rate-limit)
- **Nginx** (или Apache) с PHP-FPM

---

## 2. Установка и обновление Laravel

### Первичная установка

```bash
cd /opt  # или ваш каталог
git clone <repo_url> admin-crm
cd admin-crm
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

### Обновление (после git pull)

```bash
cd /opt/admin-crm
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3. Настройка .env (Laravel)

Основные переменные:

```env
APP_NAME="ADMIN CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admin.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=admin_crm
DB_USERNAME=...
DB_PASSWORD=...

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Токен для бота (API /api/telegram/*). Должен совпадать с CRM_BOT_API_TOKEN в боте.
CRM_BOT_API_TOKEN=ваш_секретный_токен
```

Сгенерируйте надёжный `CRM_BOT_API_TOKEN` (например, `openssl rand -hex 32`) и укажите тот же токен в `.env` бота.

---

## 4. Очереди и cron

По умолчанию в проекте нет запланированных задач в `app/Console/Kernel.php`. Если позже появятся очереди:

```bash
# Запуск воркера очередей (если используется)
php artisan queue:work --daemon
```

Cron для планировщика (при необходимости):

```cron
* * * * * cd /opt/admin-crm && php artisan schedule:run >> /dev/null 2>&1
```

---

## 5. Сборка фронтенда

```bash
cd /opt/admin-crm
npm ci
npm run build
```

Для разработки: `npm run dev`.

---

## 6. Безопасность и права

- Права на каталоги для веб-сервера:

```bash
chown -R www-data:www-data /opt/admin-crm/storage /opt/admin-crm/bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

- Файл `.env` не должен быть доступен из веб-корня.

---

## 7. Проверка /crm и API

1. Откройте в браузере: `https://admin.example.com/crm` (или ваш APP_URL + `/crm`).
2. Войдите под учётной записью с доступом к CRM (manager/administrator).
3. Проверка API для бота (с хоста, где крутится бот):

```bash
curl -s -H "Authorization: Bearer ВАШ_CRM_BOT_API_TOKEN" "https://admin.example.com/api/telegram/settings"
```

Ожидается JSON с полем `data` (настройки бота, в т.ч. `notify_chat_id`).

---

## 8. Telegram bot service

Микросервис бота расположен в репозитории: **`services/telegram_bot`**.

### 8.1 Требования

- Python 3.11+
- Redis (тот же или отдельный инстанс)
- Доступ к Laravel API по HTTPS и токен `CRM_BOT_API_TOKEN`

### 8.2 Установка

```bash
cd /opt/admin-crm/services/telegram_bot
python3 -m venv .venv
source .venv/bin/activate   # Linux
# .venv\Scripts\activate    # Windows
pip install -r requirements.txt
```

### 8.3 Переменные окружения бота

Создайте `.env` в каталоге `services/telegram_bot/`:

```env
TELEGRAM_BOT_TOKEN=123456:ABC...
CRM_API_BASE_URL=https://admin.example.com
CRM_BOT_API_TOKEN=тот_же_токен_что_в_laravel_.env
REDIS_URL=redis://127.0.0.1:6379/0
LOG_LEVEL=INFO
MODE=polling
HEALTH_PORT=8088
```

`CRM_BOT_API_TOKEN` должен совпадать с переменной в Laravel `.env`.

### 8.4 Запуск вручную (polling)

```bash
cd /opt/admin-crm/services/telegram_bot
source .venv/bin/activate
python -m app.main
```

### 8.5 Проверка /health

Бот поднимает HTTP-сервер на порту `HEALTH_PORT` (по умолчанию 8088):

```bash
curl -s http://127.0.0.1:8088/health
```

Ожидается: `{"status":"ok"}` (200). При недоступности Redis или CRM в теле могут быть поля `redis_error` / `crm_error`, а код — 503 при падении Redis.

### 8.6 Деплой под systemd (рекомендуется)

Пример unit-файла лежит в репозитории: **`services/telegram_bot/deploy/neeklo-bot.service`**.

Установка:

```bash
sudo cp /opt/admin-crm/services/telegram_bot/deploy/neeklo-bot.service /etc/systemd/system/
# Отредактируйте пути/пользователя при необходимости
sudo systemctl daemon-reload
sudo systemctl enable neeklo-bot
sudo systemctl start neeklo-bot
sudo systemctl status neeklo-bot
```

Логи:

```bash
journalctl -u neeklo-bot -f
```

Команды: `start` / `stop` / `restart` / `status`.

### 8.7 Альтернатива: Supervisor

Пример конфига: **`services/telegram_bot/DEPLOY.md`** (секция Supervisor). После размещения в `/etc/supervisor/conf.d/`:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start neeklo-bot
sudo supervisorctl status neeklo-bot
```

### 8.8 Webhook на сервере (чтобы бот отвечал на /start)

Если бот не отвечает в Telegram, он либо не запущен, либо Telegram не знает, куда слать обновления. На сервере с Nginx (например admin.neeklo.ru) используйте **webhook**:

1. **Redis** — должен быть доступен (хост/порт в `REDIS_URL`).
2. **.env бота** в `services/telegram_bot/`:
   - `TELEGRAM_BOT_TOKEN` — токен от @BotFather.
   - `CRM_API_BASE_URL=https://admin.neeklo.ru`
   - `CRM_BOT_API_TOKEN` — тот же, что в Laravel `.env`.
   - `REDIS_URL=redis://127.0.0.1:6379/0` (или ваш Redis).
   - `MODE=webhook`
   - `WEBHOOK_BASE_URL=https://admin.neeklo.ru`
   - `WEBHOOK_PATH=/webhook`
   - `HEALTH_PORT=8088`
3. **Nginx** — в конфиг HTTPS-хоста admin.neeklo.ru добавить проксирование `/webhook` на `http://127.0.0.1:8088` (пример: **`services/telegram_bot/deploy/nginx-webhook.conf`**). Выполнить `nginx -t && systemctl reload nginx`.
4. **Запуск бота** — из каталога `services/telegram_bot`: `python -m app.main` (или через systemd/supervisor). При старте бот сам вызовет `setWebhook`; дополнительно регистрировать webhook вручную не нужно.
5. **Проверка** — в Telegram отправить боту `/start`. Должен прийти ответ.

Подробнее: **`services/telegram_bot/DEPLOY.md`**, раздел 6.

---

## 9. Nginx: проброс /health бота (опционально)

Если нужно отдавать `/health` бота через Nginx (например, для внешнего мониторинга), используйте пример: **`services/telegram_bot/deploy/nginx-health.conf`**. По умолчанию health доступен только на localhost:8088.

---

## 10. Восстановление сайтов и сертификатов

Если при настройке Nginx или выпуске сертификатов пострадали другие сайты, домены или поддомены: **не удалять и не затирать** существующие конфиги. Пошаговая инструкция по восстановлению без потери данных: **docs/RESTORE_SITES_AND_CERTIFICATES.md**.
