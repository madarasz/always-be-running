import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    include: ['tests/**/*.test.ts'],
    testTimeout: 90000,
    hookTimeout: 90000,
    reporters: ['verbose'],
  },
});
