import { defineConfig } from 'vitest/config'

export default defineConfig({
  test: {
    // Globals enabled (describe, it, expect available without imports)
    globals: true,

    // Use jsdom environment for React component testing
    environment: 'jsdom',

    // Setup file for global test utilities
    setupFiles: ['./src/vitest-setup.ts'],

    // Include patterns for test files
    include: ['src/**/*.{test,spec}.{js,ts,jsx,tsx}'],

    // Coverage configuration with v8 provider
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      reportsDirectory: './coverage',
      include: ['src/**/*.ts', 'src/**/*.tsx'],
      exclude: [
        'src/**/*.test.{ts,tsx}',
        'src/**/*.spec.{ts,tsx}',
        'src/vitest-setup.ts',
        'src/main.jsx',
        'src/vite-env.d.ts',
      ],
      thresholds: {
        branches: 80,
        functions: 80,
        lines: 80,
        statements: 80,
      },
    },

    // Timeouts
    testTimeout: 10000,
    hookTimeout: 10000,

    // Mocking
    clearMocks: true,
    restoreMocks: true,
  },
})
