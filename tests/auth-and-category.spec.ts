import { test, expect } from '@playwright/test';

test.describe('Auth Guard Tests', () => {
  test('should allow unauthenticated user to access signin page', async ({ page }) => {
    await page.goto('http://localhost:5173/signin');
    await expect(page).toHaveURL('http://localhost:5173/signin');
    await expect(page.locator('h2')).toContainText('Sign in to your account');
  });

  test('should allow unauthenticated user to access signup page', async ({ page }) => {
    await page.goto('http://localhost:5173/signup');
    await expect(page).toHaveURL('http://localhost:5173/signup');
    await expect(page.locator('h2')).toContainText('Create your account');
  });
});

test.describe('Category Slug Routing Tests', () => {
  test('should load category page using slug', async ({ page }) => {
    await page.goto('http://localhost:5173/categories/data-science');

    // Wait for page to load and check URL contains slug
    await expect(page).toHaveURL('http://localhost:5173/categories/data-science');

    // Check that category name appears on page
    await expect(page.locator('h1')).toContainText(/Data Science/i);
  });

  test('should load technology category by slug', async ({ page }) => {
    await page.goto('http://localhost:5173/categories/technology');
    await expect(page).toHaveURL('http://localhost:5173/categories/technology');
    await expect(page.locator('h1')).toContainText(/Technology/i);
  });

  test('should load programming category by slug', async ({ page }) => {
    await page.goto('http://localhost:5173/categories/programming');
    await expect(page).toHaveURL('http://localhost:5173/categories/programming');
    await expect(page.locator('h1')).toContainText(/Programming/i);
  });

  test('category links on home page should use slugs', async ({ page }) => {
    await page.goto('http://localhost:5173');

    // Find all category links and verify they use slugs
    const categoryLinks = page.locator('a[href^="/categories/"]');
    const count = await categoryLinks.count();

    expect(count).toBeGreaterThan(0);

    // Check each link uses slug format (not numeric ID)
    for (let i = 0; i < count; i++) {
      const href = await categoryLinks.nth(i).getAttribute('href');
      // Slug should contain letters/hyphens, not just numbers
      expect(href).toMatch(/\/categories\/[a-z-]+/i);
    }
  });
});
