# Устранение неполадок: CRM → ADMIN, заявки не появляются

## Симптом

При регистрации на http://crm.loc/admin/register в ADMIN не создаётся заявка (страница «Заявки» пуста).

## Частые причины и решения

### 1. ADMIN использует БД CRM (`laravel_crm`) вместо своей (`laravel_admin`)

**Ошибка в логах CRM:**  
`Table 'laravel_crm.subscription_applications' doesn't exist`

Таблицы `subscription_applications`, `plans`, `subscribers`, `notifications` созданы в БД **ADMIN** (`laravel_admin`). Если ADMIN при обработке HTTP-запросов подключается к `laravel_crm`, в ней этих таблиц нет → 500, заявка не создаётся.

**Что сделать:**

1. **Проверка БД, которую видит ADMIN при HTTP-запросах**
   - Откройте в браузере: `http://admin.loc/api/v1/ping`
   - В ответе должно быть: `{"ok":true,"db":"laravel_admin"}`  
   - Если `"db":"laravel_crm"` — ADMIN при веб-запросах использует БД CRM.

2. **Убрать переопределение БД для admin.loc**
   - В конфиге vhost для **admin.loc** (Apache/Nginx в OSPanel) не должно быть:
     - `SetEnv DB_DATABASE laravel_crm` (Apache)
     - `fastcgi_param DB_DATABASE laravel_crm;` (Nginx)
   - Удалите или замените на `laravel_admin`, если нужно задать БД через vhost.

3. **Проверить .env ADMIN**
   - В `admin/.env` должно быть: `DB_DATABASE=laravel_admin`

4. **Очистить кеш конфига ADMIN**
   ```bash
   cd admin
   php artisan config:clear
   ```

5. **Убедиться, что БД и миграции ADMIN в порядке**
   ```bash
   cd admin
   php artisan migrate --force
   ```

### 2. CRM не отправляет заявку в ADMIN (APP_CRM_URL)

**В логах CRM:**  
`AdminApiService: APP_CRM_URL не задан, пропуск отправки заявки`

**Что сделать:**

- В `crm/.env` задать: `APP_CRM_URL=http://admin.loc/api/v1` (без завершающего слеша или с ним — оба варианта обрабатываются).
- Выполнить: `cd crm && php artisan config:clear`

### 3. Запрос из браузера без Accept: application/json

Регистрация идёт через SPA; фронтенд должен отправлять `Accept: application/json`. Если при ручных проверках (curl, Postman) вы не передаёте этот заголовок, возможны редиректы и HTML вместо JSON. Убедитесь, что запросы к API идут с `Accept: application/json`.

### 4. Проверка полного цикла

1. В **ADMIN**: `config:clear`, `migrate`, проверить `http://admin.loc/api/v1/ping` → `"db":"laravel_admin"`.
2. В **CRM**: `APP_CRM_URL` в `.env`, `config:clear`.
3. Зарегистрировать нового пользователя на http://crm.loc/admin/register.
4. Открыть в ADMIN «Заявки» — должна появиться новая запись.
5. При ошибках смотреть:
   - `crm/storage/logs/laravel.log` — вызовы AdminApiService, ответы ADMIN;
   - `admin/storage/logs/laravel.log` — ошибки при создании заявки.

## Диагностический эндпоинт

- **GET** `http://admin.loc/api/v1/ping`  
- Ответ: `{"ok":true,"db":"<текущая БД ADMIN>"}`  
- Используется, чтобы проверить, какую БД видит ADMIN при веб-запросах.
