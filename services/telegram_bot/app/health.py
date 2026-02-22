"""Health HTTP server: GET /health -> {status: ok}. Checks Redis and optionally CRM."""
import asyncio
import logging
from aiohttp import web

from app.config import HEALTH_PORT
from app.storage import get_redis
from app.http import ping_settings

logger = logging.getLogger(__name__)


async def health_handler(request: web.Request) -> web.Response:
    status = "ok"
    code = 200
    try:
        r = await get_redis()
        await r.ping()
    except Exception as e:
        logger.warning("health_redis_error %s", e)
        status = "redis_error"
        code = 503
    try:
        if not await ping_settings():
            status = status if code != 200 else "crm_unavailable"
    except Exception as e:
        logger.warning("health_crm_error %s", e)
        if code == 200:
            status = "crm_error"
    return web.json_response({"status": status}, status=code)


def run_health_app(port: int = None):
    port = port or HEALTH_PORT
    app = web.Application()
    app.router.add_get("/health", health_handler)

    async def on_start(app):
        logger.info("health_server_started port=%s", port)

    app.on_startup.append(on_start)
    return app, port


async def start_health_server():
    app, port = run_health_app()
    runner = web.AppRunner(app)
    await runner.setup()
    site = web.TCPSite(runner, "0.0.0.0", port)
    await site.start()
    logger.info("health_listen port=%s", port)
    return runner
