"""Reviews: approved list, CTA leave review."""
import logging
from aiogram import Router, F
from aiogram.types import CallbackQuery, InlineKeyboardMarkup

from app.bot_instance import bot
from app.http import get_reviews, post_event
from app.storage import rate_limit_allow, can_submit_review
from app.ui.helpers import delete_temp_messages, btn

router = Router(name="reviews")


def _back():
    return InlineKeyboardMarkup(inline_keyboard=[[btn("◀ Назад", "screen:home")]])


async def show_reviews_screen(chat_id: int, user_id: int, message_id: int) -> bool:
    """Показать экран «Отзывы» в сообщении message_id (для Reply-кнопок). Возвращает True при успехе."""
    if not await rate_limit_allow(user_id):
        return False
    await delete_temp_messages(bot, chat_id, user_id)
    r, err = await get_reviews()
    if err:
        await bot.edit_message_text(chat_id=chat_id, message_id=message_id, text=err, reply_markup=_back())
        return True
    items = (r.get("data") if isinstance(r, dict) else []) or []
    await post_event(user_id, "screen_view", {"screen": "reviews"})
    lines = ["⭐ Отзывы:\n"]
    for rev in items[:10]:
        lines.append(f"• {rev.get('author_name') or '—'} ({rev.get('rating')}): {(rev.get('text') or '')[:80]}")
    rows = []
    if await can_submit_review(user_id):
        rows.append([btn("✍ Оставить отзыв", "flow:review")])
    rows.append([btn("◀ Назад", "screen:home")])
    await bot.edit_message_text(
        chat_id=chat_id, message_id=message_id,
        text="\n".join(lines) or "Нет отзывов.",
        reply_markup=InlineKeyboardMarkup(inline_keyboard=rows),
    )
    return True


@router.callback_query(F.data == "screen:reviews")
async def cb_reviews(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    chat_id = cq.message.chat.id if cq.message else 0
    if not await rate_limit_allow(user_id):
        await cq.answer("Подождите минуту.", show_alert=True)
        return
    await show_reviews_screen(chat_id, user_id, cq.message.message_id)  # type: ignore


@router.callback_query(F.data == "flow:review")
async def cb_start_review(cq: CallbackQuery):
    from app.flows.review import start_review_flow
    await start_review_flow(cq)
