/**
 * Utilities for saving and comparing performance baselines.
 */

import { writeFileSync, readFileSync, existsSync, mkdirSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { PerfStats } from './perf-client.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

export const REPORTS_DIR = join(__dirname, '../reports');

export interface BaselineReport {
  timestamp: string;
  gitCommit?: string;
  endpoints: Record<string, PerfStats>;
  pages?: Record<string, { mean: number; min: number; max: number; times?: number[] }>;
}

/**
 * Save a baseline report to JSON file.
 */
export function saveBaseline(report: BaselineReport, filename?: string): string {
  mkdirSync(REPORTS_DIR, { recursive: true });

  const name = filename || `baseline-${new Date().toISOString().split('T')[0]}.json`;
  const path = join(REPORTS_DIR, name);

  writeFileSync(path, JSON.stringify(report, null, 2));
  console.log(`\n📁 Baseline saved to: ${path}`);

  return path;
}

/**
 * Load a baseline report from JSON file.
 */
export function loadBaseline(filename: string): BaselineReport | null {
  const path = join(REPORTS_DIR, filename);
  if (!existsSync(path)) {
    return null;
  }

  return JSON.parse(readFileSync(path, 'utf-8'));
}

/**
 * Get the current git commit hash (if available).
 */
export function getGitCommit(): string | undefined {
  try {
    const { execSync } = require('child_process');
    return execSync('git rev-parse --short HEAD', { encoding: 'utf-8' }).trim();
  } catch {
    return undefined;
  }
}

/**
 * Compare two baselines and report differences.
 */
export function compareBaselines(
  current: BaselineReport,
  previous: BaselineReport
): void {
  console.log('\n📊 Performance Comparison');
  console.log(`   Current: ${current.timestamp} (${current.gitCommit || 'unknown'})`);
  console.log(`   Previous: ${previous.timestamp} (${previous.gitCommit || 'unknown'})`);
  console.log('');

  for (const [endpoint, stats] of Object.entries(current.endpoints)) {
    const prevStats = previous.endpoints[endpoint];
    if (!prevStats) {
      console.log(`   ${endpoint}: NEW (no previous data)`);
      continue;
    }

    const diff = stats.mean - prevStats.mean;
    const pctChange = ((diff / prevStats.mean) * 100).toFixed(1);
    const indicator = diff > 0 ? '🔴' : diff < 0 ? '🟢' : '⚪';

    console.log(
      `   ${indicator} ${endpoint}: ` +
        `${stats.mean.toFixed(0)}ms (was ${prevStats.mean.toFixed(0)}ms, ${pctChange > '0' ? '+' : ''}${pctChange}%)`
    );
  }
}
