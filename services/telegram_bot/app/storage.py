"""Redis: hero message, FSM state, rate-limit, anti-spam (review 1/24h)."""
import json
import logging
from typing import Any, Optional

import redis.asyncio as redis

from app.config import REDIS_URL

logger = logging.getLogger(__name__)
_redis: Optional[redis.Redis] = None


async def get_redis() -> redis.Redis:
    global _redis
    if _redis is None:
        _redis = redis.from_url(REDIS_URL, decode_responses=True)
    return _redis


def _key_hero(user_id: int) -> str:
    return f"hero:{user_id}"


def _key_temp(user_id: int) -> str:
    return f"temp_msgs:{user_id}"


def _key_fsm(user_id: int) -> str:
    return f"fsm:{user_id}"


def _key_rate(user_id: int) -> str:
    return f"rate:{user_id}"


def _key_last_review(user_id: int) -> str:
    return f"last_review_at:{user_id}"


async def get_hero_message_id(user_id: int) -> Optional[int]:
    r = await get_redis()
    s = await r.get(_key_hero(user_id))
    if s is None:
        return None
    try:
        return int(s)
    except ValueError:
        return None


async def set_hero_message_id(user_id: int, message_id: int) -> None:
    r = await get_redis()
    await r.set(_key_hero(user_id), str(message_id))


async def clear_hero_message_id(user_id: int) -> None:
    r = await get_redis()
    await r.delete(_key_hero(user_id))


async def get_temp_message_ids(user_id: int) -> list[int]:
    r = await get_redis()
    s = await r.get(_key_temp(user_id))
    if not s:
        return []
    try:
        return json.loads(s)
    except (json.JSONDecodeError, TypeError):
        return []


async def append_temp_message_id(user_id: int, message_id: int) -> None:
    r = await get_redis()
    key = _key_temp(user_id)
    ids = await get_temp_message_ids(user_id)
    ids.append(message_id)
    await r.set(key, json.dumps(ids))


async def clear_temp_message_ids(user_id: int) -> None:
    r = await get_redis()
    await r.delete(_key_temp(user_id))


async def get_fsm_state(user_id: int) -> Optional[dict]:
    r = await get_redis()
    s = await r.get(_key_fsm(user_id))
    if not s:
        return None
    try:
        return json.loads(s)
    except (json.JSONDecodeError, TypeError):
        return None


async def set_fsm_state(user_id: int, state: str, data: Optional[dict] = None) -> None:
    r = await get_redis()
    payload = {"state": state, "data": data or {}}
    await r.set(_key_fsm(user_id), json.dumps(payload))


async def clear_fsm_state(user_id: int) -> None:
    r = await get_redis()
    await r.delete(_key_fsm(user_id))


async def rate_limit_allow(user_id: int, limit: int = 30, window_sec: int = 60) -> bool:
    r = await get_redis()
    key = _key_rate(user_id)
    pipe = r.pipeline()
    pipe.incr(key)
    pipe.expire(key, window_sec)
    n, _ = await pipe.execute()
    return n <= limit


async def can_submit_review(user_id: int) -> bool:
    r = await get_redis()
    key = _key_last_review(user_id)
    exists = await r.exists(key)
    return not exists


async def set_review_submitted(user_id: int) -> None:
    r = await get_redis()
    await r.set(_key_last_review(user_id), "1", ex=86400)
