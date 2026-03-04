import { BasePage } from './BasePage';

export class TournamentDetailsPage extends BasePage {
  // Header
  readonly tournamentTitle = this.page.locator('#tournament-title');
  readonly tournamentType = this.page.locator('#tournament-type');
  readonly tournamentCreator = this.page.locator('#tournament-creator');
  readonly controlButtons = this.page.locator('#control-buttons');
  readonly editButton = this.page.locator('#edit-button');
  readonly transferButton = this.page.locator('#button-transfer');
  readonly deleteButton = this.page.locator('#delete-button');

  // Info section
  readonly tournamentLocation = this.page.locator('#tournament-location');
  readonly tournamentDate = this.page.locator('#tournament-date');
  readonly cardpool = this.page.locator('#cardpool');
  readonly mwl = this.page.locator('#mwl');
  readonly tournamentFormat = this.page.locator('#tournament-format');
  readonly store = this.page.locator('#store');
  readonly address = this.page.locator('#address');
  readonly regTime = this.page.locator('#reg-time');
  readonly startTime = this.page.locator('#start-time');

  // Map
  readonly showMapButton = this.page.locator('#button-show-map');
  readonly mapContainer = this.page.locator('#map');

  // Statistics charts
  readonly statChartRunner = this.page.locator('#stat-chart-runner');
  readonly statChartCorp = this.page.locator('#stat-chart-corp');
  readonly statsButtonId = this.page.locator('#button-stats-id');
  readonly statsButtonFaction = this.page.locator('#button-stats-faction');
  readonly chartLoader = this.page.locator('.loader-chart');

  // Photos section
  readonly addPhotosButton = this.page.locator('#button-add-photos');
  readonly donePhotosButton = this.page.locator('#button-done-photos');
  readonly addPhotoSection = this.page.locator('#section-add-photos');
  readonly photosHeader = this.page.locator('h5:has(.fa-camera)');

  // Videos section
  readonly addVideosButton = this.page.locator('#button-add-videos');
  readonly doneVideosButton = this.page.locator('#button-done-videos');
  readonly addVideoSection = this.page.locator('#section-add-videos');
  readonly videosHeader = this.page.locator('h5:has(.fa-video-camera)');

  // Login suggestions (shown when logged out)
  readonly suggestLoginClaim = this.page.locator('#suggest-login');
  readonly suggestLoginRegister = this.page.locator('#suggest-login2');
  readonly suggestLoginMedia = this.page.locator('#suggest-login-media');

  // Results section
  readonly resultsHeader = this.page.locator('h5:has(.fa-list-ol)');
  readonly playerNumbers = this.page.locator('#player-numbers');
  readonly entriesSwiss = this.page.locator('#entries-swiss');
  readonly entriesTop = this.page.locator('#entries-top');
  readonly claimButton = this.page.locator('#button-claim').first();
  readonly claimButtons = this.page.locator('.btn-claim');

  // Registration
  readonly registerButton = this.page.locator('#register');
  readonly unregisterButton = this.page.locator('#unregister');
  readonly registeredPlayers = this.page.locator('#registered-players');
  readonly noRegisteredPlayers = this.page.locator('#no-registered-players');

  // Viewing indicators (for creator/admin)
  readonly viewingAsAdmin = this.page.locator('#viewing-as-admin');
  readonly viewingAsCreator = this.page.locator('#viewing-as-creator');

  // Admin-specific buttons
  readonly approveButton = this.page.locator('#approve-button');
  readonly rejectButton = this.page.locator('#reject-button');
  readonly restoreButton = this.page.locator('#restore-button');
  readonly approveAllPhotosButton = this.page.locator('#button-approve-all-photos');

  // Results admin info
  readonly concludedBySection = this.page.locator('#concluded-by');
  readonly revertConclusionButton = this.page.locator('button:has-text("Revert conclusion")');

  // Claim modal - general
  readonly claimModal = this.page.locator('#claimModal');

  // Claim modal - with decks tab
  readonly menuDecksTab = this.page.locator('#menu-decks');
  readonly tabWithDecks = this.page.locator('#tab-with-decks');
  readonly rankSelect = this.page.locator('#claimModal select[name="rank"]');
  readonly rankTopSelect = this.page.locator('#claimModal select[name="rank_top"]');
  readonly corpDeckSelect = this.page.locator('#corp_deck');
  readonly runnerDeckSelect = this.page.locator('#runner_deck');
  readonly submitClaimButton = this.page.locator('#submit-claim');
  readonly deckLoader = this.page.locator('#claimModal .deck-loader');

  // Claim modal - without decks tab (IDs only)
  readonly menuIdsTab = this.page.locator('#menu-ids');
  readonly tabWithoutDecks = this.page.locator('#tab-without-decks');
  readonly rankNoDeckSelect = this.page.locator('#claimModal select[name="rank_nodeck"]');
  readonly rankTopNoDeckSelect = this.page.locator('#claimModal select[name="rank_top_nodeck"]');
  readonly corpDeckIdentitySelect = this.page.locator('#corp_deck_identity');
  readonly runnerDeckIdentitySelect = this.page.locator('#runner_deck_identity');
  readonly submitIdClaimButton = this.page.locator('#submit-id-claim');

  // User's claim display
  readonly playerClaim = this.page.locator('#player-claim');
  readonly removeClaimButton = this.page.locator('#remove-claim');

  // Manual import
  readonly editEntriesButton = this.page.locator('#button-edit-entries');
  readonly doneEntriesButton = this.page.locator('#button-done-entries');
  readonly editEntriesSection = this.page.locator('#section-edit-entries');
  readonly manualRankSelect = this.page.locator('#section-edit-entries select[name="rank"]');
  readonly manualRankTopSelect = this.page.locator('#section-edit-entries select[name="rank_top"]');
  readonly manualUsernameInput = this.page.locator('#section-edit-entries input[name="import_username"]');
  readonly manualCorpIdentitySelect = this.page.locator('#corp_deck_identity_manual');
  readonly manualRunnerIdentitySelect = this.page.locator('#runner_deck_identity_manual');
  readonly addClaimButton = this.page.locator('#button-add-claim');

  // Conflict warning
  readonly conflictWarning = this.page.locator('#conflict-warning');

  // Entries table
  readonly entriesSwissTable = this.page.locator('#entries-swiss');
  readonly entriesTopTable = this.page.locator('#entries-top');
  readonly importUserCells = this.page.locator('.import-user');
  readonly deleteAnonymButtons = this.page.locator('.delete-anonym');

  async open(tournamentPath: string) {
    await this.navigate(`/tournaments/${tournamentPath}`, { waitUntil: 'domcontentloaded' });
  }

  async waitForPageLoaded() {
    await this.tournamentTitle.waitFor({ state: 'visible', timeout: 15000 });
  }

  async waitForMapButtonEnabled() {
    await this.page.waitForFunction(
      () => {
        const btn = document.querySelector('#button-show-map') as HTMLButtonElement | null;
        return btn && !btn.disabled;
      },
      { timeout: 30000 }
    );
  }

  async clickShowMap() {
    await this.waitForMapButtonEnabled();
    await this.showMapButton.click();
  }

  async waitForMapDisplayed() {
    // After clicking, the button should be hidden and map should render
    await this.page.waitForFunction(
      () => {
        const btn = document.querySelector('#button-show-map');
        const map = document.querySelector('#map');
        return btn?.classList.contains('hidden-xs-up') && map && map.children.length > 0;
      },
      { timeout: 30000 }
    );
  }

  async waitForChartsLoaded() {
    // Wait for the loader to disappear and charts to have content
    await this.page.waitForFunction(
      () => {
        const loader = document.querySelector('.loader-chart');
        const runnerChart = document.querySelector('#stat-chart-runner');
        return loader?.classList.contains('hidden-xs-up') && runnerChart && runnerChart.children.length > 0;
      },
      { timeout: 30000 }
    );
  }

  async isChartVisible(chartSelector: string): Promise<boolean> {
    const chart = this.page.locator(chartSelector);
    const count = await chart.count();
    if (count === 0) return false;
    const hasContent = await chart.evaluate((el) => el.children.length > 0);
    return hasContent;
  }

  async getTitle(): Promise<string> {
    return (await this.tournamentTitle.textContent() || '').trim();
  }

  async getType(): Promise<string> {
    return (await this.tournamentType.textContent() || '').trim();
  }

  async getCreator(): Promise<string> {
    return (await this.tournamentCreator.textContent() || '').trim();
  }

  async getLocation(): Promise<string> {
    return (await this.tournamentLocation.textContent() || '').trim();
  }

  async getDate(): Promise<string> {
    return (await this.tournamentDate.textContent() || '').trim();
  }

  async getCardpool(): Promise<string> {
    return (await this.cardpool.textContent() || '').trim();
  }

  async getMwl(): Promise<string> {
    return (await this.mwl.textContent() || '').trim();
  }

  async hasClaimButtons(): Promise<boolean> {
    const count = await this.claimButtons.count();
    return count > 0;
  }

  async hasAddPhotosButton(): Promise<boolean> {
    const count = await this.addPhotosButton.count();
    if (count === 0) return false;
    return await this.addPhotosButton.isVisible();
  }

  async hasAddVideosButton(): Promise<boolean> {
    const count = await this.addVideosButton.count();
    if (count === 0) return false;
    return await this.addVideosButton.isVisible();
  }

  async hasRegisterButton(): Promise<boolean> {
    const count = await this.registerButton.count();
    if (count === 0) return false;
    return await this.registerButton.isVisible();
  }

  async hasUnregisterButton(): Promise<boolean> {
    const count = await this.unregisterButton.count();
    if (count === 0) return false;
    return await this.unregisterButton.isVisible();
  }

  async hasControlButtons(): Promise<boolean> {
    const count = await this.controlButtons.count();
    if (count === 0) return false;
    return await this.controlButtons.isVisible();
  }

  async hasSuggestLoginClaim(): Promise<boolean> {
    const count = await this.suggestLoginClaim.count();
    if (count === 0) return false;
    return await this.suggestLoginClaim.isVisible();
  }

  async hasSuggestLoginRegister(): Promise<boolean> {
    const count = await this.suggestLoginRegister.count();
    if (count === 0) return false;
    return await this.suggestLoginRegister.isVisible();
  }

  async hasSuggestLoginMedia(): Promise<boolean> {
    const count = await this.suggestLoginMedia.count();
    if (count === 0) return false;
    return await this.suggestLoginMedia.isVisible();
  }

  async hasResultsSection(): Promise<boolean> {
    const count = await this.playerNumbers.count();
    return count > 0;
  }

  async hasEntriesTable(): Promise<boolean> {
    const swissCount = await this.entriesSwiss.count();
    return swissCount > 0;
  }

  async hasApproveButton(): Promise<boolean> {
    const count = await this.approveButton.count();
    if (count === 0) return false;
    return await this.approveButton.isVisible();
  }

  async hasRejectButton(): Promise<boolean> {
    const count = await this.rejectButton.count();
    if (count === 0) return false;
    return await this.rejectButton.isVisible();
  }

  async hasRestoreButton(): Promise<boolean> {
    const count = await this.restoreButton.count();
    if (count === 0) return false;
    return await this.restoreButton.isVisible();
  }

  async hasRevertConclusionButton(): Promise<boolean> {
    const count = await this.revertConclusionButton.count();
    if (count === 0) return false;
    return await this.revertConclusionButton.isVisible();
  }

  async hasConcludedBySection(): Promise<boolean> {
    const count = await this.concludedBySection.count();
    if (count === 0) return false;
    return await this.concludedBySection.isVisible();
  }

  async getConcludedByText(): Promise<string> {
    return (await this.concludedBySection.textContent() || '').trim();
  }

  // ===================
  // Claim Methods
  // ===================

  /**
   * Click the main claim button to open the claim modal.
   */
  async clickClaimButton() {
    await this.claimButton.click();
    await this.claimModal.waitFor({ state: 'visible', timeout: 5000 });
  }

  /**
   * Wait for decks to load from NetrunnerDB API in the claim modal.
   */
  async waitForDecksLoaded() {
    // Wait for the deck loaders to become hidden
    await this.page.waitForFunction(
      () => {
        const loaders = document.querySelectorAll('#claimModal .deck-loader');
        return Array.from(loaders).every((el) => el.classList.contains('hidden-xs-up'));
      },
      { timeout: 30000 }
    );
  }

  /**
   * Submit a claim with decks (on the "With decks" tab).
   * @param rank Swiss rank to claim
   * @param corpDeckIndex Optional index of corp deck to select (defaults to first)
   * @param runnerDeckIndex Optional index of runner deck to select (defaults to first)
   */
  async submitClaimWithDecks(rank: number, corpDeckIndex = 0, runnerDeckIndex = 0) {
    // Make sure we're on the "With decks" tab
    const isActive = await this.tabWithDecks.evaluate((el) => el.classList.contains('active'));
    if (!isActive) {
      await this.menuDecksTab.click();
      await this.page.waitForTimeout(300);
    }

    // Wait for decks to load
    await this.waitForDecksLoaded();

    // Select rank
    await this.rankSelect.selectOption({ value: rank.toString() });

    // Select corp deck (use index-based selection)
    const corpOptions = await this.corpDeckSelect.locator('option').all();
    if (corpOptions.length > corpDeckIndex) {
      const corpValue = await corpOptions[corpDeckIndex].getAttribute('value');
      if (corpValue) {
        await this.corpDeckSelect.selectOption({ value: corpValue });
      }
    }

    // Select runner deck (use index-based selection)
    const runnerOptions = await this.runnerDeckSelect.locator('option').all();
    if (runnerOptions.length > runnerDeckIndex) {
      const runnerValue = await runnerOptions[runnerDeckIndex].getAttribute('value');
      if (runnerValue) {
        await this.runnerDeckSelect.selectOption({ value: runnerValue });
      }
    }

    // Submit
    await this.submitClaimButton.click();
    await this.page.waitForLoadState('domcontentloaded');
  }

  /**
   * Submit a claim without decks (IDs only, on the "Without decks" tab).
   * @param rank Swiss rank to claim
   * @param corpIdentityLabel Corp identity label (e.g., "NBN: Making News") - optional
   * @param runnerIdentityLabel Runner identity label (e.g., "Noise: Hacker Extraordinaire") - optional
   */
  async submitClaimWithoutDecks(
    rank: number,
    corpIdentityLabel?: string,
    runnerIdentityLabel?: string
  ) {
    // Switch to the "Without decks" tab
    await this.menuIdsTab.click();
    await this.tabWithoutDecks.waitFor({ state: 'visible', timeout: 5000 });

    // Select rank - this triggers setIdentities() which auto-populates identity selects
    await this.rankNoDeckSelect.selectOption({ value: rank.toString() });

    // Wait for setIdentities() JavaScript to complete DOM updates
    // This prevents race conditions on slower CI environments
    await this.page.waitForTimeout(100);

    // Select corp identity if provided (by label)
    if (corpIdentityLabel) {
      await this.corpDeckIdentitySelect.selectOption({ label: corpIdentityLabel });
    }

    // Select runner identity if provided (by label)
    if (runnerIdentityLabel) {
      await this.runnerDeckIdentitySelect.selectOption({ label: runnerIdentityLabel });
    }

    // Submit and wait for full page reload after redirect
    await this.submitIdClaimButton.click();
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Check if the user has an existing claim displayed.
   */
  async hasPlayerClaim(): Promise<boolean> {
    const count = await this.playerClaim.count();
    if (count === 0) return false;
    return await this.playerClaim.isVisible();
  }

  /**
   * Remove the user's claim.
   */
  async removeClaim() {
    await this.removeClaimButton.click();
    await this.page.waitForLoadState('domcontentloaded');
  }

  // ===================
  // Registration Methods
  // ===================

  /**
   * Click the register button.
   */
  async clickRegister() {
    await this.registerButton.click();
    await this.page.waitForLoadState('domcontentloaded');
  }

  /**
   * Click the unregister button.
   */
  async clickUnregister() {
    await this.unregisterButton.click();
    await this.page.waitForLoadState('domcontentloaded');
  }

  /**
   * Check if a user is listed in registered players.
   */
  async isUserInRegisteredPlayers(username: string): Promise<boolean> {
    const text = await this.registeredPlayers.textContent() || '';
    return text.includes(username);
  }

  // ===================
  // Manual Import Methods
  // ===================

  /**
   * Open the manual import form.
   */
  async openManualImportForm() {
    await this.editEntriesButton.click();
    await this.editEntriesSection.waitFor({ state: 'visible', timeout: 5000 });
  }

  /**
   * Close the manual import form.
   */
  async closeManualImportForm() {
    await this.doneEntriesButton.click();
    await this.editEntriesSection.waitFor({ state: 'hidden', timeout: 5000 });
  }

  /**
   * Add a manual (imported) claim.
   */
  async addManualClaim(options: {
    rank: number;
    rankTop?: number;
    username: string;
    corpIdentityLabel?: string;
    runnerIdentityLabel?: string;
  }) {
    // Make sure form is open
    const isVisible = await this.editEntriesSection.isVisible();
    if (!isVisible) {
      await this.openManualImportForm();
    }

    // Fill rank
    await this.manualRankSelect.selectOption({ value: options.rank.toString() });

    // Fill top rank if provided and select exists
    if (options.rankTop !== undefined) {
      const topRankCount = await this.manualRankTopSelect.count();
      if (topRankCount > 0) {
        await this.manualRankTopSelect.selectOption({ value: options.rankTop.toString() });
      }
    }

    // Fill username
    await this.manualUsernameInput.fill(options.username);

    // Select corp identity if provided (by label)
    if (options.corpIdentityLabel) {
      await this.manualCorpIdentitySelect.selectOption({ label: options.corpIdentityLabel });
    }

    // Select runner identity if provided (by label)
    if (options.runnerIdentityLabel) {
      await this.manualRunnerIdentitySelect.selectOption({ label: options.runnerIdentityLabel });
    }

    // Submit
    await this.addClaimButton.click();
    await this.page.waitForLoadState('domcontentloaded');
  }

  /**
   * Check if an imported entry exists with the given username.
   */
  async hasImportedEntry(username: string): Promise<boolean> {
    const cells = await this.importUserCells.all();
    for (const cell of cells) {
      const text = await cell.textContent();
      if (text && text.trim() === username) {
        return true;
      }
    }
    return false;
  }

  /**
   * Delete an imported entry by username.
   */
  async deleteImportedEntry(username: string) {
    // Find the row containing the import username
    const rows = await this.page.locator('#entries-swiss tbody tr').all();
    for (const row of rows) {
      const importCell = row.locator('.import-user');
      const cellCount = await importCell.count();
      if (cellCount > 0) {
        const text = await importCell.textContent();
        if (text && text.trim() === username) {
          // Find and click the delete button in this row
          const deleteBtn = row.locator('.delete-anonym');
          await deleteBtn.click();
          await this.page.waitForLoadState('domcontentloaded');
          return;
        }
      }
    }
  }

  // ===================
  // Conflict Methods
  // ===================

  /**
   * Check if the conflict warning is displayed.
   */
  async hasConflictWarning(): Promise<boolean> {
    const count = await this.conflictWarning.count();
    if (count === 0) return false;
    return await this.conflictWarning.isVisible();
  }

  /**
   * Get the number of entries at a specific rank in the swiss table.
   */
  async getEntryCountAtRank(rank: number): Promise<number> {
    const rows = await this.entriesSwissTable.locator('tbody tr').all();
    let count = 0;
    for (const row of rows) {
      const rankCell = row.locator('td').first();
      const text = await rankCell.textContent() || '';
      if (text.includes(`#${rank}`)) {
        count++;
      }
    }
    return count;
  }
}
