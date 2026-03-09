import { defineWorkspace } from 'vitest/config';
import { BASE_URL } from './shared/base-url';

export default defineWorkspace([
  {
    test: {
      name: 'e2e',
      include: ['e2e/**/*.test.ts'],
      globalSetup: ['e2e/setup/global-setup.ts'],
      testTimeout: 120000,
      hookTimeout: 60000,
      env: {
        BASE_URL,
      },
    },
  },
  {
    test: {
      name: 'api',
      include: ['api/**/*.test.ts'],
      testTimeout: 30000,
      env: {
        BASE_URL,
      },
    },
  },
  {
    test: {
      name: 'perf',
      include: ['perf/**/*.perf.test.ts'],
      testTimeout: 180000,
      hookTimeout: 60000,
      env: {
        BASE_URL,
      },
    },
  },
]);
