"""Helpers: edit or send hero, delete temp messages, go home."""
import logging
from typing import Optional, List

from aiogram import Bot
from aiogram.types import InlineKeyboardMarkup, InlineKeyboardButton, Message

from app.storage import get_hero_message_id, set_hero_message_id, get_temp_message_ids, clear_temp_message_ids

logger = logging.getLogger(__name__)


async def edit_or_send_hero(
    bot: Bot,
    chat_id: int,
    user_id: int,
    text: str,
    reply_markup: Optional[InlineKeyboardMarkup] = None,
    photo_file_id: Optional[str] = None,
) -> int:
    """Edit existing hero or send new. If photo_file_id and no hero yet, send photo; else text. Returns hero message_id."""
    mid = await get_hero_message_id(user_id)
    try:
        if mid:
            await bot.edit_message_text(chat_id=chat_id, message_id=mid, text=text, reply_markup=reply_markup)
            return mid
    except Exception as e:
        logger.debug("edit_hero_failed %s", e)
    if photo_file_id:
        m = await bot.send_photo(chat_id=chat_id, photo=photo_file_id, caption=text, reply_markup=reply_markup)
    else:
        m = await bot.send_message(chat_id=chat_id, text=text, reply_markup=reply_markup)
    await set_hero_message_id(user_id, m.message_id)
    return m.message_id


async def delete_temp_messages(bot: Bot, chat_id: int, user_id: int) -> None:
    ids = await get_temp_message_ids(user_id)
    for mid in ids:
        try:
            await bot.delete_message(chat_id=chat_id, message_id=mid)
        except Exception as e:
            logger.debug("delete_temp %s %s", mid, e)
    await clear_temp_message_ids(user_id)


def btn(text: str, callback_data: str) -> InlineKeyboardButton:
    return InlineKeyboardButton(text=text, callback_data=callback_data)
