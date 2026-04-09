import { test, expect, type Locator, type Page } from '@playwright/test';

const frontendUrl = 'http://localhost:5173';

const adminEmail = 'admin@example.com';
const adminPassword = 'password';

async function expectVisible(locator: Locator) {
  await expect(locator).toBeVisible({ timeout: 10000 });
}

async function loginAsAdmin(page: Page) {
  await page.goto(`${frontendUrl}/signin`);
  await page.waitForLoadState('domcontentloaded');

  if (!page.url().endsWith('/signin')) {
    return;
  }

  await expect(page.getByLabel('Email address')).toBeVisible({ timeout: 15000 });
  await page.getByLabel('Email address').fill(adminEmail);
  await page.getByLabel('Password').fill(adminPassword);
  await page.getByRole('button', { name: 'Sign in' }).click();

  await expect(page).toHaveURL(`${frontendUrl}/`, { timeout: 15000 });
}

async function openAdminRoute(page: Page, path: string) {
  await page.goto(`${frontendUrl}${path}`);
  await expect(page).toHaveURL(`${frontendUrl}${path}`, { timeout: 15000 });
}

test.describe('Admin smoke tests', () => {
  test.describe.configure({ mode: 'serial' });

  let createdRoleName = '';

  test('phase 1: admin can log in and open the admin shell', async ({ page }) => {
    await loginAsAdmin(page);
    await openAdminRoute(page, '/admin/dashboard');

    await expectVisible(page.getByRole('heading', { name: 'Administration' }));
    await expectVisible(page.locator('aside').getByRole('heading', { name: 'Admin Panel' }));
    await expectVisible(page.locator('aside').getByRole('link', { name: 'Dashboard' }));
    await expectVisible(page.locator('aside').getByRole('link', { name: 'Courses' }));
    await expectVisible(page.locator('aside').getByRole('link', { name: 'Users' }));
    await expectVisible(page.locator('aside').getByRole('link', { name: 'Roles & Permissions' }));
    await expectVisible(page.locator('aside').getByRole('link', { name: 'Revenue' }));
    await expectVisible(page.getByRole('button', { name: 'Logout' }));
  });

  test('phase 2: admin can open dashboard overview and quick links', async ({ page }) => {
    await loginAsAdmin(page);
    await openAdminRoute(page, '/admin/dashboard');

    await expectVisible(page.getByRole('heading', { name: 'Dashboard Overview' }));
    await expectVisible(page.getByText('Quick Links'));
    await expectVisible(page.getByText('Total Users'));
    await expectVisible(page.getByText('Total Courses'));
    await expectVisible(page.getByText('Total Orders'));
    await expectVisible(page.getByText('Total Revenue'));
    await expectVisible(page.getByRole('link', { name: /Manage Courses/i }).first());
    await expectVisible(page.getByRole('link', { name: /Manage Users/i }).first());
    await expectVisible(page.getByRole('link', { name: /View Revenue/i }).first());
    await expectVisible(page.getByRole('link', { name: /Upload Video/i }).first());
  });

  test('phase 3: admin can open course management', async ({ page }) => {
    await loginAsAdmin(page);
    await openAdminRoute(page, '/admin/courses');

    await expectVisible(page.getByRole('heading', { name: 'Course Management' }));
    await expectVisible(page.getByRole('columnheader', { name: 'Course' }));
    await expectVisible(page.getByRole('columnheader', { name: 'Instructor' }));
    await expectVisible(page.getByRole('columnheader', { name: 'Price' }));
    await expectVisible(page.getByRole('columnheader', { name: 'Status' }));
    await expectVisible(page.getByRole('columnheader', { name: 'Actions' }));

    const rows = page.locator('tbody tr');
    await expect(rows.first()).toBeVisible({ timeout: 10000 });
    expect(await rows.count()).toBeGreaterThan(0);
  });

  test('phase 4: admin can search user management', async ({ page }) => {
    await loginAsAdmin(page);
    await openAdminRoute(page, '/admin/users');

    await expectVisible(page.getByRole('heading', { name: 'User Management' }));
    const searchInput = page.getByPlaceholder('Search users...');
    await expectVisible(searchInput);
    await expectVisible(page.getByRole('columnheader', { name: 'User' }));
    await expectVisible(page.getByRole('columnheader', { name: 'Email' }));
    await expectVisible(page.getByRole('columnheader', { name: 'Role' }));

    await searchInput.fill('admin@example.com');
    await expectVisible(page.locator('tbody tr').filter({ hasText: 'admin@example.com' }).first());

    await searchInput.fill('no-such-user-smoke');
    await expect(page.locator('tbody tr')).toHaveCount(0);
  });

  test('phase 5: admin can create and delete a custom role', async ({ page }) => {
    createdRoleName = `smoke-role-${Date.now()}`;

    await loginAsAdmin(page);
    await openAdminRoute(page, '/admin/roles');

    await expectVisible(page.getByRole('heading', { name: 'Role Management' }));
    await page.getByRole('button', { name: 'Create Role' }).click();

    const roleDialog = page.locator('.inline-block.align-bottom.bg-white').last();
    await expectVisible(roleDialog.getByRole('heading', { name: 'Create Role' }));
    const createRoleNameInput = roleDialog.getByLabel('Role Name');
    await createRoleNameInput.fill(createdRoleName);
    await roleDialog.getByRole('checkbox', { name: 'Admin Access' }).check({ force: true });
    await roleDialog.getByRole('button', { name: 'Save' }).click({ force: true });

    await expect(page.locator('tbody')).toContainText(createdRoleName, { timeout: 15000 });
    const createdRow = page.locator('tbody tr', { hasText: createdRoleName }).first();
    await createdRow.scrollIntoViewIfNeeded();

    page.once('dialog', async (dialog) => {
      await dialog.accept();
    });
    await createdRow.getByRole('button', { name: 'Delete' }).click();

    await expect(page.locator('tbody')).not.toContainText(createdRoleName, { timeout: 15000 });
  });

  test('phase 6: admin can view revenue analytics and validation states', async ({ page }) => {
    await loginAsAdmin(page);
    await openAdminRoute(page, '/admin/revenue');

    await expectVisible(page.getByRole('heading', { name: 'Revenue Analytics' }));
    await expectVisible(page.getByText('Total Revenue'));
    await expectVisible(page.getByText('Monthly Revenue'));
    await expectVisible(page.getByText('Total Orders'));
    await expectVisible(page.getByText('Avg Order Value'));
    await expectVisible(page.getByText('Top Courses by Revenue'));
    await expectVisible(page.getByText('Recent Transactions'));

    const dateInputs = page.locator('input[type="date"]');
    await expect(dateInputs).toHaveCount(2);
    await dateInputs.nth(0).fill('2026-12-31');
    await dateInputs.nth(1).fill('2026-01-01');

    await expect(page.getByText(/end date must be greater than or equal to start date/i)).toBeVisible({
      timeout: 10000,
    });
  });

  test('phase 7: admin can open upload video and see current unsupported upload behavior', async ({ page }) => {
    await loginAsAdmin(page);
    await openAdminRoute(page, '/admin/upload-video');

    await expectVisible(page.getByRole('heading', { name: 'Upload Video' }));
    await expectVisible(page.getByText('Select Course *'));
    await expectVisible(page.getByText('Select Chapter *'));
    await expectVisible(page.getByText('Select Lesson *'));
    await expectVisible(page.getByText('Video File *'));

    const courseSelect = page.locator('select').nth(0);
    await courseSelect.selectOption({ index: 1 });

    const chapterSelect = page.locator('select').nth(1);
    await expect.poll(async () => await chapterSelect.locator('option').count()).toBeGreaterThan(1);
    await chapterSelect.selectOption({ index: 1 });

    const lessonSelect = page.locator('select').nth(2);
    await expect.poll(async () => await lessonSelect.locator('option').count()).toBeGreaterThan(1);
    await lessonSelect.selectOption({ index: 1 });

    await page.getByPlaceholder('Enter video title').fill(`Smoke Upload ${Date.now()}`);
    await page.locator('input[type="file"]').setInputFiles({
      name: 'smoke-video.mp4',
      mimeType: 'video/mp4',
      buffer: Buffer.from('smoke-video'),
    });
    await page.getByRole('button', { name: 'Upload Video' }).click();

    await expect(page.getByText(/File-based video upload is not available yet/i)).toBeVisible({ timeout: 10000 });
  });
});
