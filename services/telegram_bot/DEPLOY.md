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
MODE=polling
HEALTH_PORT=8088
```

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

## 6. Webhook (опционально)

При `MODE=webhook` нужен HTTPS и публичный URL для бота. Настройка webhook в коде и nginx — отдельно; по умолчанию используется polling.
