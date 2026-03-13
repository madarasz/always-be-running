#!/usr/bin/env bash
set -euo pipefail

# Keep selected fixture tournaments in the future so "upcoming" UI/API tests remain stable.
# DB_* vars can override defaults for local runs.
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3307}"
DB_USER="${DB_USER:-root}"
DB_PASSWORD="${DB_PASSWORD:-rootsecret}"
DB_NAME="${DB_NAME:-netrunner}"

date_plus_days() {
  local days="$1"

  if date -d "+${days} days" +%Y.%m.%d. >/dev/null 2>&1; then
    date -d "+${days} days" +%Y.%m.%d.
  else
    date -v+"${days}"d +%Y.%m.%d.
  fi
}

FUTURE_DATE="$(date_plus_days 30)"
NEXT_WEEK="$(date_plus_days 7)"
NEXT_MONTH="$(date_plus_days 60)"

mysql \
  -h "${DB_HOST}" \
  -P "${DB_PORT}" \
  -u "${DB_USER}" \
  -p"${DB_PASSWORD}" \
  "${DB_NAME}" \
  -e "
    UPDATE tournaments SET date = '${FUTURE_DATE}' WHERE id IN (886, 2850, 3264);
    UPDATE tournaments SET date = '${NEXT_WEEK}' WHERE id IN (5313, 5354, 5400, 5500);
    UPDATE tournaments SET date = '${NEXT_MONTH}' WHERE id IN (5600, 5700, 5800);
    UPDATE tournaments SET featured = 1 WHERE concluded = 1 AND approved = 1 LIMIT 3;
  "

echo "Updated tournament fixture dates for E2E tests."
