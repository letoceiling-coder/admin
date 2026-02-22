#!/usr/bin/env bash
# Smoke-проверка API /api/telegram/* для бота.
# Использование:
#   BASE_URL=https://admin.neeklo.ru CRM_BOT_API_TOKEN=xxx ./tools/smoke/telegram_api_smoke.sh
#   или: ./tools/smoke/telegram_api_smoke.sh https://admin.neeklo.ru your_token
set -e

BASE_URL="${BASE_URL:-$1}"
TOKEN="${CRM_BOT_API_TOKEN:-$2}"

if [ -z "$BASE_URL" ] || [ -z "$TOKEN" ]; then
  echo "Usage: BASE_URL=... CRM_BOT_API_TOKEN=... $0"
  echo "   or: $0 <BASE_URL> <CRM_BOT_API_TOKEN>"
  exit 1
fi

BASE_URL="${BASE_URL%/}"
API="$BASE_URL/api/telegram"
FAIL=0

check() {
  local name="$1"
  local method="$2"
  local url="$3"
  local data="$4"
  local code
  if [ "$method" = "GET" ]; then
    code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$url" -H "Authorization: Bearer $TOKEN")
  else
    code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$url" -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" -d "$data")
  fi
  if [ "$code" = "200" ] || [ "$code" = "201" ]; then
    echo "OK   $name ($code)"
  else
    echo "FAIL $name (HTTP $code)"
    FAIL=1
  fi
}

# Без токена — 401
code_no=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$API/settings")
if [ "$code_no" = "401" ]; then
  echo "OK   GET /settings without token → 401"
else
  echo "FAIL GET /settings without token (expected 401, got $code_no)"
  FAIL=1
fi

# С токеном — ключевые GET
check "GET /settings"          GET "$API/settings"
check "GET /services/categories" GET "$API/services/categories"
check "GET /services"          GET "$API/services"
check "GET /cases"             GET "$API/cases"

# POST events
check "POST /events" POST "$API/events" '{"tg_user_id":1,"event_name":"screen_view","payload_json":{"screen":"smoke"}}'

if [ $FAIL -eq 0 ]; then
  echo "---"
  echo "Smoke: all checks passed."
  exit 0
else
  echo "---"
  echo "Smoke: one or more checks failed."
  exit 1
fi
