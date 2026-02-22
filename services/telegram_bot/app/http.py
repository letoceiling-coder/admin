"""CRM API client (Bearer token, retry on 5xx)."""
import asyncio
import logging
from typing import Any, Optional

import aiohttp

from app.config import CRM_API_BASE_URL, CRM_BOT_API_TOKEN

logger = logging.getLogger(__name__)
BASE = f"{CRM_API_BASE_URL}/api/telegram"
HEADERS = {
    "Authorization": f"Bearer {CRM_BOT_API_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
}


async def _request(method: str, path: str, json_body=None, params=None, retries: int = 3):
    url = BASE + path
    last_err = None
    for attempt in range(retries):
        try:
            async with aiohttp.ClientSession() as session:
                async with session.request(
                    method, url, headers=HEADERS, json=json_body or None, params=params,
                    timeout=aiohttp.ClientTimeout(total=15),
                ) as resp:
                    if resp.status in (401, 403):
                        logger.warning("crm_api_auth_error status=%s path=%s", resp.status, path)
                        return None, "Сервис временно недоступен. Попробуйте позже."
                    if resp.status >= 500:
                        last_err = "status=" + str(resp.status)
                        if attempt < retries - 1:
                            await asyncio.sleep(0.5 * (attempt + 1))
                        continue
                    if resp.status >= 400:
                        text = await resp.text()
                        logger.warning("crm_api_4xx status=%s path=%s", resp.status, path)
                        return None, "Ошибка данных. Попробуйте позже."
                    if resp.content_type and "application/json" in resp.content_type:
                        data = await resp.json()
                    else:
                        data = None
                    return data, None
        except asyncio.TimeoutError:
            last_err = "timeout"
            if attempt < retries - 1:
                await asyncio.sleep(0.5 * (attempt + 1))
        except Exception as e:
            logger.exception("crm_api_request_error path=%s", path)
            last_err = str(e)
            if attempt < retries - 1:
                await asyncio.sleep(0.5 * (attempt + 1))
    return None, last_err or "Сервис временно недоступен."


async def get_settings():
    return await _request("GET", "/settings")


async def get_service_categories():
    r, err = await _request("GET", "/services/categories")
    if err:
        return None, err
    return (r.get("data") if isinstance(r, dict) else None), None


async def get_services(category_id=None):
    params = {"category_id": category_id} if category_id else None
    r, err = await _request("GET", "/services", params=params)
    if err:
        return None, err
    return (r.get("data") if isinstance(r, dict) else None), None


async def get_cases(tag=None, page=1):
    params = {"page": page, "per_page": 10}
    if tag:
        params["tag"] = tag
    r, err = await _request("GET", "/cases", params=params)
    if err:
        return None, err
    return r, None


async def get_case(case_id: int):
    r, err = await _request("GET", f"/cases/{case_id}")
    if err:
        return None, err
    return (r.get("data") if isinstance(r, dict) else None), None


async def get_reviews(page=1):
    r, err = await _request("GET", "/reviews", params={"page": page, "per_page": 10})
    if err:
        return None, err
    return r, None


async def get_faq():
    r, err = await _request("GET", "/faq")
    if err:
        return None, err
    return (r.get("data") if isinstance(r, dict) else None), None


async def post_lead(payload: dict):
    return await _request("POST", "/leads", json_body=payload)


async def post_call_request(payload: dict):
    return await _request("POST", "/call-requests", json_body=payload)


async def post_ticket(payload: dict):
    return await _request("POST", "/tickets", json_body=payload)


async def post_review(payload: dict):
    return await _request("POST", "/reviews", json_body=payload)


async def post_event(tg_user_id: int, event_name: str, payload_json=None):
    await _request("POST", "/events", json_body={
        "tg_user_id": tg_user_id,
        "event_name": event_name,
        "payload_json": payload_json or {},
    })


async def ping_settings():
    r, err = await get_settings()
    return err is None
