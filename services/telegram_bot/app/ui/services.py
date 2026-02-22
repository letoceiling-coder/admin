"""Services: categories -> list with pagination, CTA lead with source_service_id."""
import logging
from aiogram import Router, F
from aiogram.types import CallbackQuery, InlineKeyboardMarkup

from app.bot_instance import bot
from app.http import get_service_categories, get_services, post_event
from app.storage import rate_limit_allow
from app.ui.helpers import delete_temp_messages, btn

router = Router(name="services")
logger = logging.getLogger(__name__)


def _back_kbd(extra: list = None):
    row = [btn("◀ Назад", "screen:home")]
    if extra:
        row = extra + row
    return InlineKeyboardMarkup(inline_keyboard=[row])


async def show_services_screen(chat_id: int, user_id: int, message_id: int) -> bool:
    """Показать экран «Услуги» в сообщении message_id (для Reply-кнопок). Возвращает True при успехе."""
    if not await rate_limit_allow(user_id):
        return False
    await delete_temp_messages(bot, chat_id, user_id)
    cats, err = await get_service_categories()
    if err:
        await bot.edit_message_text(chat_id=chat_id, message_id=message_id, text=err, reply_markup=_back_kbd())
        return True
    await post_event(user_id, "screen_view", {"screen": "services"})
    if not cats:
        await bot.edit_message_text(chat_id=chat_id, message_id=message_id, text="Нет категорий.", reply_markup=_back_kbd())
        return True
    rows = []
    for c in cats:
        rows.append([btn(c.get("name", "—"), f"svc_cat:{c.get('id')}")])
    rows.append([btn("◀ Назад", "screen:home")])
    await bot.edit_message_text(
        chat_id=chat_id, message_id=message_id,
        text="🧩 Услуги. Выберите категорию:",
        reply_markup=InlineKeyboardMarkup(inline_keyboard=rows),
    )
    return True


@router.callback_query(F.data == "screen:services")
async def cb_services(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    chat_id = cq.message.chat.id if cq.message else 0
    if not await rate_limit_allow(user_id):
        await cq.answer("Подождите минуту.", show_alert=True)
        return
    await show_services_screen(chat_id, user_id, cq.message.message_id)  # type: ignore


@router.callback_query(F.data.startswith("svc_cat:"))
async def cb_svc_cat(cq: CallbackQuery):
    await cq.answer()
    user_id = cq.from_user.id if cq.from_user else 0
    chat_id = cq.message.chat.id if cq.message else 0
    try:
        cat_id = int(cq.data.split(":")[1])
    except (IndexError, ValueError):
        return
    await post_event(user_id, "screen_view", {"screen": "services", "category_id": cat_id})
    items, err = await get_services(category_id=cat_id)
    if err:
        await cq.message.edit_text(err, reply_markup=_back_kbd())  # type: ignore
        return
    if not items:
        await cq.message.edit_text("Нет услуг в этой категории.", reply_markup=_back_kbd())  # type: ignore
        return
    page = 0
    per_page = 5
    chunk = items[page * per_page:(page + 1) * per_page]
    lines = ["Услуги:\n"]
    for s in chunk:
        lines.append(f"• {s.get('name', '—')}")
    rows = []
    for s in chunk:
        rows.append([btn(s.get("name", "—"), f"svc_lead:{s.get('id')}:{cat_id}")])
    rows.append([btn("◀ К категориям", "screen:services"), btn("◀ Назад", "screen:home")])
    await cq.message.edit_text("\n".join(lines) + "\n\nОставить заявку по услуге:", reply_markup=InlineKeyboardMarkup(inline_keyboard=rows))  # type: ignore


@router.callback_query(F.data.startswith("svc_lead:"))
async def cb_svc_lead(cq: CallbackQuery):
    await cq.answer()
    parts = cq.data.split(":")
    if len(parts) < 3:
        return
    try:
        service_id, _ = int(parts[1]), int(parts[2])
    except ValueError:
        return
    from app.flows.lead import start_lead_flow
    await start_lead_flow(cq, source_service_id=service_id)
