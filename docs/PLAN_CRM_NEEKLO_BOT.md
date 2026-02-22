# План реализации: CRM + Telegram-бот Neeklo Studio

## Роль

Ты — Cursor (AI в редакторе кода) + senior full-stack (Laravel + Telegram bots).  
Задача: готовый продукт — Telegram-бот «Neeklo Studio» + управление контентом из админки CRM.

---

## Ограничения

- **Нельзя:** отдельная админка на FastAPI, переписывать CRM с нуля, ломать текущую архитектуру и модули.
- **Нужно:** бот как отдельный сервис (микросервис), интеграция с CRM, деплой на VPS (Docker Compose), возможность масштабирования.
- **Где делать:** вся новая функциональность — в рамках раздела **/crm** (роут `https://admin.neeklo.ru/crm`). Существующие файлы проекта не переписывать; добавлять только новое.

---

## Стек

- **admin-crm:** Laravel (php artisan migrate/seed и т.д.).
- **Бот:** отдельный сервис (Python, aiogram) + интеграция с Laravel через **REST API** (приоритет); общая БД — только если API быстро сделать нельзя.

---

## Инфраструктура / VPS / масштабирование

- VPS, Docker Compose — единая точка запуска.
- Бот **stateless** (без локального state в памяти).
- FSM и rate-limit — в **Redis**.
- Масштабирование: `docker compose up --scale telegram_bot=3`.
- Логи: stdout JSON (под `docker logs`).
- Healthcheck бота: эндпоинт `/health` для наблюдаемости.

---

## Что уже сделано (структура /crm)

1. **Роут:** `https://admin.neeklo.ru/crm` — отдельная зона CRM.
2. **Доступ:** те же пользователи, что и у /admin (manager, administrator); авторизация через существующий логин.
3. **Фронт:**
   - `resources/js/layouts/CrmLayout.vue` — лейаут CRM.
   - `resources/js/components/crm/CrmSidebar.vue` — сайдбар **без пунктов меню** (пункты добавить по плану).
   - `resources/js/components/crm/CrmHeader.vue` — шапка.
   - `resources/js/pages/crm/DashboardPage.vue` — главная страница CRM.
4. **Роутер:** добавлен блок `/crm` с `meta: { requiresAuth: true, requiresAdminAccess: true }`; после логина редирект на `/crm` по `?redirect=/crm`.
5. В сайдбаре админки (/admin) добавлена ссылка «CRM» для перехода в /crm.

---

## План реализации по шагам

### 1. Бэкенд Laravel: миграции и модели для контента бота

- Таблицы (все новые, без изменения существующих):
  - **telegram_bot_settings** — тексты, баннер, сайт, презентация, контакты, `notify_chat_id`, feature flags (в т.ч. `banner_file_id`, `presentation_file_id` и т.д.).
  - **telegram_bot_service_categories** — категории услуг.
  - **telegram_bot_services** — услуги (связь с категорией, поля: что сделаем, результат, сроки/цена, `order`).
  - **telegram_bot_cases** — кейсы (задача, решение, результат, теги, `order`).
  - **telegram_bot_case_media** — медиа кейсов (file_id, type, `order`).
  - **telegram_bot_reviews** — отзывы (имя/компания, рейтинг, текст, status: pending/approved).
  - **telegram_bot_faq** — FAQ (вопрос, ответ, `order`).
- Лиды/тикеты/звонки:
  - По возможности использовать существующие сущности CRM; если нет — новые таблицы:
    - **telegram_leads** (или встраивание в существующие лиды).
    - **telegram_tickets**.
    - **telegram_call_requests**.
  - **telegram_events** — аналитика (screen_view, cta_click, lead_created и т.д., payload_json).

Создать модели Eloquent и сидеры с демо-контентом: 3 категории, 8 услуг, 6 кейсов, 10 отзывов, 8 FAQ.

---

### 2. Laravel API для бота (модуль telegram)

- **Авторизация:** бот ходит с заголовком `Authorization: Bearer <TELEGRAM_BOT_API_TOKEN>` (статический токен из `.env`). Middleware проверяет токен; rate-limit на эти эндпоинты.
- **Чтение (GET):**
  - `GET /api/telegram/settings`
  - `GET /api/telegram/services/categories`
  - `GET /api/telegram/services?category_id=...`
  - `GET /api/telegram/cases?tag=...&page=...`
  - `GET /api/telegram/cases/{id}`
  - `GET /api/telegram/reviews?page=...`
  - `GET /api/telegram/faq`
- **Запись (POST, бот пишет):**
  - `POST /api/telegram/leads`
  - `POST /api/telegram/call-requests`
  - `POST /api/telegram/tickets`
  - `POST /api/telegram/reviews`
  - `POST /api/telegram/events`
- Валидация всех входящих payload на стороне Laravel.

---

### 3. CRUD в CRM (/crm) — управление контентом бота

Раздел в админке: **/crm** (не отдельная админка). Подразделы (пункты меню в `CrmSidebar.vue`):

- **Настройки бота** (`/crm/neeklo-bot/settings`) — тексты, баннер, сайт, презентация, контакты, notify_chat_id, feature flags.
- **Услуги** — категории + список услуг (`/crm/neeklo-bot/services`, `/crm/neeklo-bot/services/categories`).
- **Кейсы** — список кейсов + медиа-галерея (`/crm/neeklo-bot/cases`).
- **Отзывы** — модерация (pending/approved) (`/crm/neeklo-bot/reviews`).
- **FAQ** (`/crm/neeklo-bot/faq`).
- **Лиды** — просмотр, статусы, экспорт CSV (`/crm/neeklo-bot/leads`).
- **Тикеты** — просмотр, статусы (`/crm/neeklo-bot/tickets`).

UI: в стиле текущей админки (те же компоненты/стили, что в /admin). Не вводить новый UI-фреймворк.

API для этих CRUD: под префиксом, доступным только авторизованным с `admin.access`, например `/api/crm/telegram/...` или внутри существующего префикса с проверкой роли.

---

### 4. Сервис бота: `services/telegram_bot`

- Отдельный каталог в репозитории: **services/telegram_bot** (отдельный контейнер).
- Стек: Python, aiogram.
- Режимы: **polling** (dev), **webhook** (prod, опционально).
- Конфиг из env: `TELEGRAM_BOT_TOKEN`, `CRM_API_BASE_URL`, `CRM_BOT_API_TOKEN`, `REDIS_URL`, `LOG_LEVEL`, `MODE=polling|webhook`.
- UX: Single Screen — одно hero-сообщение, всё меню через `editMessageText` / `editMessageMedia` / `editMessageReplyMarkup`; при отправке документов/альбомов — clean-up (удаление временных сообщений при возврате в меню).
- Формы (FSM в Redis): Lead (5 шагов), Call request, Ticket, Review; после отправки — сохранение через Laravel API и уведомление в `notify_chat_id`.
- Экран HOME: баннер/фото, оффер, 6 кнопок (Услуги, Кейсы, Отзывы, Поддержка, Сайт, Презентация) + опционально CTA «Оставить заявку» / «Написать менеджеру».
- Детали экранов и форм — по ТЗ (услуги с пагинацией, кейсы с тегами и галереей, отзывы с антиспамом 1/24ч, поддержка с FAQ и тикетами и т.д.).

---

### 5. Docker Compose и деплой

- В `docker-compose` добавить/проверить: **laravel app**, **db**, **redis**, **telegram_bot**.
- Сервис `telegram_bot`: образ из `services/telegram_bot`, переменные окружения как выше.
- Документация: обновить/добавить **README_DEPLOY** — инструкции деплоя на VPS, переменные окружения, масштабирование бота, при необходимости пример nginx для webhook и SSL.

---

### 6. Уведомления админам

- В настройках бота в CRM хранить `notify_chat_id`.
- При создании lead/ticket/call-request бот отправляет сообщение в этот чат: кто (username + tg link), что нужно, бюджет/срок, источник (service_id/case_id).
- В CRM лиды/тикеты имеют статусы: new / in_progress / done.

---

### 7. Аналитика

- События от бота: `screen_view`, `cta_click`, `lead_created`, `ticket_created` с payload (service_id, case_id, tag, page) в `telegram_events`.
- В разделе /crm — минимальный дашборд: Top services / Top cases (по событиям).

---

### 8. Критерии готовности (Acceptance)

- `docker compose up` поднимает всё (app, db, redis, telegram_bot).
- Бот работает, не спамит чат (single message UI).
- Контент управляется из CRM (/crm).
- Лиды/тикеты создаются через бота и видны в CRM.
- Уведомления приходят в notify_chat_id.
- Бот масштабируется (stateless + Redis).
- Деплой на VPS описан в README_DEPLOY.

---

### 9. Порядок работы в репозитории

1. Изучить: роутинг админки, модели (пользователи, лиды если есть), миграции/сидеры.
2. Реализовывать минимально инвазивно, в стиле существующих паттернов.
3. Всё новое — в /crm (фронт и роуты CRM) и в новых файлах (миграции, модели, API, `services/telegram_bot`); текущий код админки и ядра не ломать.

---

## Итог

- **Структура /crm** уже создана (layout, sidebar без пунктов, header, дашборд, доступ по тем же ролям что /admin).
- **План реализации** — выполнять по пунктам 1–8 выше; результат — готовый продукт: бот Neeklo Studio + управление контентом и лидами/тикетами из раздела CRM по адресу `https://admin.neeklo.ru/crm`.
