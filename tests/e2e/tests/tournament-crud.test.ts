import { createBrowserSuite, it, expect } from '../helpers/test-fixture';
import { E2E_TEST_PREFIX, TournamentDetails } from '../pages/TournamentPage';

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

  async function createTournamentAndVerify(details: TournamentDetails) {
    const { tournamentPage, tournamentDetailsPage } = ctx.pages;

    await tournamentPage.openCreateForm();
    await tournamentPage.fillTournamentDetails(details);
    await tournamentPage.submitForm();

    // Should redirect to tournament view page
    expect(tournamentPage.isOnViewPage()).toBe(true);
    expect(tournamentPage.getIdFromUrl()).not.toBeNull();

    // Verify details page is visible and title matches exactly
    await tournamentDetailsPage.waitForPageLoaded();
    expect(await tournamentDetailsPage.getTitle()).toBe(details.title);
  }

  async function openUpcomingAndShowAll() {
    const { upcomingPage } = ctx.pages;
    await upcomingPage.open();
    await upcomingPage.waitForTableLoaded();
    await upcomingPage.clearCountryFilter();
    await upcomingPage.clickAllPager();
    await upcomingPage.clickRecurringAllPager();

    const total = await upcomingPage.getTotalCount();
    await expect.poll(async () => await upcomingPage.getShowingTo()).toBe(total);
  }

  it('creates minimal tournament entry with location', async () => {
    const { tournamentDetailsPage, upcomingPage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Create Test ${Date.now()}`;
    const date = getFutureDate(30);

    await createTournamentAndVerify({
      title: tournamentTitle,
      date,
      location: 'Budapest Contrast Phase',
    });

    expect(await tournamentDetailsPage.getLocation()).toContain('Budapest');
    expect(await tournamentDetailsPage.getDate()).toContain(date.replace(/\.$/, ''));

    await openUpcomingAndShowAll();
    expect(await upcomingPage.hasTournamentInTable(tournamentTitle)).toBe(true);
    const row = await upcomingPage.getUpcomingRowDataByTitle(tournamentTitle);
    expect(row.title).toContain(tournamentTitle);
    expect(row.location).toContain('Budapest');
    expect(row.date.length).toBeGreaterThan(0);
    expect(row.regs).toMatch(/^\d+$/);
    expect(row.cardpool.length).toBeGreaterThan(0);
    expect(row.type.length).toBeGreaterThan(0);
  });

  it('creates online event with type/cardpool/format/mwl/decklist/contact/social/description/times', async () => {
    const { tournamentDetailsPage, upcomingPage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Create Test ${Date.now()}`;
    const date = getFutureDate(30);
    const description = '## Online showdown\n\n- Bring startup decks\n- Pairings in Discord';

    await createTournamentAndVerify({
      title: tournamentTitle,
      date,
      online: true,
      type: 'district championship',
      cardpool: 'Elevation',
      format: 'startup',
      mwl: 'Standard Ban List 26.03',
      decklistMandatory: true,
      contact: 'organizer@example.com',
      facebookLink: 'https://www.facebook.com/groups/e2e-test-group',
      description,
      regTime: '10:00',
      startTime: '11:00',
    });

    expect((await tournamentDetailsPage.getType()).toLowerCase()).toContain('district');
    expect(await tournamentDetailsPage.getDate()).toContain(date.replace(/\.$/, ''));
    expect(await tournamentDetailsPage.getCardpool()).toContain('Elevation');
    expect((await tournamentDetailsPage.getFormat()).toLowerCase()).toContain('startup');
    const mwlText = await tournamentDetailsPage.getMwl();
    expect(mwlText).toContain('Standard Ban List');
    expect(mwlText).toContain('26.03');
    expect(await tournamentDetailsPage.isDecklistMandatory()).toBe(true);
    expect(await tournamentDetailsPage.getContact()).toBe('organizer@example.com');
    expect(await tournamentDetailsPage.hasFacebookLink()).toBe(true);
    expect(await tournamentDetailsPage.getFacebookLinkHref()).toContain('facebook.com/groups/e2e-test-group');
    expect(await tournamentDetailsPage.getDescriptionText()).toContain('Online showdown');
    expect(await tournamentDetailsPage.getRegTime()).toBe('10:00');
    expect(await tournamentDetailsPage.getStartTime()).toBe('11:00');

    await openUpcomingAndShowAll();
    expect(await upcomingPage.hasTournamentInTable(tournamentTitle)).toBe(true);
    const row = await upcomingPage.getUpcomingRowDataByTitle(tournamentTitle);
    expect(row.title).toContain(tournamentTitle);
    expect(row.location.toLowerCase()).toContain('online');
    expect(row.date.length).toBeGreaterThan(0);
    expect(row.regs).toMatch(/^\d+$/);
    expect(row.cardpool).toContain('Elevation');
    expect(row.type.toLowerCase()).toContain('district');
  });

  it('creates multiple day event', async () => {
    const { tournamentDetailsPage, upcomingPage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Create Test ${Date.now()}`;
    const date = getFutureDate(30);
    const endDate = getFutureDate(31);

    await createTournamentAndVerify({
      title: tournamentTitle,
      date,
      endDate,
      location: 'Budapest Metagames',
    });

    const detailsDate = await tournamentDetailsPage.getDate();
    expect(detailsDate).toContain(date.replace(/\.$/, ''));
    expect(detailsDate).toContain(endDate.replace(/\.$/, ''));
    expect(await tournamentDetailsPage.getLocation()).toContain('Budapest');

    await openUpcomingAndShowAll();
    expect(await upcomingPage.hasTournamentInTable(tournamentTitle)).toBe(true);
    const row = await upcomingPage.getUpcomingRowDataByTitle(tournamentTitle);
    expect(row.title).toContain(tournamentTitle);
    expect(row.location).toContain('Budapest');
    expect(row.date.length).toBeGreaterThan(0);
    expect(row.regs).toMatch(/^\d+$/);
    expect(row.cardpool.length).toBeGreaterThan(0);
    expect(row.type.length).toBeGreaterThan(0);
  });

  it('creates tournament with official and unofficial prizes', async () => {
    const { tournamentDetailsPage, upcomingPage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Create Test ${Date.now()}`;

    await createTournamentAndVerify({
      title: tournamentTitle,
      date: getFutureDate(30),
      location: 'Budapest Contrast Phase',
      officialPrizeKit: '2026 H1 Casual Tournament Kit',
      unofficialPrize: {
        titleOrArtist: 'Adam by Drenus',
        quantity: '2',
      },
    });

    const prizesText = await tournamentDetailsPage.getPrizeSectionText();
    expect(prizesText).toContain('2026');
    expect(prizesText).toContain('Casual Tournament Kit');
    await expect.poll(async () => await tournamentDetailsPage.getUnofficialPrizesText()).toContain('Adam');
    const unofficialText = await tournamentDetailsPage.getUnofficialPrizesText();
    expect(unofficialText).toContain('Adam');
    expect(unofficialText).toContain('Drenus');
    expect(unofficialText).toMatch(/2x|2 x|2:/);

    await openUpcomingAndShowAll();
    expect(await upcomingPage.hasTournamentInTable(tournamentTitle)).toBe(true);
    const row = await upcomingPage.getUpcomingRowDataByTitle(tournamentTitle);
    expect(row.title).toContain(tournamentTitle);
    expect(row.location).toContain('Budapest');
    expect(row.date.length).toBeGreaterThan(0);
    expect(row.regs).toMatch(/^\d+$/);
    expect(row.cardpool.length).toBeGreaterThan(0);
    expect(row.type.length).toBeGreaterThan(0);
  });

  it('creates recurring weekly non-tournament event and verifies recurring table row', async () => {
    const { tournamentDetailsPage, upcomingPage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Create Test ${Date.now()}`;

    await createTournamentAndVerify({
      title: tournamentTitle,
      type: 'non-tournament event',
      recurringDay: 'Tuesday',
      location: 'Budapest Contrast Phase',
    });

    expect((await tournamentDetailsPage.getType()).toLowerCase()).toContain('non-tournament');
    const dateText = await tournamentDetailsPage.getDate();
    expect(dateText.toLowerCase()).toContain('recurring');
    expect(dateText).toContain('Tuesday');
    expect(await tournamentDetailsPage.getLocation()).toContain('Budapest');

    await openUpcomingAndShowAll();
    expect(await upcomingPage.hasRecurringTournamentInTable(tournamentTitle)).toBe(true);
    const row = await upcomingPage.getRecurringRowDataByTitle(tournamentTitle);
    expect(row.title).toContain(tournamentTitle);
    expect(row.location).toContain('Budapest');
    expect(row.day).toContain('Tuesday');
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
