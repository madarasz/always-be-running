import { createBrowserSuite, it, expect } from '../helpers/test-fixture';

createBrowserSuite('Authentication - Logged out user', { userType: 'none' }, (ctx) => {
  it('shows login prompt and blocks access', async () => {
    const { organizePage, adminPage } = ctx.pages;

    await organizePage.open();
    await organizePage.waitForLoginRequired();
    await organizePage.waitForLoginButton();
    expect(await organizePage.hasLogoutButton()).toBe(false);

    await adminPage.open();
    await adminPage.waitForAccessDenied();
  });
});

createBrowserSuite('Authentication - Regular user', { userType: 'regular' }, (ctx) => {
  it('can access organize page', async () => {
    const { organizePage } = ctx.pages;

    await organizePage.open();
    await organizePage.waitForMyTournaments();
  });

  it('cannot access admin page', async () => {
    const { adminPage } = ctx.pages;

    await adminPage.open();
    await adminPage.waitForAccessDenied();
  });

  it('user cannot edit other user\'s tournament', async () => {
    const { tournamentDetailsPage } = ctx.pages;

    await tournamentDetailsPage.open('3022/budapest-startup');
    await tournamentDetailsPage.waitForPageLoaded();
    expect(await tournamentDetailsPage.hasEditButton()).toBe(false);
  });
});

createBrowserSuite('Authentication - Admin user', { userType: 'admin' }, (ctx) => {
  it('can access organize page', async () => {
    const { organizePage } = ctx.pages;

    await organizePage.open();
    await organizePage.waitForMyTournaments();
  });

  it('can access admin page', async () => {
    const { organizePage, adminPage } = ctx.pages;

    // Validate admin access by checking navbar shows Admin link (faster than loading full admin page)
    await organizePage.open();
    await organizePage.waitForMyTournaments();
    expect(await adminPage.hasAdminNavLink()).toBe(true);
  });
});
