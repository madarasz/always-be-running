import { defineWorkspace } from 'vitest/config';

export default defineWorkspace([
  {
    test: {
      name: 'e2e',
      include: ['e2e/**/*.test.ts'],
      globalSetup: ['e2e/setup/global-setup.ts'],
      testTimeout: 120000,
      hookTimeout: 60000,
    },
  },
  {
    test: {
      name: 'api',
      include: ['api/**/*.test.ts'],
      testTimeout: 30000,
    },
  },
  {
    test: {
      name: 'perf',
      include: ['perf/**/*.perf.test.ts'],
      testTimeout: 180000,
      hookTimeout: 60000,
    },
  },
]);
