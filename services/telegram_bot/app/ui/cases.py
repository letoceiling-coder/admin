"""Cases: list with tag filter, case detail with media, CTA lead with source_case_id."""
import logging
from aiogram import Router, F
from aiogram.types import CallbackQuery, InlineKeyboardMarkup

from app.bot_instance import bot
from app.http import get_cases, get_case, post_event
from app.storage import rate_limit_allow, append_temp_message_id
from app.ui.helpers import delete_temp_messages, btn

router = Router(name="cases")
logger = logging.getLogger(__name__)


def _back():
    return InlineKeyboardMarkup(inline_keyboard=[[btn("◀ Назад", "screen:home")]])


async def show_cases_screen(chat_id: int, user_id: int, message_id: int) -> bool:
    """Показать экран «Кейсы» в сообщении message_id (для Reply-кнопок). Возвращает True при успехе."""
    if not await rate_limit_allow(user_id):
        return False
    await delete_temp_messages(bot, chat_id, user_id)
    r, err = await get_cases()
    if err:
        await bot.edit_message_text(chat_id=chat_id, message_id=message_id, text=err, reply_markup=_back())
        return True
    items = (r.get("data") if isinstance(r, dict) else []) or []
    await post_event(user_id, "screen_view", {"screen": "cases"})
    if not items:
        await bot.edit_message_text(chat_id=chat_id, message_id=message_id, text="Нет кейсов.", reply_markup=_back())
        return True
    rows = []
    for c in items[:10]:
        rows.append([btn(c.get("title", "—"), f"case:{c.get('id')}")])
    rows.append([btn("◀ Назад", "screen:home")])
    await bot.edit_message_text(
        chat_id=chat_id, message_id=message_id,
        text="💼 Кейсы. Выберите:",
        reply_markup=InlineKeyboardMarkup(inline_keyboard=rows),
    )
    return True


@router.callback_query(F.data == "screen:cases")
async def cb_cases(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    chat_id = cq.message.chat.id if cq.message else 0
    if not await rate_limit_allow(user_id):
        await cq.answer("Подождите минуту.", show_alert=True)
        return
    await show_cases_screen(chat_id, user_id, cq.message.message_id)  # type: ignore


@router.callback_query(F.data.startswith("case:"))
async def cb_case_detail(cq: CallbackQuery):
    await cq.answer()
    try:
        case_id = int(cq.data.split(":")[1])
    except (IndexError, ValueError):
        return
    user_id = cq.from_user.id if cq.from_user else 0
    chat_id = cq.message.chat.id if cq.message else 0
    case, err = await get_case(case_id)
    if err or not case:
        await cq.answer("Не найдено.", show_alert=True)
        return
    text = f"<b>{case.get('title', '—')}</b>\n\nЗадача: {case.get('task') or '—'}\nРешение: {case.get('solution') or '—'}\nРезультат: {case.get('result') or '—'}"
    media = case.get("media") or []
    if media:
        from aiogram.types import InputMediaPhoto
        try:
            photos = [InputMediaPhoto(media=m.get("file_id")) for m in media[:10] if m.get("file_id")]
            if photos:
                msgs = await bot.send_media_group(chat_id=chat_id, media=photos)
                for m in msgs:
                    await append_temp_message_id(user_id, m.message_id)
        except Exception as e:
            logger.warning("send_case_media %s", e)
    kbd = InlineKeyboardMarkup(inline_keyboard=[
        [btn("🧩 Хочу так же", f"case_lead:{case_id}")],
        [btn("◀ К кейсам", "screen:cases"), btn("◀ Назад", "screen:home")],
    ])
    await cq.message.edit_text(text, reply_markup=kbd)  # type: ignore


@router.callback_query(F.data.startswith("case_lead:"))
async def cb_case_lead(cq: CallbackQuery):
    await cq.answer()
    try:
        case_id = int(cq.data.split(":")[1])
    except (IndexError, ValueError):
        return
    from app.flows.lead import start_lead_flow
    await start_lead_flow(cq, source_case_id=case_id)
