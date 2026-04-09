import { test, expect, type Dialog, type Locator, type Page } from '@playwright/test';

const adminEmail = 'admin@example.com';
const adminPassword = 'password';

async function expectVisible(locator: Locator) {
  await expect(locator).toBeVisible({ timeout: 10000 });
}

async function loginAsAdmin(page: Page) {
  await page.goto('/signin');
  await page.waitForLoadState('domcontentloaded');

  if (!page.url().endsWith('/signin')) {
    return;
  }

  await expect(page.getByRole('heading', { name: 'Sign in to your account' })).toBeVisible({
    timeout: 15000,
  });
  await page.getByLabel('Email address').fill(adminEmail);
  await page.getByLabel('Password').fill(adminPassword);
  await page.getByRole('button', { name: 'Sign in' }).click();
  await expect(page).toHaveURL('/', { timeout: 15000 });
}

async function openAdminRoute(page: Page, path: string) {
  await page.goto(path);
  await expect(page).toHaveURL(path, { timeout: 15000 });
}

async function expectAdminShell(page: Page) {
  await expectVisible(page.getByRole('heading', { name: 'Administration' }));
  await expectVisible(page.locator('aside').getByRole('heading', { name: 'Admin Panel' }));
  await expectVisible(page.locator('aside').getByRole('link', { name: 'Dashboard' }));
  await expectVisible(page.locator('aside').getByRole('link', { name: 'Courses' }));
  await expectVisible(page.locator('aside').getByRole('link', { name: 'Users' }));
  await expectVisible(page.locator('aside').getByRole('link', { name: 'Roles & Permissions' }));
  await expectVisible(page.locator('aside').getByRole('link', { name: 'Revenue' }));
  await expectVisible(page.getByRole('button', { name: 'Logout' }));
}

async function dismissDialog(dialog: Dialog) {
  await dialog.dismiss();
}

test.describe('Admin E2E tests', () => {
  test.describe.configure({ mode: 'serial' });

  let createdRoleName = '';
  let editedRoleName = '';

  test('phase 1: admin can sign in and open the admin shell', async ({ page, context }) => {
    await loginAsAdmin(page);

    const cookies = await context.cookies();
    expect(cookies.some((cookie) => cookie.name === 'access_token')).toBe(true);
    expect(cookies.some((cookie) => cookie.name === 'refresh_token')).toBe(true);

    await openAdminRoute(page, '/admin/dashboard');
    await expectAdminShell(page);
  });

  test('phase 2: admin dashboard surfaces overview, implemented quick links, and notifications', async ({
    page,
  }) => {
    await loginAsAdmin(page);
    await openAdminRoute(page, '/admin/dashboard');

    await expectAdminShell(page);
    await expectVisible(page.getByRole('heading', { name: 'Dashboard Overview' }));
    await expectVisible(page.getByText('Quick Links'));
    await expectVisible(page.getByText('Total Users'));
    await expectVisible(page.getByText('Total Courses'));
    await expectVisible(page.getByText('Total Orders'));
    await expectVisible(page.getByText('Total Revenue'));

    const dashboardLinks = [
      { name: /Manage Courses/i, path: '/admin/courses' },
      { name: /Manage Users/i, path: '/admin/users' },
      { name: /View Revenue/i, path: '/admin/revenue' },
      { name: /Upload Video/i, path: '/admin/upload-video' },
    ];

    for (const link of dashboardLinks) {
      await page.goto('/admin/dashboard');
      await page.getByRole('link', { name: link.name }).first().click();
      await expect(page).toHaveURL(link.path, { timeout: 15000 });
    }

    await page.goto('/admin/dashboard');
    const dismissButtons = page.locator('button', { hasText: 'x' });
    const dismissCount = await dismissButtons.count();
    if (dismissCount > 0) {
      const firstNotice = dismissButtons.first().locator('xpath=ancestor::div[contains(@class, "rounded-lg")]');
      await dismissButtons.first().click();
      await expect(firstNotice).toBeHidden({ timeout: 10000 });
    }
  });

  test('phase 3: admin can browse course management', async ({ page }) => {
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

  test('phase 4: admin can search users and safely cancel destructive delete', async ({ page }) => {
    await loginAsAdmin(page);
    await openAdminRoute(page, '/admin/users');

    await expectVisible(page.getByRole('heading', { name: 'User Management' }));
    const searchInput = page.getByPlaceholder('Search users...');
    await expectVisible(searchInput);

    await searchInput.fill('admin@example.com');
    const adminRow = page.locator('tbody tr').filter({ hasText: 'admin@example.com' }).first();
    await expectVisible(adminRow);

    page.once('dialog', dismissDialog);
    await adminRow.getByRole('button', { name: 'Delete' }).click();
    await expectVisible(page.locator('tbody tr').filter({ hasText: 'admin@example.com' }).first());

    await searchInput.fill('no-such-admin-user');
    await expect(page.locator('tbody tr')).toHaveCount(0);
  });

  test('phase 5: admin can create, edit, and delete a disposable role', async ({ page }) => {
    createdRoleName = `admin-e2e-role-${Date.now()}`;
    editedRoleName = `${createdRoleName}-edited`;

    await loginAsAdmin(page);
    await openAdminRoute(page, '/admin/roles');

    await expectVisible(page.getByRole('heading', { name: 'Role Management' }));
    await page.getByRole('button', { name: 'Create Role' }).click();

    const roleDialog = page.locator('div[role="dialog"], .relative.z-50.inline-block').last();
    await expectVisible(roleDialog.getByRole('heading', { name: 'Create Role' }));
    await roleDialog.getByLabel('Role Name').fill(createdRoleName);
    await roleDialog.getByRole('checkbox').first().check({ force: true });
    await roleDialog.getByRole('button', { name: 'Save' }).click();

    const createdRow = page.locator('tbody tr', { hasText: createdRoleName }).first();
    await expectVisible(createdRow);

    await createdRow.getByRole('button', { name: 'Edit' }).click();
    const editDialog = page.locator('div[role="dialog"], .relative.z-50.inline-block').last();
    await expectVisible(editDialog.getByRole('heading', { name: 'Edit Role' }));
    await editDialog.getByLabel('Role Name').fill(editedRoleName);
    await editDialog.getByRole('button', { name: 'Save' }).click();

    const editedRow = page.locator('tbody tr', { hasText: editedRoleName }).first();
    await expectVisible(editedRow);

    page.once('dialog', async (dialog) => {
      await dialog.accept();
    });
    await editedRow.getByRole('button', { name: 'Delete' }).click();
    await expect(page.locator('tbody')).not.toContainText(editedRoleName, { timeout: 15000 });
  });

  test('phase 6: admin can validate revenue analytics filters', async ({ page }) => {
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

    await expect(
      page.getByText(/end date must be greater than or equal to start date/i)
    ).toBeVisible({ timeout: 10000 });
  });

  test('phase 7: admin can traverse upload video selectors and sees honest upload feedback', async ({
    page,
  }) => {
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
    await expect.poll(async () => chapterSelect.locator('option').count()).toBeGreaterThan(1);
    await chapterSelect.selectOption({ index: 1 });

    const lessonSelect = page.locator('select').nth(2);
    await expect.poll(async () => lessonSelect.locator('option').count()).toBeGreaterThan(1);
    await lessonSelect.selectOption({ index: 1 });

    await page.getByPlaceholder('Enter video title').fill(`Admin E2E Upload ${Date.now()}`);
    await page.locator('input[type="file"]').setInputFiles({
      name: 'admin-e2e-video.mp4',
      mimeType: 'video/mp4',
      buffer: Buffer.from('admin-e2e-video'),
    });
    await page.getByRole('button', { name: 'Upload Video' }).click();

    await expect(
      page.getByText(/File-based video upload is not available yet/i)
    ).toBeVisible({ timeout: 10000 });
  });
});
