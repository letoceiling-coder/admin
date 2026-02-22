# Neeklo Studio Telegram Bot

Микросервис бота (aiogram 3). Интеграция только через Laravel API `/api/telegram/*` (Bearer `CRM_BOT_API_TOKEN`). FSM и rate-limit в Redis.

## Локальный запуск

1. Создать venv и установить зависимости:

```bash
cd services/telegram_bot
python3 -m venv .venv
# Windows: .venv\Scripts\activate
# Linux/macOS: source .venv/bin/activate
pip install -r requirements.txt
```

2. Настроить `.env` (или экспорт переменных):

```
TELEGRAM_BOT_TOKEN=...
CRM_API_BASE_URL=https://admin.neeklo.ru
CRM_BOT_API_TOKEN=...
REDIS_URL=redis://127.0.0.1:6379/0
LOG_LEVEL=INFO
MODE=polling
HEALTH_PORT=8088
```

3. Запуск (polling):

```bash
python -m app.main
```

Health: `GET http://127.0.0.1:8088/health` → `{"status":"ok"}`.

## Структура

- `app/main.py` — entrypoint, логи, health-сервер, polling
- `app/config.py` — переменные окружения
- `app/logging.py` — JSON в stdout
- `app/http.py` — клиент CRM API
- `app/storage.py` — Redis (hero, FSM, rate-limit, антиспам отзывов)
- `app/health.py` — HTTP /health
- `app/bot.py` — роутеры
- `app/ui/` — экраны (home, services, cases, reviews, support)
- `app/flows/` — FSM-формы (lead, call_request, ticket, review)

## Эндпоинты CRM API

Бот использует только:

- GET: settings, services/categories, services, cases, cases/{id}, reviews, faq
- POST: leads, call-requests, tickets, reviews, events

Авторизация: `Authorization: Bearer <CRM_BOT_API_TOKEN>`.
