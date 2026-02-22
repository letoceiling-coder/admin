"""Configuration from environment."""
import os

TELEGRAM_BOT_TOKEN = os.environ.get("TELEGRAM_BOT_TOKEN", "")
CRM_API_BASE_URL = (os.environ.get("CRM_API_BASE_URL") or "").rstrip("/")
CRM_BOT_API_TOKEN = os.environ.get("CRM_BOT_API_TOKEN", "")
REDIS_URL = os.environ.get("REDIS_URL", "redis://127.0.0.1:6379/0")
LOG_LEVEL = os.environ.get("LOG_LEVEL", "INFO").upper()
MODE = os.environ.get("MODE", "polling").lower()
HEALTH_PORT = int(os.environ.get("HEALTH_PORT", "8088"))
