"""Call request flow: phone, time, comment -> POST call-requests, notify, HOME."""
import logging
from aiogram import Router, F
from aiogram.types import CallbackQuery, Message, InlineKeyboardMarkup

from app.bot import bot
from app.http import post_call_request
from app.storage import get_fsm_state, set_fsm_state, clear_fsm_state, get_hero_message_id
from app.ui.helpers import btn
from app.flows.lead import _notify_admin, _go_home

router = Router(name="call")


async def start_call_flow(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    username = (cq.from_user.username or "").strip() or None if cq.from_user else None
    await set_fsm_state(user_id, "call:phone", {"username": username})
    await cq.message.edit_text("Введите телефон:", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[btn("◀ Отмена", "screen:home")]]))


@router.message(F.text, F.text.len() <= 100)
async def msg_call(message: Message):
    user_id = message.from_user.id if message.from_user else 0
    chat_id = message.chat.id
    st = await get_fsm_state(user_id)
    if not st or not st.get("state", "").startswith("call:"):
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
    if state == "call:phone":
        data["phone"] = (message.text or "").strip()[:100]
        await set_fsm_state(user_id, "call:time", data)
        await bot.edit_message_text(chat_id=chat_id, message_id=mid, text="Удобное время (или нажмите Пропустить):", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[btn("Пропустить", "flow:call:skip_time"), btn("◀ Отмена", "screen:home")]]))
        return
    if state == "call:time":
        data["preferred_time"] = (message.text or "").strip()[:200]
        await set_fsm_state(user_id, "call:comment", data)
        await bot.edit_message_text(chat_id=chat_id, message_id=mid, text="Комментарий (или Пропустить):", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[btn("Пропустить", "flow:call:skip_comment"), btn("◀ Отмена", "screen:home")]]))
        return
    if state == "call:comment":
        data["comment"] = (message.text or "").strip()[:500]
        await _submit_call(user_id, chat_id, data)


@router.callback_query(F.data == "flow:call:skip_time")
async def cb_skip_time(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    chat_id = cq.message.chat.id if cq.message else 0
    st = await get_fsm_state(user_id)
    if not st or st.get("state") != "call:time":
        return
    data = st.get("data") or {}
    await set_fsm_state(user_id, "call:comment", data)
    await cq.message.edit_text("Комментарий (или Пропустить):", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[btn("Пропустить", "flow:call:skip_comment"), btn("◀ Отмена", "screen:home")]]))


@router.callback_query(F.data == "flow:call:skip_comment")
async def cb_skip_comment(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    chat_id = cq.message.chat.id if cq.message else 0
    st = await get_fsm_state(user_id)
    if not st:
        return
    await _submit_call(user_id, chat_id, st.get("data") or {})


async def _submit_call(user_id: int, chat_id: int, data: dict):
    payload = {"tg_user_id": user_id, "username": data.get("username"), "full_name": None, "phone": data.get("phone") or "n/a", "preferred_time": data.get("preferred_time"), "comment": data.get("comment")}
    resp, err = await post_call_request(payload)
    await clear_fsm_state(user_id)
    if err:
        await bot.send_message(chat_id=chat_id, text=err)
    else:
        await bot.send_message(chat_id=chat_id, text="Заявка на звонок принята.")
        await _notify_admin(f"Call request\nUser: {user_id}\nPhone: {data.get('phone')}\nTime: {data.get('preferred_time')}\nComment: {data.get('comment')}")
    await _go_home(chat_id, user_id)
