import { test } from '@playwright/test';

test('Google OAuth flow is blocked without live test credentials', async () => {
  test.fixme(true, 'Blocked: live Google OAuth credentials and an interactive account are not available in this environment.');
});
