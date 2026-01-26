# План интеграции CRM ↔ ADMIN

**Проекты:**
- **CRM:** `C:\OSPanel\domains\ADMIN-CRM\crm` — регистрация на `http://crm.loc/admin/register`
- **ADMIN:** `C:\OSPanel\domains\ADMIN-CRM\admin` — админ-панель, API v1, подписчики

**Цель:** при регистрации в CRM создавать заявку на подписку в ADMIN, получать API-токен, сохранять его в CRM для будущих запросов; в ADMIN показывать уведомление о новой заявке и вести подписчиков (список, детали, планы).

---

## Краткая схема сценария (регистрация → заявка → токен)

```
[Пользователь] → CRM /admin/register (форма)
       ↓
[CRM Frontend] POST /api/auth/register { name, email, password }
       ↓
[CRM Backend]  RegisterRequest ✅ → User::create, Auth::login
       ↓
[CRM Backend]  HTTP POST → ADMIN /api/v1/subscription-applications
               Body: { domain, name, email }  (domain = APP_URL host, например crm.loc)
       ↓
[ADMIN]        Валидация ✅ → SubscriptionApplication::create
               api_token = Str::random(64), expires_at = now + 3 days
               → Notify(manager/administrator) «Новая заявка: {domain} — {email}»
               → 201 { data: { id, api_token, expires_at, ... } }
       ↓
[CRM Backend]  Сохранить api_token в settings (БД)
       ↓
[CRM Backend]  Ответ frontend: { user, message }
       ↓
[CRM Frontend] Redirect /admin/dashboard
```

Далее все запросы CRM → ADMIN используют заголовок `Authorization: Bearer {api_token}` (или `X-Api-Token`).

---

## 1. Анализ проектов

### 1.1 CRM
- **Стек:** Laravel, Vue 3, Pinia, Sanctum.
- **Регистрация:** `POST /api/auth/register` → `AuthController::register` → создаёт `User` (роль admin), логинит, возвращает `{ user, message }`.
- **Маршрут регистрации (SPA):** `/admin/register` → `RegisterPage.vue` → вызов `authStore.register()`.
- **Конфиг:** в `.env` уже есть `APP_CRM_URL=http://admin.loc/api/v1/` (URL ADMIN API).
- **БД:** отдельная (users, roles, shops, shop_user). Токен ADMIN пока нигде не хранится.

### 1.2 ADMIN
- **Стек:** Laravel, Vue 3, Pinia, Sanctum, Tailwind, темы.
- **API:** `/api/*` (auth, notifications). **Маршрутов `/api/v1/*` пока нет.**
- **Уведомления:** `GET /api/notifications` → заглушка `{ data: [] }`. Планируется Laravel Database Notifications.
- **БД:** отдельная (users, roles). Нет таблиц `plans`, `subscribers`, `subscription_applications`, `notifications`.

### 1.3 Выводы
- CRM и ADMIN — **разные приложения, разные БД.**
- Запрос CRM → ADMIN выполняется **на бэкенде CRM** (после успешной регистрации), не из браузера. CORS для этого сценария не требуется.
- Токен, возвращаемый ADMIN, нужно хранить **в БД CRM** (например, таблица `settings` или `integration_config`), чтобы использовать при последующих запросах к ADMIN.

---

## 2. Модели и миграции (ADMIN)

### 2.1 Таблица `plans` (тип подписки / план)
| Поле       | Тип           | Описание                                      |
|------------|---------------|-----------------------------------------------|
| id         | bigint PK     | —                                             |
| name       | string        | Уникальное имя (например `standard`, `premium`) |
| cost       | decimal(10,2) | Стоимость                                     |
| is_active  | boolean       | Активность плана                              |
| limits     | json          | Ограничения/требования (гибкая структура)     |
| timestamps | —             | created_at, updated_at                        |

**Сидер:** два плана — **Стандарт** и **Премиум** (например, cost 0 и 1, limits — пустой объект или примеры лимитов).

### 2.2 Таблица `subscribers` (подписчики)
| Поле                | Тип        | Описание                                    |
|---------------------|------------|---------------------------------------------|
| id                  | bigint PK  | —                                           |
| domain              | string     | Домен (например `crm.loc`)                  |
| login               | string     | Логин                                       |
| subscription_start  | date       | Начало подписки                             |
| subscription_end    | date       | Конец подписки                              |
| is_active           | boolean    | Активность подписки                         |
| plan_id             | FK → plans | План                                        |
| api_token           | string     | Уникальный токен для API (nullable до одобрения) |
| payment_data        | json       | Данные об оплате (суммы, даты, ссылки и т.п.) |
| timestamps          | —          | created_at, updated_at                      |

Рекомендуется уникальность по `domain` (один домен — один подписчик).

### 2.3 Таблица `subscription_applications` (заявки на подписку)
| Поле        | Тип        | Описание                                           |
|-------------|------------|----------------------------------------------------|
| id          | bigint PK  | —                                                  |
| domain      | string     | Домен CRM                                          |
| name        | string     | Имя пользователя (из регистрации)                  |
| email       | string     | Email                                              |
| api_token   | string     | Сгенерированный токен (уникальный)                 |
| expires_at  | timestamp  | Окончание срока действия заявки (3 дня)            |
| status      | enum       | `pending`, `approved`, `rejected`                  |
| timestamps  | —          | created_at, updated_at                             |

При создании заявки: `status = pending`, `expires_at = now() + 3 days`, генерируем `api_token` (например, `Str::random(64)`).

### 2.4 Таблица `notifications` (Laravel Database Notifications)
- Выполнить `php artisan notifications:table` + миграция.
- Модель `User` в ADMIN уже использует `Notifiable` — готово для уведомлений.

### 2.5 Связи
- `Subscriber` → `Plan` (belongsTo).
- При одобрении заявки: создать `Subscriber` (domain, login из email, plan_id, api_token из заявки и т.д.), обновить заявку `status = approved`.

---

## 3. API ADMIN

### 3.1 Префикс `/api/v1/`
- Выделить группу маршрутов с префиксом `api/v1` (например, в `routes/api.php` или отдельный файл `routes/api_v1.php`).

### 3.2 Публичный маршрут (без auth)

**`POST /api/v1/subscription-applications`** — приём заявки от CRM при регистрации.

**Назначение:** CRM после успешной регистрации пользователя отправляет сюда домен и данные пользователя.

**Тело запроса (JSON):**
```json
{
  "domain": "crm.loc",
  "name": "Иван Иванов",
  "email": "user@example.com"
}
```

**Валидация:**
- `domain` — required, string, max:255.
- `name` — required, string, max:255.
- `email` — required, email.

**Логика:**
1. Валидировать вход.
2. Создать запись в `subscription_applications`: domain, name, email, сгенерировать `api_token`, `expires_at = now() + 3 days`, `status = pending`.
3. Создать **уведомление** для пользователей с доступом в админ-панель (manager, administrator): «Новая заявка на подписку: {domain} — {email}».
4. Вернуть ответ:
```json
{
  "message": "Заявка создана",
  "data": {
    "id": 1,
    "domain": "crm.loc",
    "email": "user@example.com",
    "api_token": "...",
    "expires_at": "2026-01-28T12:00:00.000000Z"
  }
}
```
Код ответа: `201`.

**Важно:** маршрут **публичный** (без `auth:sanctum`). Рекомендуется включить **throttle** (например, `throttle:30,1`), чтобы ограничить злоупотребления.

### 3.3 Защищённые маршруты (auth + admin)
- Уже есть группа `auth:sanctum` + `admin.access` для `/api/admin/*`. Роуты подписчиков/заявок для админов можно вешать туда или в отдельную группу с теми же middleware.
- Примеры на будущее:
  - `GET /api/admin/subscribers` — список подписчиков (пагинация, фильтры).
  - `GET /api/admin/subscribers/{id}` — детали подписчика.
  - `GET /api/admin/subscription-applications` — список заявок.
  - `PATCH /api/admin/subscription-applications/{id}` — одобрить/отклонить заявку (и при одобрении создавать `Subscriber`).

### 3.4 Запросы от CRM к ADMIN (будущие)
- Все последующие запросы CRM → ADMIN должны использовать **API-токен** (из заявки, затем из подписчика).
- Варианты: заголовок `Authorization: Bearer {api_token}` или `X-Api-Token: {api_token}`.
- В ADMIN — middleware, которое проверяет токен по `subscription_applications` (pending) или `subscribers` (active), и при необходимости ограничивает доступ только к допустимым эндпоинтам.

---

## 4. Уведомления в ADMIN

### 4.1 Создание уведомления о новой заявке
- При создании заявки в `POST /api/v1/subscription-applications`:
  - Выбрать пользователей с `hasAdminPanelAccess()` (роли manager, administrator).
  - Для каждого вызвать `$user->notify(new NewSubscriptionApplicationNotification($application))`.
- Тип: Database Notification (канал `database`).

### 4.2 Отображение в header
- `GET /api/notifications` уже используется в `NotificationDropdown`.
- Переписать `NotificationController::index`: брать уведомления из `$request->user()->notifications()` (или `unreadNotifications`), маппить в формат `{ id, title, message, read, created_at }`, возвращать `{ data: [...] }`.
- При клике по уведомлению — при необходимости помечать как прочитанное и/или вести на страницу заявок/подписчика.

---

## 5. Сценарий CRM при регистрации

### 5.1 Конфигурация CRM
- В CRM уже есть `APP_CRM_URL=http://admin.loc/api/v1/` в `.env`. Добавить в `config/app.php` ключ `admin_api_url` (или `crm_url`) из `env('APP_CRM_URL')`, без завершающего слеша.
- Домен CRM для отправки в ADMIN: использовать хост из `APP_URL` (например, `parse_url(config('app.url'), PHP_URL_HOST)` → `crm.loc`) или отдельный `APP_DOMAIN` — по соглашению.

### 5.2 Где хранить токен в CRM
- Таблица **`settings`** (key-value): ключи `admin_api_url`, `admin_api_token`, `admin_api_token_expires_at`.
- Либо отдельная таблица `integration_config` с полями `key`, `value`.  
После успешного ответа от ADMIN сохранять `api_token` и при необходимости `expires_at`.

### 5.3 Изменения в CRM

**1) Миграция `create_settings_table`**  
Поля: `id`, `key` (unique), `value` (text/json), `timestamps`.

**2) Модель `Setting`**  
Хелперы `get('admin_api_token')`, `set('admin_api_token', $value)`.

**3) Сервис `AdminApiService` (или аналог)**  
- Метод `sendSubscriptionApplication(domain, name, email)`:
  - HTTP POST на `config('app.admin_api_url') . 'subscription-applications'` (или `APP_CRM_URL` + `subscription-applications`).
  - Тело: `{ domain, name, email }`.
  - Обработка ответа: при 201 сохранить `data.api_token` (и при необходимости `data.expires_at`) в `settings`.
  - При ошибке — логировать, не падать регистрацию (по желанию можно показывать пользователю предупреждение).

**4) `AuthController::register`**  
После создания пользователя и логина:
- Определить `domain` (например, из `parse_url(config('app.url'), PHP_URL_HOST)`).
- Вызвать `AdminApiService::sendSubscriptionApplication($domain, $user->name, $user->email)`.
- Регистрацию считать успешной в любом случае (если интеграция с ADMIN недоступна — только логируем).

**5) Валидация данных перед отправкой**  
- Использовать уже провалидированные `name`, `email` из `RegisterRequest`.  
- Дополнительно проверять `domain` (не пустой, допустимая длина).

### 5.4 Frontend CRM
- Текущий поток без изменений: `RegisterPage` → `authStore.register()` → редирект на дашборд.
- Всё взаимодействие с ADMIN идёт на бэкенде CRM после `POST /api/auth/register`.

---

## 6. UI ADMIN: Подписчики и заявки

### 6.1 Пункт меню «Подписчики»
- В сайдбаре (`AdminSidebar.vue`) добавить пункт «Подписчики» с иконкой, ссылкой на `/admin/subscribers` (или отдельно «Заявки» на `/admin/subscription-applications` при необходимости).

### 6.2 Страница «Список подписчиков»
- Роут: `/admin/subscribers`, компонент `SubscribersPage.vue`.
- Таблица: домен, логин, план, начало/конец подписки, активность, дата создания.
- **Пагинация:** backend `GET /api/admin/subscribers?page=…&per_page=…`.
- **Фильтрация:** по домену, плану, активности, периоду подписки — query-параметры, те же эндпоинты.

### 6.3 Страница «Детали подписчика»
- Роут: `/admin/subscribers/:id`, компонент `SubscriberDetailPage.vue`.
- Отображение всех полей: домен, логин, план, начало/конец подписки, активность, API-токен (маскированный при выводе), данные об оплате (`payment_data`), даты создания/обновления.
- При необходимости — кнопки «Редактировать», «Продлить», «Деактивировать» и т.д. (отдельные задачи).

### 6.4 Заявки (опционально отдельная страница)
- Список заявок с фильтрами, пагинацией.
- Действия: «Одобрить» (создать подписчика, обновить заявку), «Отклонить».

---

## 7. Валидация и безопасность

### 7.1 ADMIN — приём заявки
- Строгая валидация `domain`, `name`, `email`.
- Rate limiting (`throttle`) на `POST /api/v1/subscription-applications`.
- Не принимать лишние поля (использовать только явно разрешённые).

### 7.2 CRM — отправка заявки
- Не передавать пароль и другие чувствительные данные.
- Проверять `domain` перед отправкой (не пустой, формат).
- Обрабатывать таймауты и ошибки HTTP (логирование, не ломать регистрацию).

### 7.3 Токен
- Хранить в ADMIN в виде хеша (например, `Hash::make`) — при проверке использовать `Hash::check`.  
  Либо хранить в открытом виде, если нужна выдача того же токена CRM (сейчас мы отдаём его в ответе). В последнем случае обеспечить HTTPS и ограничение доступа к БД.
- В CRM хранить только в БД (`settings`), не в логах и не во frontend.

---

## 8. Рекомендации и дополнения

1. **Отдельный конфиг интеграции**  
   В CRM вынести `APP_CRM_URL`, `APP_DOMAIN` (если нужен) в `config/integration.php` — так проще менять окружения.

2. **Idempotency при заявках**  
   Рассмотреть уникальность по `(domain, email)` для заявок: при повторной регистрации с того же домена/email — обновлять существующую заявку (продлевать `expires_at`, обновлять `name`) вместо создания дубликата.

3. **Логирование**  
   В CRM логировать факт вызова ADMIN (успех/ошибка, домен, email без пароля). В ADMIN — логирование создания заявок и одобрений.

4. **Команда Artisan в ADMIN**  
   `php artisan subscribers:sync-token-from-applications` — для массового переноса токенов из заявок в подписчиков при миграциях или сценариях одобрения.

5. **Заморозка регистрации при недоступности ADMIN**  
   По умолчанию в плане регистрация не блокируется при сбое ADMIN. Если нужно «жёстко» требовать успешную заявку — ввести опцию в конфиге (например, `integration.require_subscription_application`) и в этом случае при ошибке ADMIN возвращать 503 и не создавать пользователя.

6. **Единый формат ответов API**  
   Для ADMIN v1 использовать структуру `{ message?, data?, errors? }` и единообразные коды ответов (200, 201, 400, 422, 401, 403).

---

## 9. Порядок реализации (краткий)

1. **ADMIN**
   - Миграции: `plans`, `subscribers`, `subscription_applications`, `notifications`.
   - Сидер планов (Стандарт, Премиум).
   - Модели: `Plan`, `Subscriber`, `SubscriptionApplication`.
   - Роуты `api/v1`: публичный `POST subscription-applications`, контроллер + request validation.
   - Создание заявки → генерация токена → уведомления для manager/administrator.
   - Подключить Database Notifications к `GET /api/notifications`.
   - API админки: `GET/PATCH` подписчики и заявки (список, детали, одобрение).
   - UI: пункт «Подписчики», страницы список и детали, пагинация и фильтры.

2. **CRM**
   - Миграция `settings`, модель `Setting`.
   - Конфиг `APP_CRM_URL` / `APP_DOMAIN`.
   - Сервис `AdminApiService::sendSubscriptionApplication`.
   - Изменение `AuthController::register`: после регистрации вызвать сервис, сохранить токен в `settings`.

3. **Интеграционные тесты**
   - Регистрация в CRM → заявка в ADMIN, токен в `settings`.
   - Проверка уведомлений в ADMIN и отображения в header.

4. **Дальнейшие шаги**
   - Middleware проверки API-токена в ADMIN для v1.
   - Реализация последующих сценариев CRM ↔ ADMIN с использованием сохранённого токена.

---

## 10. Чек-лист по пунктам ТЗ

| Требование | Реализация |
|------------|------------|
| Пункт меню «Подписчики» в ADMIN | Раздел 6.1 — добавить в `AdminSidebar.vue` |
| Список подписчиков с пагинацией и фильтрацией | Раздел 6.2 — `SubscribersPage`, API `GET /api/admin/subscribers` |
| Детальная страница подписчика | Раздел 6.3 — `SubscriberDetailPage`, `GET /api/admin/subscribers/{id}` |
| Поля подписчика: домен, логин, начало/конец подписки, активность, план, api_token, оплата | Раздел 2.2 — модель `Subscriber` |
| Таблица планов: Стандарт, Премиум; стоимость, активность, JSON ограничений | Раздел 2.1, 2.5 — `plans`, сидер |
| API v1 в ADMIN | Раздел 3.1 — префикс `/api/v1/` |
| Публичный роут приёма заявки от CRM | Раздел 3.2 — `POST /api/v1/subscription-applications` |
| Заявка на 3 дня, api_token, ответ с данными записи | Раздел 3.2, 2.3 |
| Уведомление в ADMIN header о новой заявке | Раздел 4, 3.2 — Database Notifications, `GET /api/notifications` |
| CRM: после регистрации запрос в ADMIN, сохранение токена | Раздел 5 — `AdminApiService`, `settings`, `AuthController::register` |
| Валидация данных в обоих проектах | Раздел 7 |
| Будущие запросы CRM ↔ ADMIN по токену | Раздел 3.4, 7.3 |

---

**Документ:** план интеграции CRM ↔ ADMIN.  
**Версия:** 1.0.  
**Дата:** 2026-01-25.
