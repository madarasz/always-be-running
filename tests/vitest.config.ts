import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    include: ['e2e/tests/**/*.test.ts'],
    testTimeout: 90000,
    hookTimeout: 120000,
    reporters: ['verbose'],
    globalSetup: ['./e2e/setup/global-setup.ts'],
  },
});
