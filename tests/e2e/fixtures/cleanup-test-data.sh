#!/bin/bash
# Cleanup E2E test data from database
# Usage: ./cleanup-test-data.sh

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Database configuration (matches Docker setup)
DB_NAME="netrunner"
DB_USER="root"
DB_PASS="rootsecret"

# Check if running via Docker or directly
if command -v docker &> /dev/null && docker compose ps --services 2>/dev/null | grep -q mysql; then
    echo "Cleaning up E2E test data via Docker..."
    docker compose exec -T mysql mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SCRIPT_DIR/cleanup-test-data.sql"
else
    echo "Cleaning up E2E test data via local MySQL..."
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SCRIPT_DIR/cleanup-test-data.sql"
fi

echo "Done. Test data with [E2E_TEST] prefix has been removed."
