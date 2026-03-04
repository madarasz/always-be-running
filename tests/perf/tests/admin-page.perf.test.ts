/**
 * Performance test for admin page.
 *
 * The admin page is known to be slow due to:
 * - Synchronous external HTTP call to alwaysberunning.net/ktm/metas.json
 * - N+1 queries for VIP users (communityCount() method)
 * - Multiple separate COUNT queries
 *
 * This test establishes a baseline before migration.
 */

import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { BrowserManager } from 'agent-browser/dist/browser.js';
import {
  createAuthenticatedBrowser,
  closeBrowserSafely,
  CHROME_PATH,
} from '../../e2e/helpers/auth.js';
import { getPageThreshold } from '../thresholds.js';
import { formatDuration } from '../helpers/perf-client.js';

const BASE_URL = process.env.API_BASE_URL || 'http://localhost:8000';
const ITERATIONS = 10;

interface PageTiming {
  url: string;
  duration: number;
}

async function measurePageLoad(
  browser: BrowserManager,
  path: string
): Promise<PageTiming> {
  const page = browser.getPage();
  const url = `${BASE_URL}${path}`;

  const start = performance.now();

  await page.goto(url, { waitUntil: 'networkidle' });

  const duration = performance.now() - start;

  return { url: path, duration };
}

describe('Admin Page Performance', () => {
  let browser: BrowserManager;

  beforeAll(async () => {
    // Use authenticated admin session
    browser = await createAuthenticatedBrowser('admin');
  }, 60000);

  afterAll(async () => {
    await closeBrowserSafely(browser);
  });

  it('loads within threshold', async () => {
    const times: number[] = [];

    for (let i = 0; i < ITERATIONS; i++) {
      const timing = await measurePageLoad(browser, '/admin');
      times.push(timing.duration);
      console.log(`\n📊 /admin (iteration ${i + 1}): ${formatDuration(timing.duration)}`);
    }

    const mean = times.reduce((a, b) => a + b, 0) / times.length;
    const min = Math.min(...times);
    const max = Math.max(...times);

    console.log(`\n📊 /admin Summary`);
    console.log(`   Iterations: ${ITERATIONS}`);
    console.log(`   Mean: ${formatDuration(mean)}`);
    console.log(`   Min:  ${formatDuration(min)}`);
    console.log(`   Max:  ${formatDuration(max)}`);

    const threshold = getPageThreshold('/admin');

    if (mean > threshold.warn) {
      console.warn(
        `⚠️  Warning: /admin mean (${formatDuration(mean)}) ` +
          `exceeds warn threshold (${formatDuration(threshold.warn)})`
      );
    }

    expect(
      mean,
      `/admin mean (${formatDuration(mean)}) exceeds fail threshold (${formatDuration(threshold.fail)})`
    ).toBeLessThan(threshold.fail);
  }, 120000); // 2 minutes - admin page can be very slow

  it('renders admin stats content', async () => {
    const page = browser.getPage();

    // Navigate to admin page
    await page.goto(`${BASE_URL}/admin`, { waitUntil: 'networkidle' });

    // Verify key admin sections are rendered
    const content = await page.content();

    // Check for expected admin page elements
    const hasUserCount = content.includes('user') || content.includes('User');
    const hasTournamentCount =
      content.includes('tournament') || content.includes('Tournament');

    expect(hasUserCount || hasTournamentCount).toBe(true);
  }, 60000);
});
