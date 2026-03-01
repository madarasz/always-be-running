import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { BrowserManager } from 'agent-browser/dist/browser.js';
import { ProfilePage } from '../pages/ProfilePage';
import { createAuthenticatedBrowser, closeBrowserSafely } from '../helpers/auth';

describe('Profile Page', () => {
  let browser: BrowserManager;
  let profilePage: ProfilePage;

  beforeAll(async () => {
    browser = await createAuthenticatedBrowser('admin');
    profilePage = new ProfilePage(browser);
  });

  afterAll(async () => {
    await closeBrowserSafely(browser);
  });

  it('Admin user: profile page displays all UI sections', async () => {

    // Navigate to profile page via navbar
    await profilePage.open();

    // Verify page header contains "Profile"
    const pageTitle = await profilePage.getPageTitle();
    expect(pageTitle).toContain('Profile');

    // Verify Info tab is active
    expect(await profilePage.isInfoTabActive()).toBe(true);

    // Verify admin alert is shown
    expect(await profilePage.isAdmin()).toBe(true);

    // Verify User info section is present
    expect(await profilePage.hasUserSection()).toBe(true);

    // Verify user counts are displayed (tournaments created, claims, decks)
    const userCounts = await profilePage.getUserCountsText();
    expect(userCounts).toContain('tournament');
    expect(userCounts).toContain('claim');
    expect(userCounts).toContain('deck');

    // Verify Badges section is present
    expect(await profilePage.hasBadgesSection()).toBe(true);

    // Verify Usernames section is present
    expect(await profilePage.hasUsernamesSection()).toBe(true);

    // Note: Claims and Created sections are conditional based on user data
    // They may or may not be present depending on the admin user's activity
  });
});
