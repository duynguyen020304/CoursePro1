import { test, expect } from '@playwright/test';

const frontendUrl = 'http://localhost:5173';
const studentEmail = 'student@example.com';
const studentPassword = 'password';

async function login(page) {
  await page.goto(`${frontendUrl}/signin`);
  await page.getByLabel('Email address').fill(studentEmail);
  await page.getByLabel('Password').fill(studentPassword);
  await page.getByRole('button', { name: 'Sign in' }).click();
  await expect(page).toHaveURL(`${frontendUrl}/`);
}

async function openFirstCourse(page) {
  await page.goto(`${frontendUrl}/courses`);
  const firstCourse = page.locator('a[href^="/courses/"]').first();
  await expect(firstCourse).toBeVisible();
  await firstCourse.click();
}

test('missing XSRF cookie recovers once after a 419', async ({ page, context }) => {
  const responses = [];

  page.on('response', (response) => {
    const url = response.url();
    if (url.includes('/api/cart/items') || url.includes('/sanctum/csrf-cookie')) {
      responses.push({ url, status: response.status() });
    }
  });

  await login(page);

  const cookies = await context.cookies();
  const preservedCookies = cookies.filter((cookie) => cookie.name !== 'XSRF-TOKEN');

  await context.clearCookies();
  await context.addCookies(preservedCookies);

  await openFirstCourse(page);
  await page.getByRole('button', { name: /add to cart/i }).click();

  await expect.poll(async () => responses.some((entry) => entry.url.includes('/sanctum/csrf-cookie') && entry.status === 204)).toBeTruthy();
  expect(responses.filter((entry) => entry.url.includes('/api/cart/items') && entry.status === 419)).toHaveLength(1);
});
