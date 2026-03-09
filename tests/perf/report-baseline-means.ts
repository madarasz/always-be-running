#!/usr/bin/env npx tsx
/**
 * Print mean values from all baseline JSON reports.
 *
 * Usage:
 *   npm run test:perf:means
 *   npm run test:perf:means -- ./perf/reports
 */

import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

interface MetricWithMean {
  mean: number;
}

interface BaselineReport {
  timestamp?: string;
  endpoints?: Record<string, MetricWithMean>;
  pages?: Record<string, MetricWithMean>;
}

interface LoadedReport {
  fileName: string;
  report: BaselineReport;
}

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const defaultReportsDir = path.resolve(__dirname, 'reports');
const reportsDir = path.resolve(process.argv[2] ?? defaultReportsDir);

async function loadReports(directory: string): Promise<LoadedReport[]> {
  const entries = await fs.readdir(directory, { withFileTypes: true });

  const baselineFiles = entries
    .filter((entry) => entry.isFile() && /^baseline-.*\.json$/i.test(entry.name))
    .map((entry) => entry.name)
    .sort((a, b) => a.localeCompare(b));

  const reports: LoadedReport[] = [];

  for (const fileName of baselineFiles) {
    const filePath = path.join(directory, fileName);
    const raw = await fs.readFile(filePath, 'utf-8');
    const report = JSON.parse(raw) as BaselineReport;

    reports.push({ fileName, report });
  }

  return reports;
}

function printSection(title: string, reports: LoadedReport[], section: 'endpoints' | 'pages'): void {
  const keys = new Set<string>();

  for (const { report } of reports) {
    const metrics = report[section] ?? {};
    for (const key of Object.keys(metrics)) {
      keys.add(key);
    }
  }

  const sortedKeys = [...keys].sort((a, b) => a.localeCompare(b));

  console.log(`\n${title}`);
  console.log('='.repeat(title.length));

  if (sortedKeys.length === 0) {
    console.log('No data found.');
    return;
  }

  for (const metricName of sortedKeys) {
    console.log(`\n${metricName}`);

    for (const { fileName, report } of reports) {
      const mean = report[section]?.[metricName]?.mean;
      const meanText = typeof mean === 'number' ? `${mean.toFixed(3)} ms` : 'N/A';
      console.log(`  ${fileName}: ${meanText}`);
    }
  }
}

async function main(): Promise<void> {
  const reports = await loadReports(reportsDir);

  if (reports.length === 0) {
    console.error(`No baseline files found in ${reportsDir}`);
    process.exit(1);
  }

  console.log(`Found ${reports.length} baseline file(s) in ${reportsDir}`);
  console.log('\nBaselines:');
  for (const { fileName, report } of reports) {
    const label = report.timestamp ? `${fileName} (${report.timestamp})` : fileName;
    console.log(`- ${label}`);
  }

  printSection('Endpoint means', reports, 'endpoints');
  printSection('Page means', reports, 'pages');
}

main().catch((error) => {
  console.error('Failed to generate mean report:', error);
  process.exit(1);
});
