"""Ticket flow: subject, message -> POST tickets, notify, HOME."""
import logging
from aiogram import Router, F
from aiogram.types import CallbackQuery, Message, InlineKeyboardMarkup

from app.bot_instance import bot
from app.http import post_ticket, post_event
from app.storage import get_fsm_state, set_fsm_state, clear_fsm_state, get_hero_message_id
from app.ui.helpers import btn
from app.flows.lead import _notify_admin, _go_home

router = Router(name="ticket")


async def start_ticket_flow(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    username = (cq.from_user.username or "").strip() or None if cq.from_user else None
    full_name = (cq.from_user.full_name or "").strip() if cq.from_user else ""
    await set_fsm_state(user_id, "ticket:subject", {"username": username, "full_name": full_name})
    await cq.message.edit_text("Тема обращения:", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[btn("◀ Отмена", "screen:home")]]))


@router.message(F.text, F.text.len() <= 500)
async def msg_ticket(message: Message):
    user_id = message.from_user.id if message.from_user else 0
    chat_id = message.chat.id
    st = await get_fsm_state(user_id)
    if not st or not st.get("state", "").startswith("ticket:"):
        return
    state = st.get("state")
    data = st.get("data") or {}
    mid = await get_hero_message_id(user_id)
    try:
        await message.delete()
    except Exception:
        pass
    if not mid:
        return
    if state == "ticket:subject":
        data["subject"] = (message.text or "").strip()[:255]
        await set_fsm_state(user_id, "ticket:message", data)
        await bot.edit_message_text(chat_id=chat_id, message_id=mid, text="Опишите проблему или вопрос:", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[btn("◀ Отмена", "screen:home")]]))
        return
    if state == "ticket:message":
        data["message"] = (message.text or "").strip()[:5000]
        await _submit_ticket(user_id, chat_id, data)


async def _submit_ticket(user_id: int, chat_id: int, data: dict):
    payload = {
        "tg_user_id": user_id,
        "username": data.get("username"),
        "full_name": data.get("full_name"),
        "subject": data.get("subject") or "—",
        "message": data.get("message") or "—",
    }
    resp, err = await post_ticket(payload)
    await clear_fsm_state(user_id)
    if err:
        await bot.send_message(chat_id=chat_id, text=err)
    else:
        await bot.send_message(chat_id=chat_id, text="Тикет создан. Мы ответим в ближайшее время.")
        txt = f"Ticket\nUser: {user_id} @{data.get('username') or '—'}\nSubject: {data.get('subject')}\nMessage: {data.get('message')}"
        await _notify_admin(txt)
    await _go_home(chat_id, user_id)
