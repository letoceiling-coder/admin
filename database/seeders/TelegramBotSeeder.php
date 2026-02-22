<?php

namespace Database\Seeders;

use App\Models\TelegramBotCase;
use App\Models\TelegramBotCaseMedia;
use App\Models\TelegramBotFaq;
use App\Models\TelegramBotReview;
use App\Models\TelegramBotService;
use App\Models\TelegramBotServiceCategory;
use App\Models\TelegramBotSetting;
use Illuminate\Database\Seeder;

class TelegramBotSeeder extends Seeder
{
    public function run(): void
    {
        TelegramBotSetting::firstOrCreate([], [
            'welcome_text' => 'Добрый день, вы попали в Neeklo Studio. Здесь мы создаем готовые решения для бизнеса.',
            'start_text' => 'Нажмите «Старт», чтобы начать.',
            'home_offer_text' => 'Готовые решения для вашего бизнеса. Услуги, кейсы и отзывы — в одном месте.',
            'feature_flags' => ['cta_buttons' => true],
        ]);

        $cat1 = TelegramBotServiceCategory::create(['name' => 'Разработка', 'sort_order' => 1]);
        $cat2 = TelegramBotServiceCategory::create(['name' => 'Дизайн', 'sort_order' => 2]);
        $cat3 = TelegramBotServiceCategory::create(['name' => 'Маркетинг', 'sort_order' => 3]);

        $services = [
            ['category_id' => $cat1->id, 'name' => 'Сайты и лендинги', 'description' => 'Корпоративные сайты и посадочные страницы', 'result' => 'Готовый сайт с админкой', 'price_or_terms' => 'от 2 недель', 'sort_order' => 1],
            ['category_id' => $cat1->id, 'name' => 'Telegram-боты', 'description' => 'Автоматизация и интеграции в Telegram', 'result' => 'Рабочий бот под ваши задачи', 'price_or_terms' => 'от 1 недели', 'sort_order' => 2],
            ['category_id' => $cat1->id, 'name' => 'Интеграции API', 'description' => 'Связка CRM, 1С, маркетплейсов', 'result' => 'Единый контур данных', 'price_or_terms' => 'по ТЗ', 'sort_order' => 3],
            ['category_id' => $cat2->id, 'name' => 'Фирменный стиль', 'description' => 'Логотип, брендбук, гайды', 'result' => 'Узнаваемый бренд', 'price_or_terms' => 'от 5 дней', 'sort_order' => 1],
            ['category_id' => $cat2->id, 'name' => 'UI/UX интерфейсов', 'description' => 'Прототипы и дизайн интерфейсов', 'result' => 'Макеты для разработки', 'price_or_terms' => 'по проекту', 'sort_order' => 2],
            ['category_id' => $cat2->id, 'name' => 'Иллюстрации', 'description' => 'Иконки, инфографика, оформление', 'result' => 'Готовые материалы', 'price_or_terms' => 'от 3 дней', 'sort_order' => 3],
            ['category_id' => $cat3->id, 'name' => 'Таргет и реклама', 'description' => 'Настройка и ведение рекламы', 'result' => 'Стабильный поток заявок', 'price_or_terms' => 'ежемесячно', 'sort_order' => 1],
            ['category_id' => $cat3->id, 'name' => 'Контент-продвижение', 'description' => 'SMM, контент-план, копирайт', 'result' => 'Рост охватов и вовлечения', 'price_or_terms' => 'пакеты', 'sort_order' => 2],
        ];
        foreach ($services as $s) {
            TelegramBotService::create($s);
        }

        $cases = [
            ['title' => 'CRM для подписок', 'task' => 'Управление подписками и заявками', 'solution' => 'Laravel + Vue админка, API для бота', 'result' => 'Единая панель и автоматизация', 'tags' => ['crm', 'laravel'], 'sort_order' => 1],
            ['title' => 'Бот для записей', 'task' => 'Запись клиентов без звонков', 'solution' => 'Telegram-бот с календарем и напоминаниями', 'result' => 'Снижение no-show на 40%', 'tags' => ['telegram', 'автоматизация'], 'sort_order' => 2],
            ['title' => 'Лендинг стоматологии', 'task' => 'Конверсия заявок с рекламы', 'solution' => 'Лендинг + форма + интеграция с CRM', 'result' => 'Рост заявок в 2 раза', 'tags' => ['лендинг', 'медицина'], 'sort_order' => 3],
            ['title' => 'Бренд магазина одежды', 'task' => 'Узнаваемость и единый стиль', 'solution' => 'Логотип, палитра, шаблоны соцсетей', 'result' => 'Единый визуал на всех точках', 'tags' => ['дизайн', 'бренд'], 'sort_order' => 4],
            ['title' => 'Интеграция 1С и сайта', 'task' => 'Синхронизация остатков и заказов', 'solution' => 'REST API, обмен в реальном времени', 'result' => 'Актуальные цены и наличие', 'tags' => ['1С', 'интеграция'], 'sort_order' => 5],
            ['title' => 'Таргет для курсов', 'task' => 'Заявки на обучение', 'solution' => 'Настройка кампаний, креативы, аудитории', 'result' => 'Стабильный CPL', 'tags' => ['таргет', 'образование'], 'sort_order' => 6],
        ];
        foreach ($cases as $c) {
            TelegramBotCase::create($c);
        }

        $reviews = [
            ['author_name' => 'Алексей', 'company' => 'ООО «Старт»', 'rating' => 5, 'text' => 'Сделали бота под запись клиентов — всё работает без сбоев. Рекомендую.', 'status' => 'approved'],
            ['author_name' => 'Мария', 'company' => null, 'rating' => 5, 'text' => 'Быстро и качественно сделали лендинг. Заявки идут.', 'status' => 'approved'],
            ['author_name' => 'Дмитрий', 'company' => 'ИП Петров', 'rating' => 5, 'text' => 'Интеграция с 1С сдана в срок. Команда на связи.', 'status' => 'approved'],
            ['author_name' => 'Ольга', 'company' => null, 'rating' => 4, 'text' => 'Фирстиль понравился. Небольшие правки делали оперативно.', 'status' => 'approved'],
            ['author_name' => 'Игорь', 'company' => 'Студия маркетинга', 'rating' => 5, 'text' => 'Настроили таргет — лиды стабильные. Доволен результатом.', 'status' => 'approved'],
            ['author_name' => 'Анна', 'company' => null, 'rating' => 5, 'text' => 'Отличная коммуникация и результат. Спасибо!', 'status' => 'approved'],
            ['author_name' => 'Сергей', 'company' => 'ООО «Вектор»', 'rating' => 5, 'text' => 'CRM под наши процессы настроили за неделю. Всё логично.', 'status' => 'approved'],
            ['author_name' => 'Елена', 'company' => null, 'rating' => 5, 'text' => 'Презентацию и сайт сделали в едином стиле. Выглядит дорого.', 'status' => 'approved'],
            ['author_name' => 'Николай', 'company' => 'Магазин техники', 'rating' => 4, 'text' => 'Каталог на сайте синхронизировали с 1С. Работает.', 'status' => 'approved'],
            ['author_name' => 'Татьяна', 'company' => null, 'rating' => 5, 'text' => 'Первый раз заказывала бота — всё объяснили и сделали под ключ.', 'status' => 'approved'],
        ];
        foreach ($reviews as $r) {
            TelegramBotReview::create($r);
        }

        $faq = [
            ['question' => 'Как быстро можно начать?', 'answer' => 'После согласования ТЗ и предоплаты — от 3–5 дней в зависимости от объёма.', 'sort_order' => 1],
            ['question' => 'Работаете ли по договору?', 'answer' => 'Да. Заключаем договор, выставляем счёт. Для ИП и физлиц — упрощённо.', 'sort_order' => 2],
            ['question' => 'Можно ли доработать после сдачи?', 'answer' => 'Да. Гарантийные правки в рамках ТЗ — бесплатно. Доработки — по отдельной оценке.', 'sort_order' => 3],
            ['question' => 'Нужен ли свой сервер для бота?', 'answer' => 'Для работы бота нужен доступ в интернет и, при необходимости, Redis. Можем подсказать настройку VPS.', 'sort_order' => 4],
            ['question' => 'Делаете ли вы поддержку после запуска?', 'answer' => 'Да. Поддержка и доработки обсуждаются отдельно: почасовая или абонент.', 'sort_order' => 5],
            ['question' => 'Как передаётся доступ к проекту?', 'answer' => 'Репозиторий, доступы к хостингу и инструкции передаём после полной оплаты.', 'sort_order' => 6],
            ['question' => 'Работаете с НДС?', 'answer' => 'Да, работаем с НДС. Уточняйте при запросе КП.', 'sort_order' => 7],
            ['question' => 'Можно ли срочно?', 'answer' => 'Срочность обсуждаем индивидуально. Иногда возможно за счёт приоритета в очереди.', 'sort_order' => 8],
        ];
        foreach ($faq as $f) {
            TelegramBotFaq::create($f);
        }
    }
}
