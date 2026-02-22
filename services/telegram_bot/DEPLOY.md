# Деплой бота (без Docker)

## Требования

- Python 3.11+
- Redis
- Доступ к Laravel API (CRM_API_BASE_URL) и токен CRM_BOT_API_TOKEN

## 1. Установка на VPS

```bash
cd /opt  # или ваш каталог
git clone <repo> admin-crm
cd admin-crm/services/telegram_bot
python3 -m venv .venv
source .venv/bin/activate  # Linux
pip install -r requirements.txt
```

## 2. Переменные окружения

Создайте файл `.env` в `services/telegram_bot/`:

```
TELEGRAM_BOT_TOKEN=123:ABC...
CRM_API_BASE_URL=https://admin.neeklo.ru
CRM_BOT_API_TOKEN=секретный_токен_из_laravel_env
REDIS_URL=redis://127.0.0.1:6379/0
LOG_LEVEL=INFO
MODE=webhook
HEALTH_PORT=8088
WEBHOOK_BASE_URL=https://admin.neeklo.ru
WEBHOOK_PATH=/webhook
```
Для polling вместо webhook задайте `MODE=polling` и не используйте WEBHOOK_*.

В Laravel в `.env` должен быть тот же `CRM_BOT_API_TOKEN`.

## 3. Запуск (systemd)

Файл `/etc/systemd/system/neeklo-bot.service`:

```ini
[Unit]
Description=Neeklo Studio Telegram Bot
After=network.target redis-server.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/opt/admin-crm/services/telegram_bot
EnvironmentFile=/opt/admin-crm/services/telegram_bot/.env
ExecStart=/opt/admin-crm/services/telegram_bot/.venv/bin/python -m app.main
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

Команды:

```bash
sudo systemctl daemon-reload
sudo systemctl enable neeklo-bot
sudo systemctl start neeklo-bot
sudo systemctl status neeklo-bot
sudo journalctl -u neeklo-bot -f
```

## 4. Альтернатива: Supervisor

Файл `/etc/supervisor/conf.d/neeklo-bot.conf`:

```ini
[program:neeklo-bot]
command=/opt/admin-crm/services/telegram_bot/.venv/bin/python -m app.main
directory=/opt/admin-crm/services/telegram_bot
user=www-data
autostart=true
autorestart=true
environment=TELEGRAM_BOT_TOKEN="...",CRM_API_BASE_URL="...",CRM_BOT_API_TOKEN="...",REDIS_URL="redis://127.0.0.1:6379/0",LOG_LEVEL="INFO",MODE="polling",HEALTH_PORT="8088"
stdout_logfile=/var/log/neeklo-bot.log
stderr_logfile=/var/log/neeklo-bot.err.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start neeklo-bot
sudo supervisorctl status neeklo-bot
```

## 5. Health

Проверка: `curl http://127.0.0.1:8088/health` → `{"status":"ok"}`.

Порт 8088 пробросьте в мониторинг или балансировщик при необходимости.

## 6. Webhook (режим для работы за Nginx на одном сервере)

Если бот крутится на том же сервере, что и сайт (admin.neeklo.ru), удобно использовать **webhook**: Telegram шлёт обновления на HTTPS-URL, Nginx проксирует на локальный порт бота. Тогда бот не держит длинное соединение (long polling), а только принимает POST-запросы.

### 6.1 Переменные для webhook

В `.env` бота добавьте/измените:

```
MODE=webhook
WEBHOOK_BASE_URL=https://admin.neeklo.ru
WEBHOOK_PATH=/webhook
HEALTH_PORT=8088
```

Опционально (рекомендуется для безопасности):

```
WEBHOOK_SECRET=случайная_строка_20_символов
```

Тогда Telegram будет присылать заголовок `X-Telegram-Bot-Api-Secret-Token` с этим значением — бот проверит его.

### 6.2 Nginx

**Важно:** только **добавить** location в существующий конфиг хоста admin.neeklo.ru; не удалять и не затирать конфиги других сайтов/доменов.

В конфиг виртуального хоста с HTTPS (admin.neeklo.ru) добавьте:

```nginx
include /path/to/admin-crm/services/telegram_bot/deploy/nginx-webhook.conf;
```

Либо вручную добавьте location:

```nginx
location /webhook {
    proxy_pass http://127.0.0.1:8088;
    proxy_http_version 1.1;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_pass_request_body on;
    proxy_pass_request_headers on;
}
```

Перезагрузите Nginx: `sudo nginx -t && sudo systemctl reload nginx`.

### 6.3 Запуск бота и регистрация webhook

1. Запустите бота (systemd или supervisor). При `MODE=webhook` он при старте сам вызовет `setWebhook` и будет слушать порт 8088 (путь `/webhook` и `/health`).

2. Либо зарегистрировать webhook вручную один раз (если бот уже слушает):

```bash
# Подставьте свой TELEGRAM_BOT_TOKEN и домен
curl -s "https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/setWebhook?url=https://admin.neeklo.ru/webhook"
```

Если задан `WEBHOOK_SECRET`:

```bash
curl -s -X POST "https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/setWebhook" \
  -H "Content-Type: application/json" \
  -d '{"url":"https://admin.neeklo.ru/webhook","secret_token":"ваш_WEBHOOK_SECRET"}'
```

Ответ должен быть `{"ok":true,"result":true,...}`.

3. Проверка: отправьте боту в Telegram команду `/start`. Должен прийти ответ от бота.

4. Проверка health: `curl https://admin.neeklo.ru/telegram-bot/health` (если включён пример из nginx-webhook.conf) или `curl http://127.0.0.1:8088/health`.

### 6.4 Сброс webhook (вернуться на polling)

Чтобы снова использовать polling:

```bash
curl -s "https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/deleteWebhook"
```

В `.env` бота поставьте `MODE=polling`, перезапустите бота.
