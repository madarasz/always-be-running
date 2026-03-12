import { BasePage } from './BasePage';

// Prefix for test tournament names - used for cleanup
export const E2E_TEST_PREFIX = '[E2E_TEST]';

export interface TournamentDetails {
  title: string;
  type?: string;  // e.g., 'GNK / seasonal', 'community tournament'
  format?: string;  // e.g., 'standard', 'startup'
  date: string;  // Format: YYYY.MM.DD.
  online?: boolean;
  location?: string;  // Location search query (uses Google Places Autocomplete)
  description?: string;
}

export class TournamentPage extends BasePage {
  // Form elements (create/edit)
  readonly titleInput = this.page.locator('input[name="title"]');
  readonly typeSelect = this.page.locator('select[name="tournament_type_id"]');
  readonly formatSelect = this.page.locator('select[name="tournament_format_id"]');
  readonly cardpoolSelect = this.page.locator('select[name="cardpool_id"]');
  readonly dateInput = this.page.locator('input[name="date"]');
  readonly onlineCheckbox = this.page.locator('input#online');
  readonly locationSearchInput = this.page.locator('input[name="location_search"]');
  readonly descriptionInput = this.page.locator('textarea[name="description"]');
  readonly concludedCheckbox = this.page.locator('input#concluded');
  readonly playersNumberInput = this.page.locator('input[name="players_number"]');
  readonly topNumberSelect = this.page.locator('select[name="top_number"]');
  readonly submitButton = this.page.locator('input[type="submit"]');

  // View page elements
  readonly editButton = this.page.locator('#edit-button');
  readonly deleteButton = this.page.locator('#delete-button');
  readonly tournamentTitle = this.page.locator('#tournament-title');

  // Flash messages
  readonly flashMessage = this.page.locator('.alert');

  /**
   * Navigate to the create tournament form.
   */
  async openCreateForm() {
    await this.navigate('/tournaments/create');
    await this.submitButton.waitFor({ state: 'visible', timeout: 10000 });
    await this.waitForPrizesLoaded();
  }

  /**
   * Navigate to a tournament's view page.
   */
  async open(tournamentId: number) {
    await this.navigate(`/tournaments/${tournamentId}`);
    await this.tournamentTitle.waitFor({ state: 'visible', timeout: 15000 });
  }

  /**
   * Navigate to a tournament's edit page.
   */
  async openEdit(tournamentId: number) {
    await this.navigate(`/tournaments/${tournamentId}/edit`);
    await this.submitButton.waitFor({ state: 'visible', timeout: 10000 });
    await this.waitForPrizesLoaded();
  }

  /**
   * Fill tournament creation/edit form with the provided details.
   */
  async fillTournamentDetails(details: TournamentDetails) {
    await this.titleInput.fill(details.title);

    if (details.type) {
      await this.typeSelect.selectOption({ label: details.type });
    }

    if (details.format) {
      await this.formatSelect.selectOption({ label: details.format });
    }

    // Date - click to focus, fill, then click elsewhere to close datepicker
    await this.dateInput.click();
    await this.dateInput.fill(details.date);
    await this.titleInput.click();

    if (details.online) {
      const isChecked = await this.onlineCheckbox.isChecked();
      if (!isChecked) {
        await this.onlineCheckbox.check();
      }
    }

    if (details.location && !details.online) {
      await this.searchLocation(details.location);
    }

    if (details.description) {
      await this.descriptionInput.fill(details.description);
    }
  }

  /**
   * Search for a location using Google Places Autocomplete and select the first result.
   */
  async searchLocation(query: string) {
    // Dismiss cookie banner if present - it can interfere with the autocomplete dropdown
    await this.dismissCookieBanner();

    // Fill the input to trigger autocomplete
    await this.locationSearchInput.fill(query);

    // Wait for autocomplete suggestions to appear
    const firstSuggestion = this.page.locator('.pac-item').first();
    await firstSuggestion.waitFor({ state: 'visible', timeout: 10000 });

    // Click the first suggestion
    await firstSuggestion.click();

    // Wait for autocomplete dropdown to close
    await this.page.locator('.pac-container').waitFor({ state: 'hidden', timeout: 5000 });

    // Wait for location country to be populated
    await this.waitForInputToHaveValue('input[name="location_country"]');
  }

  /**
   * Wait for the prize select to have options loaded from the API.
   * The select is conditionally rendered by Vue.js only when prizes exist.
   */
  async waitForPrizesLoaded(timeout = 10000) {
    // Wait for the second option in prize select (first is "--- none ---", second is actual prize)
    const prizeSelectOption = this.page.locator('select[name="prize_id"] option:nth-child(2)');
    await prizeSelectOption.waitFor({ state: 'attached', timeout }).catch(() => {
      // If no prizes exist, the select won't render - that's OK
    });
  }

  /**
   * Wait for an input element to have a non-empty value.
   */
  async waitForInputToHaveValue(selector: string, timeout = 5000) {
    await this.page.waitForFunction(
      (sel) => {
        const input = document.querySelector(sel) as HTMLInputElement;
        return input && input.value.length > 0;
      },
      selector,
      { timeout }
    );
  }

  /**
   * Fill an input field by name (useful for edit forms).
   */
  async fillInput(inputName: string, value: string) {
    const input = this.page.locator(`input[name="${inputName}"]`);
    await input.waitFor({ state: 'visible', timeout: 5000 });
    await input.fill(value);
  }

  /**
   * Submit the tournament form and wait for navigation.
   */
  async submitForm() {
    const currentUrl = this.getUrl();
    const isCreatePage = currentUrl.includes('/create');

    await this.submitButton.click();

    // Wait for navigation after form submission
    // For create: wait for URL to change from /create to /tournaments/id
    // For edit: wait for URL to change from /edit to /tournaments/id
    if (isCreatePage || currentUrl.includes('/edit')) {
      await this.page.waitForURL(
        (url) => /\/tournaments\/\d+/.test(url.pathname) && !url.pathname.includes('/edit') && !url.pathname.includes('/create'),
        { timeout: 15000 }
      ).catch(async () => {
        // URL didn't change to view page - check for errors
        await this.page.waitForLoadState('domcontentloaded');
      });
    } else {
      await this.page.waitForLoadState('domcontentloaded');
    }

    // Check for validation errors if still on form page
    const newUrl = this.getUrl();
    if (newUrl === currentUrl || newUrl.includes('/create') || newUrl.includes('/edit')) {
      const errorAlert = this.page.locator('.alert-danger');
      if (await errorAlert.count() > 0) {
        const errorText = await errorAlert.textContent();
        console.error('Form validation error:', errorText);
      }
    }
  }

  /**
   * Click the edit button on tournament view page.
   */
  async clickEdit() {
    await this.editButton.click({ timeout: 10000 });
    await this.submitButton.waitFor({ state: 'visible', timeout: 10000 });
  }

  /**
   * Click the delete button and confirm the dialog.
   */
  async deleteTournament() {
    this.page.once('dialog', async dialog => {
      await dialog.accept();
    });
    await this.deleteButton.click({ timeout: 10000 });
    await this.page.waitForLoadState('domcontentloaded');
  }

  /**
   * Get the tournament title from the view page.
   */
  async getTitle(): Promise<string> {
    return await this.tournamentTitle.textContent() || '';
  }

  /**
   * Check if we're on the tournament view page.
   */
  isOnViewPage(): boolean {
    const url = this.getUrl();
    return /\/tournaments\/\d+/.test(url) && !url.includes('/edit') && !url.includes('/create');
  }

  /**
   * Get the tournament ID from the current URL.
   */
  getIdFromUrl(): number | null {
    const url = this.getUrl();
    const match = url.match(/\/tournaments\/(\d+)/);
    return match ? parseInt(match[1], 10) : null;
  }

  /**
   * Mark tournament as concluded with results.
   */
  async markAsConcluded(playersNumber: number, topCut: number = 0) {
    const isChecked = await this.concludedCheckbox.isChecked();
    if (!isChecked) {
      await this.concludedCheckbox.check();
    }

    // Wait for players_number input to become enabled
    await this.playersNumberInput.waitFor({ state: 'visible' });
    await this.playersNumberInput.fill(playersNumber.toString());

    if (topCut > 0) {
      await this.topNumberSelect.selectOption({ value: topCut.toString() });
    }
  }

  /**
   * Check if a success/info message is visible.
   */
  async hasMessage(text: string): Promise<boolean> {
    try {
      await this.page.locator(`.alert:has-text("${text}")`).waitFor({
        state: 'visible',
        timeout: 5000
      });
      return true;
    } catch {
      return false;
    }
  }
}
