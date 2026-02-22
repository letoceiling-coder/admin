"""Bot setup: router, handlers, polling."""
import logging
from aiogram import Bot, Dispatcher
from aiogram.client.default import DefaultBotProperties
from aiogram.enums import ParseMode

from app.config import TELEGRAM_BOT_TOKEN
from app.ui.home import router as home_router
from app.ui.services import router as services_router
from app.ui.cases import router as cases_router
from app.ui.reviews import router as reviews_router
from app.ui.support import router as support_router
from app.flows.lead import router as lead_router
from app.flows.call_request import router as call_router
from app.flows.ticket import router as ticket_router
from app.flows.review import router as review_router

logger = logging.getLogger(__name__)

bot = Bot(token=TELEGRAM_BOT_TOKEN, default=DefaultBotProperties(parse_mode=ParseMode.HTML))
dp = Dispatcher()
dp.include_router(home_router, name="home")
dp.include_router(services_router, name="services")
dp.include_router(cases_router, name="cases")
dp.include_router(reviews_router, name="reviews")
dp.include_router(support_router, name="support")
dp.include_router(lead_router, name="lead")
dp.include_router(call_router, name="call")
dp.include_router(ticket_router, name="ticket")
dp.include_router(review_router, name="review")


async def run_polling():
    await dp.start_polling(bot)
