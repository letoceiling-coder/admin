"""Webhook HTTP server: /health and Telegram webhook path. Used when MODE=webhook."""
import asyncio
import logging
from aiohttp import web

from app.config import HEALTH_PORT, WEBHOOK_PATH, WEBHOOK_BASE_URL, WEBHOOK_SECRET
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


def create_webhook_app(bot, dp):
    """Create aiohttp app with /health and webhook route. Call setup_application and set_webhook externally."""
    from aiogram.webhook.aiohttp_server import SimpleRequestHandler, setup_application

    app = web.Application()
    app["bot"] = bot
    app.router.add_get("/health", health_handler)

    SimpleRequestHandler(
        dispatcher=dp,
        bot=bot,
        secret_token=WEBHOOK_SECRET,
    ).register(app, path=WEBHOOK_PATH)

    setup_application(app, dp, bot=bot)
    return app


async def run_webhook(bot, dp):
    """Set webhook URL and run aiohttp app (blocks)."""
    from aiohttp import web
    from app.config import WEBHOOK_BASE_URL

    if not WEBHOOK_BASE_URL:
        raise RuntimeError("WEBHOOK_BASE_URL is required for MODE=webhook")

    url = f"{WEBHOOK_BASE_URL.rstrip('/')}{WEBHOOK_PATH}"
    logger.info("webhook_set url=%s", url)
    await bot.set_webhook(url, secret_token=WEBHOOK_SECRET)

    app = create_webhook_app(bot, dp)
    runner = web.AppRunner(app)
    await runner.setup()
    site = web.TCPSite(runner, "0.0.0.0", HEALTH_PORT)
    await site.start()
    logger.info("webhook_listen port=%s path=%s", HEALTH_PORT, WEBHOOK_PATH)
    try:
        while True:
            await asyncio.sleep(3600)
    except asyncio.CancelledError:
        pass
    finally:
        await bot.delete_webhook()
        await runner.cleanup()
