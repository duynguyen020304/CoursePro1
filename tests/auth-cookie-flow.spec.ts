import { test, expect } from '@playwright/test';

const frontendUrl = 'http://localhost:5173';
const backendUrl = 'http://localhost:8000';
const studentEmail = 'student@example.com';
const studentPassword = 'password';

async function login(page) {
  await page.goto(`${frontendUrl}/signin`);
  await page.getByLabel('Email address').fill(studentEmail);
  await page.getByLabel('Password').fill(studentPassword);
  await page.getByRole('button', { name: 'Sign in' }).click();
  await expect(page).toHaveURL(`${frontendUrl}/`);
}

test.describe('Cookie Auth Flow', () => {
  test('CSRF bootstrap exposes XSRF cookie only', async ({ page, context }) => {
    const response = await page.goto(`${frontendUrl}/`);
    expect(response?.ok()).toBeTruthy();

    const cookies = await context.cookies();
    const xsrfCookie = cookies.find((cookie) => cookie.name === 'XSRF-TOKEN');

    expect(xsrfCookie).toBeTruthy();
  });

  test('login uses HttpOnly cookies and survives refresh', async ({ page, context }) => {
    await login(page);

    await expect.poll(async () => page.evaluate(() => localStorage.getItem('token'))).toBeNull();
    await expect.poll(async () => page.evaluate(() => localStorage.getItem('user'))).toBeNull();
    await expect.poll(async () => page.evaluate(() => document.cookie.includes('access_token'))).toBeFalsy();
    await expect.poll(async () => page.evaluate(() => document.cookie.includes('refresh_token'))).toBeFalsy();

    const cookies = await context.cookies();
    expect(cookies.find((cookie) => cookie.name === 'access_token' && cookie.httpOnly)).toBeTruthy();
    expect(cookies.find((cookie) => cookie.name === 'refresh_token' && cookie.httpOnly)).toBeTruthy();

    await page.reload();
    await page.goto(`${frontendUrl}/profile`);
    await expect(page).toHaveURL(`${frontendUrl}/profile`);
  });

  test('logout clears auth state and protects routes', async ({ page }) => {
    await login(page);
    await page.getByRole('button', { name: 'Logout' }).click();

    await expect(page).toHaveURL(`${frontendUrl}/signin`);

    await page.goto(`${frontendUrl}/profile`);
    await expect(page).toHaveURL(`${frontendUrl}/signin`);
  });

  test('expired access token refreshes from refresh token', async ({ page, context }) => {
    let refreshSeen = false;

    page.on('response', (response) => {
      if (response.url().includes('/api/auth/refresh')) {
        refreshSeen = true;
      }
    });

    await login(page);

    const cookies = await context.cookies();
    const accessCookie = cookies.find((cookie) => cookie.name === 'access_token');
    const refreshCookie = cookies.find((cookie) => cookie.name === 'refresh_token');

    expect(accessCookie).toBeTruthy();
    expect(refreshCookie).toBeTruthy();

    await context.clearCookies();
    await context.addCookies([
      {
        name: refreshCookie.name,
        value: refreshCookie.value,
        domain: refreshCookie.domain,
        path: refreshCookie.path,
        expires: refreshCookie.expires,
        httpOnly: refreshCookie.httpOnly,
        secure: refreshCookie.secure,
        sameSite: refreshCookie.sameSite,
      },
    ]);

    await page.goto(`${frontendUrl}/profile`);
    await expect(page).toHaveURL(`${frontendUrl}/profile`);
    expect(refreshSeen).toBeTruthy();
  });
});
