"""Bot setup: router, handlers, polling."""
import logging

from app.bot_instance import bot, dp
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

dp.include_router(home_router)
dp.include_router(services_router)
dp.include_router(cases_router)
dp.include_router(reviews_router)
dp.include_router(support_router)
dp.include_router(lead_router)
dp.include_router(call_router)
dp.include_router(ticket_router)
dp.include_router(review_router)


async def run_polling():
    await dp.start_polling(bot)
