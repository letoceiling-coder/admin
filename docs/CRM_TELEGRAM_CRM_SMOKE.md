# CRM Neeklo Bot — smoke-проверки (ШАГ 3)

## Страницы /crm/neeklo-bot/*

После входа под пользователем с ролью manager или administrator откройте:

- https://admin.neeklo.ru/crm — главная CRM
- https://admin.neeklo.ru/crm/neeklo-bot/settings — настройки бота
- https://admin.neeklo.ru/crm/neeklo-bot/services — услуги
- https://admin.neeklo.ru/crm/neeklo-bot/services/categories — категории услуг
- https://admin.neeklo.ru/crm/neeklo-bot/cases — кейсы (и медиа по кнопке «Медиа»)
- https://admin.neeklo.ru/crm/neeklo-bot/reviews — отзывы (вкладки «На модерации» / «Одобренные», кнопка «Одобрить»)
- https://admin.neeklo.ru/crm/neeklo-bot/faq — FAQ
- https://admin.neeklo.ru/crm/neeklo-bot/leads — лиды (смена статуса, кнопка «Экспорт CSV»)
- https://admin.neeklo.ru/crm/neeklo-bot/tickets — тикеты (смена статуса)

## Что должно быть видно

После выполнения сидера (ШАГ 1):

- **Категории услуг:** 3 (Разработка, Дизайн, Маркетинг).
- **Услуги:** 8 (по категориям).
- **Кейсы:** 6.
- **Отзывы:** 10 одобренных (вкладка «Одобренные»).
- **FAQ:** 8 записей.
- **Настройки:** одна запись (при первом открытии может быть пустая — сохраните поля и обновите).
- **Лиды / Тикеты:** пусто, пока бот не создаёт заявки.

## Проверка API из браузера

1. Войти в /admin или /crm (Sanctum cookie + Bearer в localStorage).
2. DevTools → Network: запросы к `/api/crm/telegram/*` должны уходить с заголовком `Authorization: Bearer <token>` и возвращать 200 с телом `{"data": ...}`.
3. Без авторизации запрос к `/api/crm/telegram/settings` даёт 401.

## Список маршрутов API

```bash
php artisan route:list --path=crm
```

См. вывод команды: GET/PUT settings; CRUD service-categories, services, cases, case-media; reviews (index, update, approve, reject); CRUD faq; leads (index, update, export.csv); tickets (index, update).
