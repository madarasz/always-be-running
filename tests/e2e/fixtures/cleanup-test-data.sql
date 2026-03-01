-- Cleanup script for E2E test data
-- Removes tournaments created by automated tests (identified by [E2E_TEST] prefix)
-- Run this before/after tests to ensure clean state

-- Delete entries for test tournaments first (foreign key constraint)
DELETE FROM entries WHERE tournament_id IN (
    SELECT id FROM tournaments WHERE title LIKE '[E2E_TEST]%'
);

-- Delete unofficial prizes for test tournaments
DELETE FROM tournament_prizes WHERE tournament_id IN (
    SELECT id FROM tournaments WHERE title LIKE '[E2E_TEST]%'
);

-- Delete photos for test tournaments
DELETE FROM photos WHERE tournament_id IN (
    SELECT id FROM tournaments WHERE title LIKE '[E2E_TEST]%'
);

-- Delete videos for test tournaments
DELETE FROM videos WHERE tournament_id IN (
    SELECT id FROM tournaments WHERE title LIKE '[E2E_TEST]%'
);

-- Delete the test tournaments (including soft-deleted ones)
DELETE FROM tournaments WHERE title LIKE '[E2E_TEST]%';
