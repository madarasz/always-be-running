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
  readonly claimButton = this.page.locator('#button-claim');
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
}
