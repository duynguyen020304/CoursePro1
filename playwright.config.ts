import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  timeout: 30000,
  expect: {
    timeout: 5000
  },
  use: {
    baseURL: 'http://localhost:5173',
    browserName: 'chromium',
    headless: true,
  },
  projects: [
    {
      name: 'chromium',
      use: { browserName: 'chromium' }
    }
  ]
});
