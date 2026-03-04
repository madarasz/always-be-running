import { defineWorkspace } from 'vitest/config';

export default defineWorkspace([
  {
    test: {
      name: 'e2e',
      include: ['e2e/tests/**/*.test.ts'],
      testTimeout: 90000,
      hookTimeout: 120000,
      globalSetup: ['./e2e/setup/global-setup.ts'],
    },
  },
  {
    test: {
      name: 'api',
      include: ['api/tests/**/*.test.ts'],
      testTimeout: 30000,
    },
  },
]);
