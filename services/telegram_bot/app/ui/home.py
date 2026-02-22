"""HOME screen: welcome, start, hero (offer + 6 buttons + optional CTA). Reply keyboard always visible."""
import logging
from aiogram import Router, F
from aiogram.types import (
    Message,
    CallbackQuery,
    InlineKeyboardMarkup,
    InlineKeyboardButton,
    ReplyKeyboardMarkup,
    KeyboardButton,
)

from app.bot_instance import bot
from app.http import get_settings, post_event
from app.storage import get_hero_message_id, set_hero_message_id, clear_hero_message_id, rate_limit_allow, clear_fsm_state
from app.ui.helpers import edit_or_send_hero, delete_temp_messages, btn

router = Router(name="home")
logger = logging.getLogger(__name__)

# Тексты Reply-кнопок (постоянное меню) — те же, что и в меню
REPLY_BTN_SERVICES = "🧩 Услуги"
REPLY_BTN_CASES = "💼 Кейсы"
REPLY_BTN_REVIEWS = "⭐ Отзывы"
REPLY_BTN_SUPPORT = "🆘 Поддержка"
REPLY_BTN_SITE = "🌐 Сайт"
REPLY_BTN_PRESENTATION = "📎 Презентация"
REPLY_BTN_LEAD = "📝 Оставить заявку"
REPLY_BTN_MANAGER = "💬 Написать менеджеру"


def _reply_keyboard(settings: dict) -> ReplyKeyboardMarkup:
    """Постоянная клавиатура внизу экрана (всегда на виду)."""
    row1 = [
        KeyboardButton(text=REPLY_BTN_SERVICES),
        KeyboardButton(text=REPLY_BTN_CASES),
        KeyboardButton(text=REPLY_BTN_REVIEWS),
    ]
    row2 = [
        KeyboardButton(text=REPLY_BTN_SUPPORT),
        KeyboardButton(text=REPLY_BTN_SITE),
        KeyboardButton(text=REPLY_BTN_PRESENTATION),
    ]
    rows = [row1, row2]
    flags = settings.get("feature_flags") or {}
    if flags.get("cta_buttons"):
        rows.append([
            KeyboardButton(text=REPLY_BTN_LEAD),
            KeyboardButton(text=REPLY_BTN_MANAGER),
        ])
    return ReplyKeyboardMarkup(
        keyboard=rows,
        resize_keyboard=True,
        is_persistent=True,
    )


def _home_keyboard(settings: dict) -> InlineKeyboardMarkup:
    row1 = [
        btn(REPLY_BTN_SERVICES, "screen:services"),
        btn(REPLY_BTN_CASES, "screen:cases"),
        btn(REPLY_BTN_REVIEWS, "screen:reviews"),
    ]
    row2 = [
        btn(REPLY_BTN_SUPPORT, "screen:support"),
        btn(REPLY_BTN_SITE, "screen:site"),
        btn(REPLY_BTN_PRESENTATION, "screen:presentation"),
    ]
    rows = [row1, row2]
    flags = settings.get("feature_flags") or {}
    if flags.get("cta_buttons"):
        rows.append([btn(REPLY_BTN_LEAD, "flow:lead"), btn(REPLY_BTN_MANAGER, "cta:manager")])
    return InlineKeyboardMarkup(inline_keyboard=rows)


async def _show_home(chat_id: int, user_id: int, text: str, settings: dict, from_callback: bool = False):
    reply_kbd = _reply_keyboard(settings)
    banner_id = (settings.get("home_banner_file_id") or "").strip()
    if from_callback:
        await delete_temp_messages(bot, chat_id, user_id)
        mid = await get_hero_message_id(user_id)
        if mid:
            try:
                await bot.delete_message(chat_id=chat_id, message_id=mid)
            except Exception:
                pass
            await clear_hero_message_id(user_id)
        await edit_or_send_hero(bot, chat_id, user_id, text, reply_kbd)
        return
    if not banner_id:
        await edit_or_send_hero(bot, chat_id, user_id, text, reply_kbd)
        return
    await delete_temp_messages(bot, chat_id, user_id)
    try:
        m = await bot.send_photo(chat_id=chat_id, photo=banner_id, caption=text, reply_markup=reply_kbd)
        await set_hero_message_id(user_id, m.message_id)
    except Exception as e:
        logger.warning("send_banner_failed %s", e)
        await edit_or_send_hero(bot, chat_id, user_id, text, reply_kbd)


@router.message(F.text.in_(["/start", "Старт", "▶️ Старт", "старт"]))
async def cmd_start(message: Message):
    user_id = message.from_user.id if message.from_user else 0
    chat_id = message.chat.id
    if not await rate_limit_allow(user_id):
        await message.answer("Слишком много запросов. Подождите минуту.")
        return
    settings_data, err = await get_settings()
    if err:
        await message.answer(err or "Сервис временно недоступен.")
        return
    settings = settings_data.get("data") if isinstance(settings_data, dict) else {}
    welcome = (settings.get("start_text") or settings.get("welcome_text") or "Добрый день. Нажмите кнопки ниже.").strip()
    offer = (settings.get("home_offer_text") or welcome).strip()
    await post_event(user_id, "screen_view", {"screen": "home"})
    try:
        await message.delete()
    except Exception:
        pass
    await _show_home(chat_id, user_id, offer, settings)


@router.callback_query(F.data == "screen:home")
async def cb_home(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    chat_id = cq.message.chat.id if cq.message else 0
    await clear_fsm_state(user_id)
    await delete_temp_messages(bot, chat_id, user_id)
    settings_data, err = await get_settings()
    if err:
        await cq.message.edit_text(err)  # type: ignore
        return
    settings = settings_data.get("data") if isinstance(settings_data, dict) else {}
    offer = (settings.get("home_offer_text") or "Главное меню").strip()
    await post_event(user_id, "screen_view", {"screen": "home"})
    await _show_home(chat_id, user_id, offer, settings, from_callback=True)


@router.callback_query(F.data == "screen:site")
async def cb_site(cq: CallbackQuery):
    await cq.answer()
    settings_data, _ = await get_settings()
    settings = (settings_data or {}).get("data") if isinstance(settings_data, dict) else {}
    url = (settings.get("site_url") or "").strip()
    if url:
        await cq.answer(url=url)
    else:
        await cq.answer("Сайт не настроен.", show_alert=True)


@router.callback_query(F.data == "screen:presentation")
async def cb_presentation(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    chat_id = cq.message.chat.id if cq.message else 0
    settings_data, _ = await get_settings()
    settings = (settings_data or {}).get("data") if isinstance(settings_data, dict) else {}
    file_id = (settings.get("presentation_file_id") or "").strip()
    url = (settings.get("presentation_url") or "").strip()
    if file_id:
        try:
            m = await bot.send_document(chat_id=chat_id, document=file_id)
            from app.storage import append_temp_message_id
            await append_temp_message_id(user_id, m.message_id)
        except Exception as e:
            logger.warning("send_presentation_failed %s", e)
    elif url:
        await cq.message.answer(f"Презентация: {url}")  # type: ignore
    else:
        await cq.answer("Презентация не настроена.", show_alert=True)


@router.callback_query(F.data == "cta:manager")
async def cta_manager(cq: CallbackQuery):
    await cq.answer()
    settings_data, _ = await get_settings()
    settings = (settings_data or {}).get("data") if isinstance(settings_data, dict) else {}
    username = (settings.get("manager_username") or "").strip().lstrip("@")
    if username:
        await cq.answer(url=f"https://t.me/{username}")
    else:
        await cq.answer("Менеджер не настроен.", show_alert=True)


# ---------- Reply-кнопки (постоянное меню): тот же функционал, что и callback ----------


@router.message(F.text == REPLY_BTN_SERVICES)
async def msg_services(message: Message):
    user_id = message.from_user.id if message.from_user else 0
    chat_id = message.chat.id
    mid = await get_hero_message_id(user_id)
    if not mid:
        await message.answer("Нажмите /start.")
        return
    from app.ui.services import show_services_screen
    if not await show_services_screen(chat_id, user_id, mid):
        await message.answer("Подождите минуту.")


@router.message(F.text == REPLY_BTN_CASES)
async def msg_cases(message: Message):
    user_id = message.from_user.id if message.from_user else 0
    chat_id = message.chat.id
    mid = await get_hero_message_id(user_id)
    if not mid:
        await message.answer("Нажмите /start.")
        return
    from app.ui.cases import show_cases_screen
    if not await show_cases_screen(chat_id, user_id, mid):
        await message.answer("Подождите минуту.")


@router.message(F.text == REPLY_BTN_REVIEWS)
async def msg_reviews(message: Message):
    user_id = message.from_user.id if message.from_user else 0
    chat_id = message.chat.id
    mid = await get_hero_message_id(user_id)
    if not mid:
        await message.answer("Нажмите /start.")
        return
    from app.ui.reviews import show_reviews_screen
    if not await show_reviews_screen(chat_id, user_id, mid):
        await message.answer("Подождите минуту.")


@router.message(F.text == REPLY_BTN_SUPPORT)
async def msg_support(message: Message):
    user_id = message.from_user.id if message.from_user else 0
    chat_id = message.chat.id
    mid = await get_hero_message_id(user_id)
    if not mid:
        await message.answer("Нажмите /start.")
        return
    from app.ui.support import show_support_screen
    if not await show_support_screen(chat_id, user_id, mid):
        await message.answer("Подождите минуту.")


@router.message(F.text == REPLY_BTN_SITE)
async def msg_site(message: Message):
    settings_data, _ = await get_settings()
    settings = (settings_data or {}).get("data") if isinstance(settings_data, dict) else {}
    url = (settings.get("site_url") or "").strip()
    if url:
        await message.answer(f"Сайт: {url}")
    else:
        await message.answer("Сайт не настроен.")


@router.message(F.text == REPLY_BTN_PRESENTATION)
async def msg_presentation(message: Message):
    user_id = message.from_user.id if message.from_user else 0
    chat_id = message.chat.id
    settings_data, _ = await get_settings()
    settings = (settings_data or {}).get("data") if isinstance(settings_data, dict) else {}
    file_id = (settings.get("presentation_file_id") or "").strip()
    url = (settings.get("presentation_url") or "").strip()
    if file_id:
        try:
            from app.storage import append_temp_message_id
            m = await bot.send_document(chat_id=chat_id, document=file_id)
            await append_temp_message_id(user_id, m.message_id)
        except Exception as e:
            logger.warning("send_presentation_failed %s", e)
            await message.answer("Не удалось отправить файл.")
    elif url:
        await message.answer(f"Презентация: {url}")
    else:
        await message.answer("Презентация не настроена.")


@router.message(F.text == REPLY_BTN_LEAD)
async def msg_lead(message: Message):
    from app.flows.lead import start_lead_flow_from_message
    await start_lead_flow_from_message(message)


@router.message(F.text == REPLY_BTN_MANAGER)
async def msg_manager(message: Message):
    settings_data, _ = await get_settings()
    settings = (settings_data or {}).get("data") if isinstance(settings_data, dict) else {}
    username = (settings.get("manager_username") or "").strip().lstrip("@")
    if username:
        await message.answer(f"Написать менеджеру: https://t.me/{username}")
    else:
        await message.answer("Менеджер не настроен.")
