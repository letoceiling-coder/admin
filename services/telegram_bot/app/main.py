"""Entrypoint: setup logging, then polling or webhook."""
import asyncio
import logging
import sys
from pathlib import Path

# project root = services/telegram_bot (parent of app/)
_root = Path(__file__).resolve().parent.parent
if str(_root) not in sys.path:
    sys.path.insert(0, str(_root))

from app.config import TELEGRAM_BOT_TOKEN, MODE
from app.logging import setup_logging

setup_logging()
logger = logging.getLogger(__name__)


async def main():
    if not TELEGRAM_BOT_TOKEN:
        logger.error("TELEGRAM_BOT_TOKEN is not set")
        sys.exit(1)
    if MODE == "webhook":
        from app.bot_instance import bot, dp
        import app.bot  # register routers on dp
        from app.webhook_server import run_webhook
        await run_webhook(bot, dp)
    else:
        from app.health import start_health_server
        await start_health_server()
        from app.bot import run_polling
        await run_polling()


if __name__ == "__main__":
    asyncio.run(main())
