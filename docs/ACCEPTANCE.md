# Чек-лист приёмки: CRM + Telegram Bot (STEP 01–08)

Пошаговая проверка после деплоя. Выполнять по порядку, фиксировать результат (✓ / ✗).

---

## A) Laravel / CRM

| # | Действие | Команда / проверка | Ожидаемый результат |
|---|----------|-------------------|---------------------|
| A1 | Миграции применены | `php artisan migrate:status` | Все миграции в статусе "Ran" |
| A2 | Сидер бота (при необходимости) | `php artisan db:seed --class=TelegramBotSeeder` | Без ошибок (если класс есть и нужен на стенде) |
| A3 | Маршруты API для бота | `php artisan route:list --path=telegram` | Есть GET/POST `/api/telegram/*` (settings, services/categories, services, cases, cases/{id}, reviews, faq, leads, call-requests, tickets, reviews, events) |
| A4 | Маршруты CRM Telegram | `php artisan route:list --path=crm/telegram` | Есть `/api/crm/telegram/*` (settings, service-categories, services, cases, reviews, faq, leads, tickets, analytics/top и т.д.) |

---

## B) API контракт для бота

Использовать переменные: `BASE_URL` (например `https://admin.neeklo.ru`), `CRM_BOT_API_TOKEN` из `.env`.

| # | Действие | Команда | Ожидаемый результат |
|---|----------|---------|---------------------|
| B1 | Без токена → 401 | `curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/api/telegram/settings"` | `401` |
| B2 | С токеном → 200 | `curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/api/telegram/settings" -H "Authorization: Bearer $CRM_BOT_API_TOKEN"` | `200` |
| B3 | GET settings | `curl -s "$BASE_URL/api/telegram/settings" -H "Authorization: Bearer $CRM_BOT_API_TOKEN"` | JSON с `data` (notify_chat_id, feature_flags, …) |
| B4 | GET categories | `curl -s "$BASE_URL/api/telegram/services/categories" -H "Authorization: Bearer $CRM_BOT_API_TOKEN"` | JSON `data` (массив категорий) |
| B5 | GET services | `curl -s "$BASE_URL/api/telegram/services" -H "Authorization: Bearer $CRM_BOT_API_TOKEN"` | JSON `data` (массив услуг) |
| B6 | GET cases | `curl -s "$BASE_URL/api/telegram/cases" -H "Authorization: Bearer $CRM_BOT_API_TOKEN"` | JSON `data` (+ meta при пагинации) |
| B7 | GET case by id | `curl -s "$BASE_URL/api/telegram/cases/1" -H "Authorization: Bearer $CRM_BOT_API_TOKEN"` | JSON `data` (кейс или 404) |
| B8 | GET reviews | `curl -s "$BASE_URL/api/telegram/reviews" -H "Authorization: Bearer $CRM_BOT_API_TOKEN"` | JSON `data` |
| B9 | GET faq | `curl -s "$BASE_URL/api/telegram/faq" -H "Authorization: Bearer $CRM_BOT_API_TOKEN"` | JSON `data` |
| B10 | POST leads | см. docs/CRM_TELEGRAM_API_SMOKE.md | `201`, тело `{"data":{"id":...}}` |
| B11 | POST call-requests | см. docs/CRM_TELEGRAM_API_SMOKE.md | `201` |
| B12 | POST tickets | см. docs/CRM_TELEGRAM_API_SMOKE.md | `201` |
| B13 | POST reviews | см. docs/CRM_TELEGRAM_API_SMOKE.md | `201` |
| B14 | POST events | `curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL/api/telegram/events" -H "Authorization: Bearer $CRM_BOT_API_TOKEN" -H "Content-Type: application/json" -d '{"tg_user_id":1,"event_name":"screen_view","payload_json":{"screen":"home"}}'` | `201` |

---

## C) CRM UI

Проверка в браузере (авторизованный пользователь с доступом к CRM).

| # | Действие | Ожидаемый результат |
|---|----------|---------------------|
| C1 | Открыть `/crm` | Страница открывается, дашборд отображается |
| C2 | Меню | Пункты меню присутствуют (Neeklo Bot: настройки, категории, услуги, кейсы, отзывы, FAQ, лиды, тикеты и т.д.) |
| C3 | Settings | Сохранение и перезагрузка настроек (notify_chat_id, тексты, баннер, сайт, презентация) работают |
| C4 | Categories / Services | CRUD категорий и услуг работает |
| C5 | Cases | CRUD кейсов; добавление/удаление медиа по file_id |
| C6 | Reviews | Список, approve/reject отзывов |
| C7 | FAQ | CRUD FAQ |
| C8 | Leads | Список лидов, смена статуса (new / in_progress / done) |
| C9 | Tickets | Список тикетов, смена статуса |
| C10 | Export | Скачивание `leads/export.csv` (кнопка/ссылка) работает |

---

## D) Bot + Redis + Health

На машине, где запущен бот (локально или VPS).

| # | Действие | Команда / проверка | Ожидаемый результат |
|---|----------|---------------------|---------------------|
| D1 | Redis доступен | `redis-cli ping` (или аналог) | `PONG` |
| D2 | Бот запускается | `cd services/telegram_bot && source .venv/bin/activate && python -m app.main` (или systemd) | Без падения при старте (polling или webhook) |
| D3 | Health 200 | `curl -s http://127.0.0.1:8088/health` | `200`, тело `{"status":"ok"}` |
| D4 | Health при недоступном Redis | Остановить Redis, запросить `/health` | `503` или статус с указанием redis_error |

---

## E) Интеграция end-to-end (главный acceptance)

| # | Действие | Ожидаемый результат |
|---|----------|---------------------|
| E1 | В CRM → Neeklo Bot → Настройки задать **notify_chat_id** (ID чата/группы) | Сохранено |
| E2 | В боте создать **Lead** (Услуги → заявка или Кейсы → Хочу так же) | Запись появилась в CRM → Лиды, статус по умолчанию **new** |
| E3 | В боте создать **Ticket** (Поддержка → Создать тикет) | Запись в CRM → Тикеты, статус **new** |
| E4 | В боте создать **Call request** (Поддержка → Заказать звонок) | Запись в CRM (call-requests), уведомление приходит в notify_chat_id (если реализован список в CRM) |
| E5 | Уведомления в notify_chat_id | В чат приходят сообщения с: **тип** (Lead/Ticket/Call request), **имя + @username**, **tg://user?id=...**, **содержимое формы**, для Lead — **источник** (service_id/case_id при выборе) |
| E6 | События и аналитика | Бот отправляет POST /api/telegram/events (screen_view, lead_created, ticket_created) |
| E7 | API аналитики | `curl -s -H "Authorization: Bearer <SANCTUM_TOKEN>" "$BASE_URL/api/crm/telegram/analytics/top?days=30"` | JSON `data.top_services`, `data.top_cases` |
| E8 | Дашборд CRM | В `/crm` отображаются блоки **Top services (last 30 days)** и **Top cases (last 30 days)** |

---

## Итог

- Все пункты A–E отмечены ✓ → **Acceptance passed**.
- При первом деплое на сервер дополнительно: выполнить `php artisan deploy` (или шаги из docs/README_DEPLOY.md), затем пройти пункты A–E на окружении сервера.
