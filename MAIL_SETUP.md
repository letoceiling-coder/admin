# Настройка почты: как избежать попадания писем в спам

Сайт: **https://admin.neeklo.ru/**  
Почта отправляется через Beget SMTP (info@neeklo.ru).

## 1. Проверка .env

Убедитесь, что в `.env` на сервере указано:

```env
# Обязательно
MAIL_MAILER=smtp
MAIL_HOST=smtp.beget.com
MAIL_PORT=465
MAIL_USERNAME=info@neeklo.ru
MAIL_PASSWORD=ваш_пароль_от_ящика
MAIL_ENCRYPTION=ssl

# От кого письмо (должно совпадать с доменом ящика!)
MAIL_FROM_ADDRESS=info@neeklo.ru
MAIL_FROM_NAME="Neeklo"

# Домен сайта (для ссылок и идентификации)
APP_URL=https://admin.neeklo.ru

# Рекомендуется: домен для SMTP EHLO (без https://)
# Укажите именно домен почты: neeklo.ru
MAIL_EHLO_DOMAIN=neeklo.ru
```

**Важно:**

- `MAIL_FROM_ADDRESS` должен быть **тот же ящик**, что и `MAIL_USERNAME` (info@neeklo.ru), иначе почтовики чаще помечают письмо как спам.
- `MAIL_FROM_NAME` — человекочитаемое имя (например «Neeklo» или «Админ Neeklo»), не оставляйте «Example».
- `MAIL_EHLO_DOMAIN=neeklo.ru` — домен без поддомена и без протокола; улучшает идентификацию при отправке через Beget.

После изменений на сервере выполните:

```bash
php artisan config:clear
```

## 2. DNS-записи домена neeklo.ru

Письма не попадут в спам только при правильных SPF и желательно DKIM для домена **neeklo.ru**.

### SPF (обязательно)

В панели управления доменом (Beget или где зарегистрирован neeklo.ru) добавьте **одну** TXT-запись для корня домена:

| Поле   | Значение |
|--------|----------|
| Имя/Хост | `@` |
| Тип    | `TXT`    |
| Значение | `v=spf1 include:spf.beget.com ~all` |

Если SPF уже есть (например, от другого сервиса), **не создавайте вторую**. Добавьте в существующую запись `include:spf.beget.com` и оставьте `~all` в конце.

### DKIM (сильно снижает риск спама)

1. В панели Beget откройте настройки почты для домена neeklo.ru.
2. Включите DKIM и скопируйте предложенную TXT-запись (поддомен вида `mail._domainkey` или аналогичный).
3. Добавьте эту TXT-запись в DNS домена neeklo.ru.

Подробнее: [настройка DKIM для Beget](https://ru.support.powerdmarc.com/support/solutions/articles/60000719751-how-to-set-up-dkim-for-beget-ru-).

### DMARC (по желанию)

Дополнительно можно добавить TXT-запись:

| Имя   | Тип | Значение |
|-------|-----|----------|
| `_dmarc` | `TXT` | `v=DMARC1; p=quarantine; rua=mailto:info@neeklo.ru` |

Это указывает почтовым серверам, что делать с письмами, не прошедшими SPF/DKIM (например, помещать в карантин и присылать отчёты на info@neeklo.ru).

## 3. Что уже сделано в коде

- В письме явно задаются **From** и **Reply-To** из `config('mail.from')`.
- Для SMTP используется **local_domain** (EHLO): из `MAIL_EHLO_DOMAIN` или из хоста `APP_URL`.

## 4. Краткий чек-лист

- [ ] В .env: `MAIL_FROM_ADDRESS=info@neeklo.ru`, `MAIL_FROM_NAME` — осмысленное имя.
- [ ] В .env: `MAIL_EHLO_DOMAIN=neeklo.ru`.
- [ ] В DNS домена neeklo.ru: SPF с `include:spf.beget.com`.
- [ ] В DNS: DKIM по инструкции Beget.
- [ ] Выполнено `php artisan config:clear` после правок .env.

После настройки DNS изменения могут применяться до 24–48 часов. Затем отправьте тестовое КП и проверьте папку «Спам» у получателя.
