"""Lead flow: 5 steps, then POST /api/telegram/leads, notify, HOME."""
import logging
from aiogram import Router, F
from aiogram.types import CallbackQuery, Message, InlineKeyboardMarkup

from app.bot import bot
from app.http import get_settings, post_lead, post_event
from app.storage import get_fsm_state, set_fsm_state, clear_fsm_state, rate_limit_allow
from app.ui.helpers import edit_or_send_hero, delete_temp_messages, btn
from app.ui.home import _show_home

router = Router(name="lead")
logger = logging.getLogger(__name__)


async def _notify_admin(text: str):
    settings_data, _ = await get_settings()
    settings = (settings_data or {}).get("data") if isinstance(settings_data, dict) else {}
    chat_id = (settings.get("notify_chat_id") or "").strip()
    if not chat_id:
        return
    try:
        await bot.send_message(chat_id=chat_id, text=text)
    except Exception as e:
        logger.warning("notify_admin_error %s", e)


async def start_lead_flow(cq: CallbackQuery, source_service_id: int = None, source_case_id: int = None):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    name = (cq.from_user.full_name or "").strip() if cq.from_user else ""
    username = (cq.from_user.username or "").strip() or None if cq.from_user else None
    await set_fsm_state(user_id, "lead:name", {"source_service_id": source_service_id, "source_case_id": source_case_id, "name": name, "username": username})
    kbd = InlineKeyboardMarkup(inline_keyboard=[
        [btn("Пропустить", "flow:lead:skip_name")],
        [btn("◀ Отмена", "screen:home")],
    ])
    await cq.message.edit_text("Введите ваше имя или нажмите «Пропустить»:", reply_markup=kbd)  # type: ignore


@router.callback_query(F.data == "flow:lead")
async def cb_lead_start(cq: CallbackQuery):
    await start_lead_flow(cq)


@router.callback_query(F.data == "flow:lead:skip_name")
async def cb_lead_skip_name(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    chat_id = cq.message.chat.id if cq.message else 0
    st = await get_fsm_state(user_id)
    if not st or st.get("state") != "lead:name":
        return
    data = st.get("data") or {}
    await set_fsm_state(user_id, "lead:contact", data)
    await cq.message.edit_text("Введите контакт (телефон или @username):", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[btn("◀ Отмена", "screen:home")]]))  # type: ignore


@router.message(F.text, F.text.len() <= 200)
async def msg_lead_step(message: Message):
    user_id = message.from_user.id if message.from_user else 0
    chat_id = message.chat.id
    st = await get_fsm_state(user_id)
    if not st:
        return
    state = st.get("state")
    data = st.get("data") or {}
    from app.storage import get_hero_message_id
    mid = await get_hero_message_id(user_id)
    try:
        await message.delete()
    except Exception:
        pass
    if not mid:
        return
    if state == "lead:name":
        data["name"] = (message.text or "").strip()[:200]
        await set_fsm_state(user_id, "lead:contact", data)
        await bot.edit_message_text(chat_id=chat_id, message_id=mid, text="Введите контакт (телефон или @username):", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[btn("◀ Отмена", "screen:home")]]))
        return
    if state == "lead:contact":
        data["contact"] = (message.text or "").strip()[:200]
        await set_fsm_state(user_id, "lead:message", data)
        await bot.edit_message_text(chat_id=chat_id, message_id=mid, text="Что нужно? (кратко опишите задачу):", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[btn("◀ Отмена", "screen:home")]]))
        return
    if state == "lead:message":
        data["message"] = (message.text or "").strip()[:2000]
        await set_fsm_state(user_id, "lead:budget", data)
        kbd = InlineKeyboardMarkup(inline_keyboard=[
            [btn("До 50 тыс.", "flow:lead:budget:50"), btn("50–100 тыс.", "flow:lead:budget:100")],
            [btn("100–300 тыс.", "flow:lead:budget:300"), btn("По договорённости", "flow:lead:budget:any")],
            [btn("◀ Отмена", "screen:home")],
        ])
        await bot.edit_message_text(chat_id=chat_id, message_id=mid, text="Бюджет?", reply_markup=kbd)
        return
    if state == "lead:deadline":
        data["deadline_text"] = (message.text or "").strip()[:100]
        await _submit_lead(user_id, chat_id, data)
        return


@router.callback_query(F.data.startswith("flow:lead:budget:"))
async def cb_lead_budget(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    chat_id = cq.message.chat.id if cq.message else 0
    val = cq.data.split(":")[-1]
    st = await get_fsm_state(user_id)
    if not st or st.get("state") != "lead:budget":
        return
    data = st.get("data") or {}
    data["budget"] = val
    await set_fsm_state(user_id, "lead:deadline", data)
    kbd = InlineKeyboardMarkup(inline_keyboard=[
        [btn("Срочно", "flow:lead:deadline:urgent"), btn("Обычно", "flow:lead:deadline:normal")],
        [btn("Не важно", "flow:lead:deadline:any")],
        [btn("◀ Отмена", "screen:home")],
    ])
    await cq.message.edit_text("Сроки?", reply_markup=kbd)  # type: ignore


@router.callback_query(F.data.startswith("flow:lead:deadline:"))
async def cb_lead_deadline(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    chat_id = cq.message.chat.id if cq.message else 0
    val = cq.data.split(":")[-1]
    st = await get_fsm_state(user_id)
    if not st:
        return
    data = st.get("data") or {}
    data["deadline"] = val
    await _submit_lead(user_id, chat_id, data)


async def _submit_lead(user_id: int, chat_id: int, data: dict):
    username = None
    from app.bot import bot
    try:
        u = await bot.get_chat(chat_id)
        if u.username:
            username = u.username
    except Exception:
        pass
    payload = {
        "tg_user_id": user_id,
        "username": username,
        "full_name": data.get("name"),
        "phone": data.get("contact") or username or "n/a",
        "message": data.get("message"),
        "source_service_id": data.get("source_service_id"),
        "source_case_id": data.get("source_case_id"),
    }
    resp, err = await post_lead(payload)
    await clear_fsm_state(user_id)
    if err:
        await bot.send_message(chat_id=chat_id, text=err)
        await _go_home(chat_id, user_id)
        return
    await post_event(user_id, "lead_created", {"source_service_id": data.get("source_service_id"), "source_case_id": data.get("source_case_id")})
    uname = username
    link = f"tg://user?id={user_id}"
    notify_text = f"Lead\nUser: {data.get('name')} @{uname or '—'} {link}\nContact: {payload.get('phone')}\nMessage: {data.get('message')}\nService: {data.get('source_service_id')} Case: {data.get('source_case_id')}"
    await _notify_admin(notify_text)
    await bot.send_message(chat_id=chat_id, text="Заявка отправлена. Мы свяжемся с вами.")
    await _go_home(chat_id, user_id)


async def _go_home(chat_id: int, user_id: int):
    await delete_temp_messages(bot, chat_id, user_id)
    settings_data, err = await get_settings()
    if err:
        return
    settings = (settings_data or {}).get("data") if isinstance(settings_data, dict) else {}
    offer = (settings.get("home_offer_text") or "Главное меню").strip()
    await _show_home(chat_id, user_id, offer, settings, from_callback=True)
