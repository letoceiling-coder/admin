"""Review flow: rating 1-5, text. Anti-spam 1/24h. POST reviews (pending), notify, HOME."""
import logging
from aiogram import Router, F
from aiogram.types import CallbackQuery, Message, InlineKeyboardMarkup

from app.bot import bot
from app.http import post_review
from app.storage import get_fsm_state, set_fsm_state, clear_fsm_state, get_hero_message_id, can_submit_review, set_review_submitted
from app.ui.helpers import btn
from app.flows.lead import _notify_admin, _go_home

router = Router(name="review")


async def start_review_flow(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    if not await can_submit_review(user_id):
        await cq.answer("Один отзыв раз в 24 часа.", show_alert=True)
        return
    username = (cq.from_user.username or "").strip() or None if cq.from_user else None
    name = (cq.from_user.full_name or "").strip() if cq.from_user else ""
    await set_fsm_state(user_id, "review:rating", {"username": username, "author_name": name})
    kbd = InlineKeyboardMarkup(inline_keyboard=[
        [btn("1", "flow:review:r:1"), btn("2", "flow:review:r:2"), btn("3", "flow:review:r:3"), btn("4", "flow:review:r:4"), btn("5", "flow:review:r:5")],
        [btn("◀ Отмена", "screen:home")],
    ])
    await cq.message.edit_text("Оцените от 1 до 5:", reply_markup=kbd)


@router.callback_query(F.data.startswith("flow:review:r:"))
async def cb_rating(cq: CallbackQuery):
    await cq.answer()
    try:
        rating = int(cq.data.split(":")[-1])
    except (IndexError, ValueError):
        return
    if rating < 1 or rating > 5:
        return
    user_id = cq.from_user.id if cq.from_user else 0
    st = await get_fsm_state(user_id)
    if not st or st.get("state") != "review:rating":
        return
    data = st.get("data") or {}
    data["rating"] = rating
    await set_fsm_state(user_id, "review:text", data)
    await cq.message.edit_text("Напишите отзыв текстом:", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[btn("◀ Отмена", "screen:home")]]))


@router.message(F.text, F.text.len() <= 2000)
async def msg_review_text(message: Message):
    user_id = message.from_user.id if message.from_user else 0
    chat_id = message.chat.id
    st = await get_fsm_state(user_id)
    if not st or st.get("state") != "review:text":
        return
    data = st.get("data") or {}
    data["text"] = (message.text or "").strip()[:5000]
    try:
        await message.delete()
    except Exception:
        pass
    await _submit_review(user_id, chat_id, data)


async def _submit_review(user_id: int, chat_id: int, data: dict):
    payload = {
        "tg_user_id": user_id,
        "username": data.get("username"),
        "full_name": data.get("author_name"),
        "rating": data.get("rating", 5),
        "text": data.get("text") or "—",
    }
    resp, err = await post_review(payload)
    await set_review_submitted(user_id)
    await clear_fsm_state(user_id)
    if err:
        await bot.send_message(chat_id=chat_id, text=err)
    else:
        await bot.send_message(chat_id=chat_id, text="Спасибо! Отзыв на модерации.")
        txt = f"Review (pending)\nUser: {user_id} @{data.get('username') or '—'}\nRating: {data.get('rating')}\nText: {data.get('text')}"
        await _notify_admin(txt)
    await _go_home(chat_id, user_id)
