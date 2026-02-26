import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    include: ['tests/**/*.test.ts'],
    testTimeout: 90000,
    hookTimeout: 120000,
    reporters: ['verbose'],
    globalSetup: ['./setup/global-setup.ts'],
  },
});
