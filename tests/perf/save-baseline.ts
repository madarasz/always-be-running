#!/usr/bin/env npx tsx
/**
 * Save performance baseline to JSON file.
 *
 * Usage: npm run test:perf:baseline
 */

import { BrowserManager } from 'agent-browser/dist/browser.js';
import { measureEndpoint, logStats, formatDuration, type PerfStats } from './helpers/perf-client.js';
import { saveBaseline, getGitCommit, type BaselineReport } from './helpers/baseline.js';
import { API_THRESHOLDS, PAGE_THRESHOLDS } from './thresholds.js';
import {
  createAuthenticatedBrowser,
  closeBrowserSafely,
} from '../e2e/helpers/auth.js';

const ITERATIONS = 10;
const BASE_URL = process.env.API_BASE_URL || 'http://localhost:8000';

async function measurePageLoad(
  browser: BrowserManager,
  path: string,
  iterations: number
): Promise<{ mean: number; min: number; max: number; times: number[] }> {
  const page = browser.getPage();
  const url = `${BASE_URL}${path}`;
  const times: number[] = [];

  for (let i = 0; i < iterations; i++) {
    const start = performance.now();
    await page.goto(url, { waitUntil: 'networkidle' });
    const duration = performance.now() - start;
    times.push(duration);
    console.log(`   Iteration ${i + 1}: ${formatDuration(duration)}`);
  }

  const sorted = [...times].sort((a, b) => a - b);
  return {
    mean: times.reduce((a, b) => a + b, 0) / times.length,
    min: sorted[0],
    max: sorted[sorted.length - 1],
    times,
  };
}

async function main() {
  console.log('🚀 Running performance tests for baseline...\n');

  // Measure API endpoints
  const endpoints = Object.keys(API_THRESHOLDS);
  const apiResults: Record<string, PerfStats> = {};

  for (const endpoint of endpoints) {
    console.log(`Testing ${endpoint}...`);
    const stats = await measureEndpoint(endpoint, ITERATIONS);
    logStats(stats);
    apiResults[endpoint] = stats;
  }

  // Measure admin page (requires authenticated browser)
  console.log('\n📄 Testing admin page (requires browser)...\n');
  const pages = Object.keys(PAGE_THRESHOLDS);
  const pageResults: Record<string, { mean: number; min: number; max: number; times: number[] }> = {};

  let browser: BrowserManager | undefined;
  try {
    browser = await createAuthenticatedBrowser('admin');

    for (const path of pages) {
      console.log(`Testing ${path}...`);
      const stats = await measurePageLoad(browser, path, ITERATIONS);
      pageResults[path] = stats;
      console.log(`\n📊 ${path}`);
      console.log(`   Mean: ${formatDuration(stats.mean)}`);
      console.log(`   Min:  ${formatDuration(stats.min)}`);
      console.log(`   Max:  ${formatDuration(stats.max)}`);
    }
  } finally {
    await closeBrowserSafely(browser);
  }

  const report: BaselineReport = {
    timestamp: new Date().toISOString(),
    gitCommit: getGitCommit(),
    endpoints: apiResults,
    pages: pageResults,
  };

  const filename = process.argv[2] || undefined;
  saveBaseline(report, filename);

  console.log('\n✅ Baseline saved successfully!');
}

main().catch((err) => {
  console.error('Error:', err);
  process.exit(1);
});
