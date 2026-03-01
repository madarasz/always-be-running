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
    // Wait for Vue to initialize the form (prize kit loads via AJAX)
    await this.page.waitForTimeout(500);
  }

  /**
   * Navigate to a tournament's view page.
   */
  async open(tournamentId: number) {
    await this.navigate(`/tournaments/${tournamentId}`);
    await this.tournamentTitle.waitFor({ state: 'visible' });
  }

  /**
   * Navigate to a tournament's edit page.
   */
  async openEdit(tournamentId: number) {
    await this.navigate(`/tournaments/${tournamentId}/edit`);
    await this.submitButton.waitFor({ state: 'visible' });
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
    await this.locationSearchInput.fill(query);

    const autocompleteContainer = this.page.locator('.pac-container');
    await autocompleteContainer.waitFor({ state: 'visible', timeout: 10000 });

    const firstSuggestion = this.page.locator('.pac-item').first();
    await firstSuggestion.click();

    await this.page.waitForTimeout(500);
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
    await this.submitButton.click();
    await this.page.waitForLoadState('domcontentloaded');
    await this.page.waitForTimeout(500);

    const newUrl = this.getUrl();
    if (newUrl === currentUrl || newUrl.includes('/create')) {
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
    await this.editButton.click();
    await this.submitButton.waitFor({ state: 'visible' });
  }

  /**
   * Click the delete button and confirm the dialog.
   */
  async deleteTournament() {
    this.page.once('dialog', async dialog => {
      await dialog.accept();
    });
    await this.deleteButton.click();
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

    await this.page.waitForTimeout(300);
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
