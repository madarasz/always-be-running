#!/bin/bash
# Cleanup E2E test data from database and test artifacts
# Usage: ./cleanup-test-data.sh

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Database configuration (matches Docker setup)
DB_NAME="netrunner"
DB_USER="root"
DB_PASS="rootsecret"

# Clean up auth state files
AUTH_DIR="$SCRIPT_DIR/../.auth"
if [ -d "$AUTH_DIR" ]; then
    echo "Cleaning up auth state files..."
    find "$AUTH_DIR" -type f -delete
fi

# Clean up test artifacts (screenshots and traces)
TEST_RESULTS_DIR="$SCRIPT_DIR/../test-results"
if [ -d "$TEST_RESULTS_DIR" ]; then
    echo "Cleaning up test artifacts..."
    rm -f "$TEST_RESULTS_DIR"/*.png "$TEST_RESULTS_DIR"/*.zip
fi

# Check if running via Docker or directly
if command -v docker &> /dev/null && docker compose ps --services 2>/dev/null | grep -q mysql; then
    echo "Cleaning up E2E test data via Docker..."
    docker compose exec -T mysql mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SCRIPT_DIR/cleanup-test-data.sql"
else
    echo "Cleaning up E2E test data via local MySQL..."
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SCRIPT_DIR/cleanup-test-data.sql"
fi

echo "Done. Test data with [E2E_TEST] prefix has been removed."
