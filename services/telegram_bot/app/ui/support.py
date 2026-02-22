"""Support: FAQ list, Create ticket, Call request."""
import logging
from aiogram import Router, F
from aiogram.types import CallbackQuery, InlineKeyboardMarkup

from app.bot_instance import bot
from app.http import get_faq, post_event
from app.storage import rate_limit_allow
from app.ui.helpers import delete_temp_messages, btn

router = Router(name="support")
logger = logging.getLogger(__name__)


def _back():
    return InlineKeyboardMarkup(inline_keyboard=[[btn("◀ Назад", "screen:home")]])


async def show_support_screen(chat_id: int, user_id: int, message_id: int) -> bool:
    """Показать экран «Поддержка» в сообщении message_id (для Reply-кнопок). Возвращает True при успехе."""
    if not await rate_limit_allow(user_id):
        return False
    await delete_temp_messages(bot, chat_id, user_id)
    faq, err = await get_faq()
    if err:
        await bot.edit_message_text(chat_id=chat_id, message_id=message_id, text=err, reply_markup=_back())
        return True
    await post_event(user_id, "screen_view", {"screen": "support"})
    lines = ["🆘 Поддержка\n\nFAQ:"]
    for f in (faq or [])[:5]:
        lines.append(f"• {f.get('question')}: {f.get('answer', '')[:60]}...")
    rows = [
        [btn("📞 Заказать звонок", "flow:call")],
        [btn("❓ Создать тикет", "flow:ticket")],
        [btn("◀ Назад", "screen:home")],
    ]
    await bot.edit_message_text(
        chat_id=chat_id, message_id=message_id,
        text="\n".join(lines) or "Поддержка",
        reply_markup=InlineKeyboardMarkup(inline_keyboard=rows),
    )
    return True


@router.callback_query(F.data == "screen:support")
async def cb_support(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    chat_id = cq.message.chat.id if cq.message else 0
    if not await rate_limit_allow(user_id):
        await cq.answer("Подождите минуту.", show_alert=True)
        return
    await show_support_screen(chat_id, user_id, cq.message.message_id)  # type: ignore


@router.callback_query(F.data == "flow:call")
async def cb_start_call(cq: CallbackQuery):
    from app.flows.call_request import start_call_flow
    await start_call_flow(cq)


@router.callback_query(F.data == "flow:ticket")
async def cb_start_ticket(cq: CallbackQuery):
    from app.flows.ticket import start_ticket_flow
    await start_ticket_flow(cq)
