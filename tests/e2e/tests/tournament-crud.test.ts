import { createBrowserSuite, it, expect } from '../helpers/test-fixture';
import { E2E_TEST_PREFIX } from '../pages/TournamentPage';

/**
 * Tournament CRUD Tests
 *
 * These tests validate tournament creation, editing, deletion, and conclusion.
 * All test tournaments use the [E2E_TEST] prefix for easy identification and cleanup.
 *
 * Cleanup: Run tests/e2e/fixtures/cleanup-test-data.sh to remove test data.
 */

/**
 * Format a date as YYYY.MM.DD. (Laravel format)
 */
function formatDate(date: Date): string {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}.${month}.${day}.`;
}

/**
 * Get a future date relative to today
 */
function getFutureDate(daysFromNow: number): string {
  const date = new Date();
  date.setDate(date.getDate() + daysFromNow);
  return formatDate(date);
}

/**
 * Get a past date relative to today
 */
function getPastDate(daysAgo: number): string {
  const date = new Date();
  date.setDate(date.getDate() - daysAgo);
  return formatDate(date);
}

createBrowserSuite('Tournament Management', { userType: 'regular' }, (ctx) => {
  // Generate unique timestamp for test isolation
  const timestamp = Date.now();

  it('creates new tournament', async () => {
    const { tournamentPage, upcomingPage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Create Test ${timestamp}`;

    await tournamentPage.openCreateForm();

    await tournamentPage.fillTournamentDetails({
      title: tournamentTitle,
      date: getFutureDate(30), // 30 days from now
      location: 'Budapest Contrast Phase', // Real location using Google Places
    });

    await tournamentPage.submitForm();

    // Should redirect to tournament view page
    expect(tournamentPage.isOnViewPage()).toBe(true);

    // Verify we have a tournament ID in the URL (confirms creation)
    const tournamentId = tournamentPage.getIdFromUrl();
    expect(tournamentId).not.toBeNull();

    // Verify tournament appears on Upcoming page
    await upcomingPage.open();
    await upcomingPage.waitForTableLoaded();

    // Clear country filter (user preference may have auto-filter enabled)
    await upcomingPage.clearCountryFilter();

    // Switch to "all" paging to show all tournaments
    await upcomingPage.clickAllPager();

    // Check that tournament is visible
    expect(await upcomingPage.hasTournamentInTable(tournamentTitle)).toBe(true);
  });

  it('edits an existing tournament', async () => {
    const { tournamentPage } = ctx.pages;
    const originalTitle = `${E2E_TEST_PREFIX} Edit Test ${timestamp}`;
    const editTimestamp = Date.now();
    const newTitle = `${E2E_TEST_PREFIX} Edited Test ${editTimestamp}`;

    // First create a tournament to edit
    await tournamentPage.openCreateForm();
    await tournamentPage.fillTournamentDetails({
      title: originalTitle,
      date: getFutureDate(60), // 60 days from now
      online: true,
    });
    await tournamentPage.submitForm();

    // Verify creation succeeded before continuing
    expect(tournamentPage.isOnViewPage()).toBe(true);

    // Get the tournament ID
    const tournamentId = tournamentPage.getIdFromUrl();
    expect(tournamentId).not.toBeNull();

    // Navigate to edit page via the edit button on the view page
    await tournamentPage.clickEdit();

    // Verify we're on the edit page
    const editUrl = tournamentPage.getUrl();
    expect(editUrl).toContain('/edit');

    // Update the title with new timestamp
    await tournamentPage.fillInput('title', newTitle);
    await tournamentPage.submitForm();

    // Should redirect to tournament view page after update
    expect(tournamentPage.isOnViewPage()).toBe(true);
  });

  it('deletes a tournament', async () => {
    const { tournamentPage, organizePage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Delete Test ${timestamp}`;

    // First create a tournament to delete
    await tournamentPage.openCreateForm();
    await tournamentPage.fillTournamentDetails({
      title: tournamentTitle,
      date: getFutureDate(90), // 90 days from now
      online: true,
    });
    await tournamentPage.submitForm();

    // Get the tournament ID
    const tournamentId = tournamentPage.getIdFromUrl();
    expect(tournamentId).not.toBeNull();

    // Go back to tournament view page and delete
    await tournamentPage.open(tournamentId!);
    await tournamentPage.deleteTournament();

    // Should redirect away from the deleted tournament
    const currentUrl = tournamentPage.getUrl();
    expect(currentUrl).not.toContain(`/tournaments/${tournamentId}`);

    // Verify tournament is NOT visible on Organize page
    await organizePage.open();
    await organizePage.waitForTournamentsLoaded();

    expect(await organizePage.hasTournamentInList(tournamentTitle)).toBe(false);
  });

  it('concludes a tournament with results', async () => {
    const { tournamentPage, resultsPage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Conclude Test ${timestamp}`;

    // Create a tournament to conclude
    await tournamentPage.openCreateForm();
    await tournamentPage.fillTournamentDetails({
      title: tournamentTitle,
      date: getPastDate(7), // 7 days ago (past date for conclusion)
      online: true,
    });
    await tournamentPage.submitForm();

    // Get the tournament ID
    const tournamentId = tournamentPage.getIdFromUrl();
    expect(tournamentId).not.toBeNull();

    // Navigate to edit page and mark as concluded
    await tournamentPage.openEdit(tournamentId!);
    await tournamentPage.markAsConcluded(8); // 8 players

    await tournamentPage.submitForm();

    // Should redirect to tournament view page
    expect(tournamentPage.isOnViewPage()).toBe(true);

    // Verify tournament appears on Results page
    await resultsPage.open();
    await resultsPage.waitForResultsLoaded();

    // Get count before changing filters, then wait for count to change after
    const countBeforeFilters = await resultsPage.getResultsCount();

    // Clear country filter (user preference may have auto-filter enabled)
    await resultsPage.clearCountryFilter();

    // Select 500/page to avoid paging issues
    await resultsPage.select500PerPage();

    // Wait for the table to update with more results
    await resultsPage.waitForTournamentNumberToChange(countBeforeFilters);

    // Check that the concluded tournament appears in results
    expect(await resultsPage.hasTournamentInTable(tournamentTitle)).toBe(true);
  });
});
