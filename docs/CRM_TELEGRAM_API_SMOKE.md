# Telegram Bot API — smoke-проверки

## Список маршрутов

```bash
php artisan route:list --path=telegram
```

Ожидаются маршруты:

- GET  api/telegram/settings
- GET  api/telegram/services/categories
- GET  api/telegram/services
- GET  api/telegram/cases
- GET  api/telegram/cases/{id}
- GET  api/telegram/reviews
- GET  api/telegram/faq
- POST api/telegram/leads
- POST api/telegram/call-requests
- POST api/telegram/tickets
- POST api/telegram/reviews
- POST api/telegram/events

## Переменная окружения

В `.env` задать:

```
CRM_BOT_API_TOKEN=your_secret_token
```

В запросах бота передавать заголовок: `Authorization: Bearer your_secret_token`.

## Примеры curl

Базовый URL — подставить свой (например `https://admin.neeklo.ru` или `http://admin.loc`).

```bash
# Без токена — ожидается 401
curl -s -o /dev/null -w "%{http_code}" -X GET "http://admin.loc/api/telegram/settings"
# Ожидание: 401

# С токеном — ожидается 200
curl -s -o /dev/null -w "%{http_code}" -X GET "http://admin.loc/api/telegram/settings" \
  -H "Authorization: Bearer YOUR_CRM_BOT_API_TOKEN"
# Ожидание: 200
```

### GET

```bash
TOKEN="YOUR_CRM_BOT_API_TOKEN"
BASE="http://admin.loc/api/telegram"

curl -s -X GET "$BASE/settings" -H "Authorization: Bearer $TOKEN"
curl -s -X GET "$BASE/services/categories" -H "Authorization: Bearer $TOKEN"
curl -s -X GET "$BASE/services" -H "Authorization: Bearer $TOKEN"
curl -s -X GET "$BASE/services?category_id=1" -H "Authorization: Bearer $TOKEN"
curl -s -X GET "$BASE/cases" -H "Authorization: Bearer $TOKEN"
curl -s -X GET "$BASE/cases?tag=laravel&page=1" -H "Authorization: Bearer $TOKEN"
curl -s -X GET "$BASE/cases/1" -H "Authorization: Bearer $TOKEN"
curl -s -X GET "$BASE/reviews" -H "Authorization: Bearer $TOKEN"
curl -s -X GET "$BASE/faq" -H "Authorization: Bearer $TOKEN"
```

### POST

```bash
# Lead
curl -s -X POST "$BASE/leads" -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"tg_user_id":123,"username":"user","full_name":"Name","phone":"+7","message":"Hi","source_service_id":1}'

# Call request
curl -s -X POST "$BASE/call-requests" -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"tg_user_id":123,"phone":"+7999","full_name":"Ivan"}'

# Ticket
curl -s -X POST "$BASE/tickets" -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"tg_user_id":123,"subject":"Help","message":"Need help"}'

# Review
curl -s -X POST "$BASE/reviews" -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"tg_user_id":123,"rating":5,"text":"Great!"}'

# Event
curl -s -X POST "$BASE/events" -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"tg_user_id":123,"event_name":"screen_view","payload_json":{"screen":"home"}}'
```

## Формат ответов

- Успех GET: `{"data": ...}` (для cases/reviews может быть дополнительно `meta` с пагинацией).
- Успех POST (создание): `201`, тело `{"data": {"id": 123}}`.
- Ошибка авторизации: `401`, `{"message": "Unauthorized"}`.
- Ошибка валидации: `422`, стандартный формат Laravel.
