import { createBrowserSuite, it, expect, beforeAll } from '../helpers/test-fixture';
import { E2E_TEST_PREFIX } from '../pages/TournamentPage';

/**
 * Tournament Entries Tests
 *
 * These tests validate tournament entry management including:
 * - Claiming spots with and without decklists
 * - Registration for upcoming tournaments
 * - Manual import of claims by tournament creator
 * - Conflict detection and claim merging
 *
 * All test tournaments use the [E2E_TEST] prefix for easy identification and cleanup.
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

// Identity labels for testing - using the display names from the select options
const CORP_IDENTITY_LABEL_1 = 'Weyland Consortium: Building a Better World';
const RUNNER_IDENTITY_LABEL_1 = 'Chaos Theory: Wünderkind';
const CORP_IDENTITY_LABEL_2 = 'NBN: Making News';
const RUNNER_IDENTITY_LABEL_2 = 'Noise: Hacker Extraordinaire';

// =====================
// Test Suite: Claiming with decks
// =====================
createBrowserSuite('Claim with decks', { userType: 'regular' }, (ctx) => {
  const timestamp = Date.now();
  let tournamentId: number | null = null;

  beforeAll(async () => {
    // Create and conclude a tournament for claiming
    const { tournamentPage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Claim With Decks ${timestamp}`;

    await tournamentPage.openCreateForm();
    await tournamentPage.fillTournamentDetails({
      title: tournamentTitle,
      date: getPastDate(7), // Past date for conclusion
      online: true,
    });
    await tournamentPage.submitForm();

    tournamentId = tournamentPage.getIdFromUrl();
    expect(tournamentId).not.toBeNull();

    // Conclude the tournament
    await tournamentPage.openEdit(tournamentId!);
    await tournamentPage.markAsConcluded(4); // 4 players
    await tournamentPage.submitForm();
  });

  it('claims spot on concluded tournament with decklist and then removes it', async () => {
    const { tournamentDetailsPage, tournamentPage } = ctx.pages;

    // Navigate to tournament
    await tournamentPage.open(tournamentId!);
    await tournamentDetailsPage.waitForPageLoaded();

    // Verify claim button is present
    const hasClaimButton = await tournamentDetailsPage.claimButton.isVisible();
    expect(hasClaimButton).toBe(true);

    // Open claim modal
    await tournamentDetailsPage.clickClaimButton();

    // Wait for decks to load and submit claim
    await tournamentDetailsPage.submitClaimWithDecks(1); // Rank 1

    // Verify claim is displayed
    const hasClaim = await tournamentDetailsPage.hasPlayerClaim();
    expect(hasClaim).toBe(true);

    // Remove the claim
    await tournamentDetailsPage.removeClaim();

    // Verify claim button returns
    const hasClaimButtonAfter = await tournamentDetailsPage.claimButton.isVisible();
    expect(hasClaimButtonAfter).toBe(true);
  });
});

// =====================
// Test Suite: Claiming without decks
// =====================
createBrowserSuite('Claim without decks', { userType: 'regular' }, (ctx) => {
  const timestamp = Date.now();
  let tournamentId: number | null = null;

  beforeAll(async () => {
    // Create and conclude a tournament for claiming
    const { tournamentPage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Claim Without Decks ${timestamp}`;

    await tournamentPage.openCreateForm();
    await tournamentPage.fillTournamentDetails({
      title: tournamentTitle,
      date: getPastDate(7),
      online: true,
    });
    await tournamentPage.submitForm();

    tournamentId = tournamentPage.getIdFromUrl();
    expect(tournamentId).not.toBeNull();

    // Conclude the tournament
    await tournamentPage.openEdit(tournamentId!);
    await tournamentPage.markAsConcluded(4);
    await tournamentPage.submitForm();
  });

  it('claims spot on concluded tournament without decklist and then removes it', async () => {
    const { tournamentDetailsPage, tournamentPage } = ctx.pages;

    // Navigate to tournament
    await tournamentPage.open(tournamentId!);
    await tournamentDetailsPage.waitForPageLoaded();

    // Open claim modal
    await tournamentDetailsPage.clickClaimButton();

    // Submit claim without decks (using identity labels)
    await tournamentDetailsPage.submitClaimWithoutDecks(2, CORP_IDENTITY_LABEL_1, RUNNER_IDENTITY_LABEL_1);

    // Verify claim is displayed
    const hasClaim = await tournamentDetailsPage.hasPlayerClaim();
    expect(hasClaim).toBe(true);

    // Remove the claim
    await tournamentDetailsPage.removeClaim();

    // Verify claim button returns
    const hasClaimButtonAfter = await tournamentDetailsPage.claimButton.isVisible();
    expect(hasClaimButtonAfter).toBe(true);
  });
});

// =====================
// Test Suite: Registration
// =====================
createBrowserSuite('Registration', { userType: 'regular' }, (ctx) => {
  const timestamp = Date.now();
  let tournamentId: number | null = null;

  beforeAll(async () => {
    // Create an upcoming tournament for registration
    const { tournamentPage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Registration ${timestamp}`;

    await tournamentPage.openCreateForm();
    await tournamentPage.fillTournamentDetails({
      title: tournamentTitle,
      date: getFutureDate(30), // 30 days in the future
      online: true,
    });
    await tournamentPage.submitForm();

    tournamentId = tournamentPage.getIdFromUrl();
    expect(tournamentId).not.toBeNull();
  });

  it('register and unregister for an upcoming tournament', async () => {
    const { tournamentDetailsPage, tournamentPage } = ctx.pages;

    // Navigate to tournament
    await tournamentPage.open(tournamentId!);
    await tournamentDetailsPage.waitForPageLoaded();

    // Verify register button is present
    const hasRegisterButton = await tournamentDetailsPage.hasRegisterButton();
    expect(hasRegisterButton).toBe(true);

    // Click register
    await tournamentDetailsPage.clickRegister();

    // Verify unregister button appears
    const hasUnregisterButton = await tournamentDetailsPage.hasUnregisterButton();
    expect(hasUnregisterButton).toBe(true);

    // Verify user appears in registered players list
    const registeredPlayersText = await tournamentDetailsPage.registeredPlayers.textContent();
    expect(registeredPlayersText).toBeTruthy();

    // Click unregister
    await tournamentDetailsPage.clickUnregister();

    // Verify register button returns
    const hasRegisterButtonAfter = await tournamentDetailsPage.hasRegisterButton();
    expect(hasRegisterButtonAfter).toBe(true);
  });
});

// =====================
// Test Suite: Manual import
// =====================
createBrowserSuite('Manual import', { userType: 'regular' }, (ctx) => {
  const timestamp = Date.now();
  let tournamentId: number | null = null;

  beforeAll(async () => {
    // Create and conclude a tournament (user is creator)
    const { tournamentPage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Manual Import ${timestamp}`;

    await tournamentPage.openCreateForm();
    await tournamentPage.fillTournamentDetails({
      title: tournamentTitle,
      date: getPastDate(7),
      online: true,
    });
    await tournamentPage.submitForm();

    tournamentId = tournamentPage.getIdFromUrl();
    expect(tournamentId).not.toBeNull();

    // Conclude the tournament
    await tournamentPage.openEdit(tournamentId!);
    await tournamentPage.markAsConcluded(4);
    await tournamentPage.submitForm();
  });

  it('tournament creator importing claims manually, then deleting such claims', async () => {
    const { tournamentDetailsPage, tournamentPage } = ctx.pages;
    const importUsername = `TestPlayer_${timestamp}`;

    // Navigate to tournament
    await tournamentPage.open(tournamentId!);
    await tournamentDetailsPage.waitForPageLoaded();

    // Verify edit entries button is present (creator permission)
    const hasEditEntries = await tournamentDetailsPage.editEntriesButton.isVisible();
    expect(hasEditEntries).toBe(true);

    // Open manual import form
    await tournamentDetailsPage.openManualImportForm();

    // Add a manual claim (use default identities - no specific IDs needed)
    await tournamentDetailsPage.addManualClaim({
      rank: 1,
      username: importUsername,
    });

    // Verify imported entry appears
    const hasImportedEntry = await tournamentDetailsPage.hasImportedEntry(importUsername);
    expect(hasImportedEntry).toBe(true);

    // Delete the imported claim
    await tournamentDetailsPage.deleteImportedEntry(importUsername);

    // Verify claim is removed
    const hasImportedEntryAfter = await tournamentDetailsPage.hasImportedEntry(importUsername);
    expect(hasImportedEntryAfter).toBe(false);
  });
});

// =====================
// Test Suite: Claim conflict
// =====================
createBrowserSuite('Claim conflict', { userType: 'regular' }, (ctx) => {
  const timestamp = Date.now();
  let tournamentId: number | null = null;
  const importUsername = `ImportedPlayer_${timestamp}`;

  beforeAll(async () => {
    // Create and conclude a tournament
    const { tournamentPage, tournamentDetailsPage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Conflict ${timestamp}`;

    await tournamentPage.openCreateForm();
    await tournamentPage.fillTournamentDetails({
      title: tournamentTitle,
      date: getPastDate(7),
      online: true,
    });
    await tournamentPage.submitForm();

    tournamentId = tournamentPage.getIdFromUrl();
    expect(tournamentId).not.toBeNull();

    // Conclude the tournament
    await tournamentPage.openEdit(tournamentId!);
    await tournamentPage.markAsConcluded(4);
    await tournamentPage.submitForm();

    // Add an imported entry at rank 1 with specific identities (Weyland/Chaos Theory)
    await tournamentPage.open(tournamentId!);
    await tournamentDetailsPage.waitForPageLoaded();
    await tournamentDetailsPage.openManualImportForm();
    await tournamentDetailsPage.addManualClaim({
      rank: 1,
      username: importUsername,
      corpIdentityLabel: CORP_IDENTITY_LABEL_1,
      runnerIdentityLabel: RUNNER_IDENTITY_LABEL_1,
    });
  });

  it('displays conflict warning when multiple claims at same rank with different IDs', async () => {
    const { tournamentDetailsPage, tournamentPage } = ctx.pages;

    // Navigate to tournament
    await tournamentPage.open(tournamentId!);
    await tournamentDetailsPage.waitForPageLoaded();

    // Verify no conflict warning initially
    const hasConflictBefore = await tournamentDetailsPage.hasConflictWarning();
    expect(hasConflictBefore).toBe(false);

    // User claims rank 1 with DIFFERENT identities (NBN/Noise)
    await tournamentDetailsPage.clickClaimButton();
    await tournamentDetailsPage.submitClaimWithoutDecks(1, CORP_IDENTITY_LABEL_2, RUNNER_IDENTITY_LABEL_2);

    // Verify conflict warning appears
    const hasConflict = await tournamentDetailsPage.hasConflictWarning();
    expect(hasConflict).toBe(true);

    // Verify there are 2 entries at rank 1
    const entryCount = await tournamentDetailsPage.getEntryCountAtRank(1);
    expect(entryCount).toBe(2);

    // Remove claim
    await tournamentDetailsPage.removeClaim();

    // Verify conflict warning is cleared after claim removal
    const hasConflictAfter = await tournamentDetailsPage.hasConflictWarning();
    expect(hasConflictAfter).toBe(false);
  });
});

// =====================
// Test Suite: Claim merging
// =====================
createBrowserSuite('Claim merging', { userType: 'regular' }, (ctx) => {
  const timestamp = Date.now();
  let tournamentId: number | null = null;
  const importUsername = `MergePlayer_${timestamp}`;

  beforeAll(async () => {
    // Create and conclude a tournament
    const { tournamentPage, tournamentDetailsPage } = ctx.pages;
    const tournamentTitle = `${E2E_TEST_PREFIX} Merge ${timestamp}`;

    await tournamentPage.openCreateForm();
    await tournamentPage.fillTournamentDetails({
      title: tournamentTitle,
      date: getPastDate(7),
      online: true,
    });
    await tournamentPage.submitForm();

    tournamentId = tournamentPage.getIdFromUrl();
    expect(tournamentId).not.toBeNull();

    // Conclude the tournament
    await tournamentPage.openEdit(tournamentId!);
    await tournamentPage.markAsConcluded(4);
    await tournamentPage.submitForm();

    // Add an imported entry at rank 1 with specific identities (Weyland/Chaos Theory)
    await tournamentPage.open(tournamentId!);
    await tournamentDetailsPage.waitForPageLoaded();
    await tournamentDetailsPage.openManualImportForm();
    await tournamentDetailsPage.addManualClaim({
      rank: 1,
      username: importUsername,
      corpIdentityLabel: CORP_IDENTITY_LABEL_1,
      runnerIdentityLabel: RUNNER_IDENTITY_LABEL_1,
    });
  });

  it('merges user claim with imported entry when identities match', async () => {
    const { tournamentDetailsPage, tournamentPage } = ctx.pages;

    // Navigate to tournament
    await tournamentPage.open(tournamentId!);
    await tournamentDetailsPage.waitForPageLoaded();

    // Verify imported entry exists before claim
    const hasImportBefore = await tournamentDetailsPage.hasImportedEntry(importUsername);
    expect(hasImportBefore).toBe(true);

    // User claims rank 1 with SAME identities (Weyland/Chaos Theory) to trigger merge
    await tournamentDetailsPage.clickClaimButton();
    await tournamentDetailsPage.submitClaimWithoutDecks(1, CORP_IDENTITY_LABEL_1, RUNNER_IDENTITY_LABEL_1);

    // Verify NO conflict warning (merge occurred)
    const hasConflict = await tournamentDetailsPage.hasConflictWarning();
    expect(hasConflict).toBe(false);

    // Verify only one entry at rank 1 (merged)
    const entryCount = await tournamentDetailsPage.getEntryCountAtRank(1);
    expect(entryCount).toBe(1);

    // Verify user has a claim
    const hasClaim = await tournamentDetailsPage.hasPlayerClaim();
    expect(hasClaim).toBe(true);

    // The imported entry should be gone (merged into user's claim)
    const hasImportAfter = await tournamentDetailsPage.hasImportedEntry(importUsername);
    expect(hasImportAfter).toBe(false);

    // Clean up: remove claim
    await tournamentDetailsPage.removeClaim();
  });
});
