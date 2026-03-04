/**
 * Performance thresholds for API endpoints and pages.
 *
 * - warn: Yellow flag - investigate if consistently exceeded
 * - fail: Test failure - regression detected
 */

export interface Threshold {
  warn: number;  // milliseconds
  fail: number;  // milliseconds
}

export const API_THRESHOLDS: Record<string, Threshold> = {
  '/api/tournaments/upcoming': { warn: 2000, fail: 5000 },
  '/api/tournaments/results': { warn: 2500, fail: 6000 },
  '/api/tournaments': { warn: 3000, fail: 8000 },
  '/api/adminstats': { warn: 3000, fail: 10000 },
  '/api/entries': { warn: 2000, fail: 5000 },
  '/api/prizes': { warn: 3000, fail: 8000 },
  '/api/videos': { warn: 2000, fail: 5000 },
  '/api/artists': { warn: 2000, fail: 5000 },
};

export const PAGE_THRESHOLDS: Record<string, Threshold> = {
  '/admin': { warn: 5000, fail: 15000 },
};

export function getApiThreshold(endpoint: string): Threshold {
  return API_THRESHOLDS[endpoint] || { warn: 3000, fail: 10000 };
}

export function getPageThreshold(path: string): Threshold {
  return PAGE_THRESHOLDS[path] || { warn: 5000, fail: 15000 };
}
