import type { Page } from 'playwright';

/**
 * Whether to use API mocks. Enabled by default.
 * Set USE_MOCKS=false to run tests against real APIs.
 */
export const USE_MOCKS = process.env.USE_MOCKS !== 'false';

/**
 * Sets up an API mock for the given route pattern.
 * Does nothing if USE_MOCKS is false.
 */
export async function setupApiMock(
  page: Page,
  routePattern: string,
  fixture: unknown
): Promise<void> {
  if (!USE_MOCKS) return;

  await page.route(routePattern, async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(fixture),
    });
  });
}
