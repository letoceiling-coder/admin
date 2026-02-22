<template>
  <div class="documents-page">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-foreground">Документация</h1>
      <p class="text-muted-foreground mt-1">Настройка CRM и Telegram-бота Neeklo Studio: что за что отвечает и как пользоваться</p>
    </div>
    <div class="bg-card rounded-lg border border-border p-6 max-w-3xl space-y-8 text-sm text-foreground">
      <section>
        <h2 class="text-lg font-semibold mb-3">Настройки бота (что за что отвечает)</h2>
        <ul class="space-y-2 list-disc list-inside text-muted-foreground">
          <li><strong class="text-foreground">Приветствие</strong> — текст до нажатия кнопки «Старт» в боте.</li>
          <li><strong class="text-foreground">Текст старта</strong> — текст, который пользователь видит после /start (если не подставляется оффер).</li>
          <li><strong class="text-foreground">Оффер на главной</strong> — основной текст на главном экране бота (главное сообщение + кнопки: Услуги, Кейсы, Отзывы, Поддержка, Сайт, Презентация).</li>
          <li><strong class="text-foreground">ID баннера (file_id Telegram)</strong> — если задан, на главной показывается картинка (баннер). File_id получают, отправив боту изображение и взяв его из ответа API или логов.</li>
          <li><strong class="text-foreground">URL сайта</strong> — ссылка, которая открывается по кнопке «Сайт» в боте.</li>
          <li><strong class="text-foreground">ID презентации / URL презентации</strong> — по кнопке «Презентация» бот отправляет файл (по file_id) или открывает ссылку. Заполните то, что нужно.</li>
          <li><strong class="text-foreground">Username менеджера</strong> — @username для кнопки «Написать менеджеру» (если включены CTA-кнопки).</li>
          <li><strong class="text-foreground">ID чата для уведомлений (notify_chat_id)</strong> — ID чата или группы в Telegram, куда бот присылает уведомления о новых лидах, тикетах и заявках на звонок. Если пусто — уведомления не отправляются.</li>
          <li><strong class="text-foreground">UTM-шаблон</strong> — шаблон UTM-меток для ссылок (если используется в рассылках/ссылках бота).</li>
          <li><strong class="text-foreground">Feature flags</strong> (в настройках, если есть) — например «cta_buttons»: при включении на главной показываются кнопки «Оставить заявку» и «Написать менеджеру».</li>
        </ul>
      </section>

      <section>
        <h2 class="text-lg font-semibold mb-3">Разделы CRM и возможности</h2>
        <ul class="space-y-2 list-disc list-inside text-muted-foreground">
          <li><strong class="text-foreground">Главная (Дашборд)</strong> — сводка и блоки «Top services» / «Top cases» за последние 30 дней по заявкам из бота.</li>
          <li><strong class="text-foreground">Настройки бота</strong> — все тексты, баннер, сайт, презентация, notify_chat_id и флаги. Сохраните после изменений.</li>
          <li><strong class="text-foreground">Категории услуг</strong> — категории для раздела «Услуги» в боте. В боте сначала показываются категории, затем список услуг по выбранной категории.</li>
          <li><strong class="text-foreground">Услуги</strong> — список услуг с привязкой к категории. В боте по каждой услуге можно оставить заявку (лид с source_service_id).</li>
          <li><strong class="text-foreground">Кейсы</strong> — кейсы с текстом и медиа. В боте отображаются список и детали кейса; кнопка «Хочу так же» создаёт лид с source_case_id.</li>
          <li><strong class="text-foreground">Отзывы</strong> — одобренные отображаются в боте; здесь можно approve/reject. Пользователь может оставить отзыв в боте (антиспам: раз в 24 часа).</li>
          <li><strong class="text-foreground">FAQ</strong> — вопросы и ответы для раздела «Поддержка» в боте. Порядок — по sort_order.</li>
          <li><strong class="text-foreground">Лиды</strong> — заявки из бота (форма «Оставить заявку»). Можно менять статус (new / in_progress / done) и скачивать export.csv.</li>
          <li><strong class="text-foreground">Тикеты</strong> — обращения из раздела «Поддержка» → «Создать тикет». Управление статусами.</li>
        </ul>
      </section>

      <section>
        <h2 class="text-lg font-semibold mb-3">Как настроить уведомления</h2>
        <p class="text-muted-foreground">
          Укажите в настройках бота <strong class="text-foreground">notify_chat_id</strong>: ID чата или группы (например, получите через @userinfobot или добавьте бота в группу и посмотрите обновление с chat.id). После сохранения при каждом новом лиде, тикете или заявке на звонок бот отправит в этот чат сообщение с типом, именем пользователя, @username, ссылкой tg://user?id=… и содержимым формы. Если notify_chat_id пустой — уведомления не отправляются.
        </p>
      </section>

      <section>
        <h2 class="text-lg font-semibold mb-3">Деплой и проверка</h2>
        <p class="text-muted-foreground mb-2">
          Инструкции по установке Laravel и бота на сервере, переменные окружения, systemd и проверка здоровья бота — в репозитории: <strong class="text-foreground">docs/README_DEPLOY.md</strong>. Чек-лист приёмки (миграции, API, CRM UI, бот, E2E) — <strong class="text-foreground">docs/ACCEPTANCE.md</strong>. Smoke-проверка API бота: <strong class="text-foreground">tools/smoke/telegram_api_smoke.sh</strong> (BASE_URL и CRM_BOT_API_TOKEN).
        </p>
      </section>
    </div>
  </div>
</template>

<script setup>
</script>
