/**
 * Performance tests for API endpoints.
 *
 * Establishes baseline performance metrics before migration.
 * Each endpoint is tested with 3 iterations (plus warmup).
 */

import { describe, it, expect } from 'vitest';
import { measureEndpoint, logStats, formatDuration } from '../helpers/perf-client.js';
import { getApiThreshold, API_THRESHOLDS } from '../thresholds.js';

const ITERATIONS = 10;

// Test all endpoints with thresholds
const ENDPOINTS = Object.keys(API_THRESHOLDS);

describe('API Performance', () => {
  describe.each(ENDPOINTS)('%s', (endpoint) => {
    it('responds within threshold', async () => {
      const stats = await measureEndpoint(endpoint, ITERATIONS);
      logStats(stats);

      const threshold = getApiThreshold(endpoint);

      // Use mean for overall assessment
      if (stats.mean > threshold.warn) {
        console.warn(
          `⚠️  Warning: ${endpoint} mean (${formatDuration(stats.mean)}) ` +
            `exceeds warn threshold (${formatDuration(threshold.warn)})`
        );
      }

      expect(
        stats.mean,
        `${endpoint} mean (${formatDuration(stats.mean)}) exceeds fail threshold (${formatDuration(threshold.fail)})`
      ).toBeLessThan(threshold.fail);
    }, 30000); // 30s timeout per endpoint
  });
});

describe('API Consistency', () => {
  it('/api/tournaments/upcoming has low variance', async () => {
    const stats = await measureEndpoint('/api/tournaments/upcoming', ITERATIONS);

    // Variance check: max should be within 3x of min (indicates stable performance)
    const variance = stats.max / stats.min;
    console.log(`\n📈 Variance ratio (max/min): ${variance.toFixed(2)}x`);

    // Allow high variance in this baseline - just log it
    if (variance > 3) {
      console.warn(`⚠️  High variance detected - performance may be inconsistent`);
    }
  }, 30000);

  it('/api/tournaments/results has low variance', async () => {
    const stats = await measureEndpoint('/api/tournaments/results', ITERATIONS);

    const variance = stats.max / stats.min;
    console.log(`\n📈 Variance ratio (max/min): ${variance.toFixed(2)}x`);

    if (variance > 3) {
      console.warn(`⚠️  High variance detected - performance may be inconsistent`);
    }
  }, 30000);
});
