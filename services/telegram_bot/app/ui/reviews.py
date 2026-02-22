"""Reviews: approved list, CTA leave review."""
import logging
from aiogram import Router, F
from aiogram.types import CallbackQuery, InlineKeyboardMarkup

from app.bot import bot
from app.http import get_reviews, post_event
from app.storage import rate_limit_allow, can_submit_review
from app.ui.helpers import delete_temp_messages, btn

router = Router(name="reviews")


def _back():
    return InlineKeyboardMarkup(inline_keyboard=[[btn("◀ Назад", "screen:home")]])


@router.callback_query(F.data == "screen:reviews")
async def cb_reviews(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    if not await rate_limit_allow(user_id):
        await cq.answer("Подождите минуту.", show_alert=True)
        return
    await delete_temp_messages(bot, cq.message.chat.id, user_id)
    r, err = await get_reviews()
    if err:
        await cq.message.edit_text(err, reply_markup=_back())
        return
    items = (r.get("data") if isinstance(r, dict) else []) or []
    await post_event(user_id, "screen_view", {"screen": "reviews"})
    lines = ["⭐ Отзывы:\n"]
    for rev in items[:10]:
        lines.append(f"• {rev.get('author_name') or '—'} ({rev.get('rating')}): {(rev.get('text') or '')[:80]}")
    rows = []
    if await can_submit_review(user_id):
        rows.append([btn("✍ Оставить отзыв", "flow:review")])
    rows.append([btn("◀ Назад", "screen:home")])
    await cq.message.edit_text("\n".join(lines) or "Нет отзывов.", reply_markup=InlineKeyboardMarkup(inline_keyboard=rows))


@router.callback_query(F.data == "flow:review")
async def cb_start_review(cq: CallbackQuery):
    from app.flows.review import start_review_flow
    await start_review_flow(cq)
