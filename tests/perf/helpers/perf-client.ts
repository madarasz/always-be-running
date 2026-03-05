/**
 * HTTP client with timing measurement for performance tests.
 */

const BASE_URL = process.env.API_BASE_URL || 'http://localhost:8000';

export interface TimingResult {
  url: string;
  status: number;
  duration: number;  // milliseconds
  timestamp: number; // Unix timestamp
}

export interface PerfStats {
  url: string;
  iterations: number;
  times: number[];
  mean: number;
  min: number;
  max: number;
  p75: number;
  p95: number;
}

/**
 * Make a single timed HTTP request.
 */
export async function timedFetch(
  path: string,
  options: RequestInit = {}
): Promise<TimingResult> {
  const url = `${BASE_URL}${path}`;
  const start = performance.now();

  const response = await fetch(url, {
    ...options,
    headers: {
      Accept: 'application/json',
      ...options.headers,
    },
  });

  const duration = performance.now() - start;

  // Consume the response body to ensure full transfer time is measured
  await response.text();

  return {
    url: path,
    status: response.status,
    duration,
    timestamp: Date.now(),
  };
}

/**
 * Run multiple iterations of a request and calculate statistics.
 * Includes a warmup request that's excluded from stats.
 */
export async function measureEndpoint(
  path: string,
  iterations: number = 3,
  options: RequestInit = {}
): Promise<PerfStats> {
  // Warmup request (excluded from stats)
  await timedFetch(path, options);

  // Measured iterations
  const times: number[] = [];
  for (let i = 0; i < iterations; i++) {
    const result = await timedFetch(path, options);
    times.push(result.duration);
  }

  // Sort for percentile calculations
  const sorted = [...times].sort((a, b) => a - b);

  return {
    url: path,
    iterations,
    times,
    mean: times.reduce((a, b) => a + b, 0) / times.length,
    min: sorted[0],
    max: sorted[sorted.length - 1],
    p75: percentile(sorted, 75),
    p95: percentile(sorted, 95),
  };
}

function percentile(sorted: number[], p: number): number {
  const index = Math.ceil((p / 100) * sorted.length) - 1;
  return sorted[Math.max(0, index)];
}

/**
 * Format duration for display.
 */
export function formatDuration(ms: number): string {
  if (ms < 1000) {
    return `${Math.round(ms)}ms`;
  }
  return `${(ms / 1000).toFixed(2)}s`;
}

/**
 * Log performance stats in a readable format.
 */
export function logStats(stats: PerfStats): void {
  console.log(`\n📊 ${stats.url}`);
  console.log(`   Iterations: ${stats.iterations}`);
  console.log(`   Mean: ${formatDuration(stats.mean)}`);
  console.log(`   Min:  ${formatDuration(stats.min)}`);
  console.log(`   Max:  ${formatDuration(stats.max)}`);
  console.log(`   P75:  ${formatDuration(stats.p75)}`);
  console.log(`   P95:  ${formatDuration(stats.p95)}`);
}
