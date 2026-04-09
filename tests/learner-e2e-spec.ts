import { test, expect, type Page } from '@playwright/test';

const learnerEmail = 'student1@example.com';
const learnerPassword = 'Student@123';
const learnerProfileImagePrefix = 'https://example.com/e2e/learner-avatar';

async function loginAsLearner(page: Page) {
  await page.goto('/signin');
  await expect(page.getByRole('heading', { name: 'Sign in to your account' })).toBeVisible();

  await page.getByLabel('Email address').fill(learnerEmail);
  await page.getByLabel('Password').fill(learnerPassword);
  await page.getByRole('button', { name: 'Sign in' }).click();

  await expect(page).toHaveURL('/', { timeout: 15000 });
}

async function openLearnerRoute(page: Page, path: string) {
  await page.goto(path);
  await expect(page).toHaveURL(path, { timeout: 15000 });
}

async function resolveOwnedCoursePath(page: Page) {
  await openLearnerRoute(page, '/my-courses');
  const ownedCourseLinks = page.locator('a[href^="/watch/"]');
  await expect(ownedCourseLinks.first()).toBeVisible({ timeout: 10000 });
  const href = await ownedCourseLinks.first().getAttribute('href');
  expect(href).toBeTruthy();
  return href!;
}

async function collectOwnedCourseIds(page: Page) {
  await openLearnerRoute(page, '/my-courses');
  const ownedCourseLinks = page.locator('a[href^="/watch/"]');
  const linkCount = await ownedCourseLinks.count();
  const ownedCourseIds = new Set<string>();

  for (let index = 0; index < linkCount; index += 1) {
    const href = await ownedCourseLinks.nth(index).getAttribute('href');
    const match = href?.match(/^\/watch\/([^/]+)/);
    if (match?.[1]) {
      ownedCourseIds.add(match[1]);
    }
  }

  return ownedCourseIds;
}

async function resolvePurchasableCourseId(page: Page) {
  const ownedCourseIds = await collectOwnedCourseIds(page);
  const coursesResponse = await page.request.get('http://localhost:8000/api/courses?page=1');
  expect(coursesResponse.ok()).toBeTruthy();

  const body = await coursesResponse.json();
  expect(Array.isArray(body.data)).toBeTruthy();

  const candidateCourse = body.data.find((course: { course_id?: string | number }) => {
    return course.course_id && !ownedCourseIds.has(String(course.course_id));
  });

  expect(candidateCourse?.course_id).toBeTruthy();
  return String(candidateCourse.course_id);
}

async function ensureCartHasItem(page: Page) {
  await page.goto('/cart');
  await expect(page.locator('h1')).toContainText(/Shopping Cart|Your Cart is Empty/, { timeout: 10000 });

  const emptyHeading = page.getByRole('heading', { name: 'Your Cart is Empty' });
  const hasEmptyCart = await emptyHeading.isVisible().catch(() => false);
  const hasCartSummary = await page.getByText('Order Summary').isVisible().catch(() => false);

  if (!hasEmptyCart || hasCartSummary) {
    await expect(page.getByRole('heading', { name: 'Shopping Cart' })).toBeVisible({ timeout: 10000 });
    return;
  }

  const courseId = await resolvePurchasableCourseId(page);
  const addToCartResult = await page.evaluate(async (targetCourseId) => {
    const xsrfCookie = document.cookie
      .split('; ')
      .find((cookie) => cookie.startsWith('XSRF-TOKEN='))
      ?.split('=')[1];

    const response = await fetch('/api/cart/items', {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-XSRF-TOKEN': xsrfCookie ? decodeURIComponent(xsrfCookie) : '',
      },
      body: JSON.stringify({
        course_id: targetCourseId,
        quantity: 1,
      }),
    });

    const body = await response.json().catch(() => null);
    return {
      ok: response.ok,
      status: response.status,
      body,
    };
  }, courseId);

  expect(addToCartResult.ok || addToCartResult.body?.message === 'Course already in cart').toBe(true);

  await page.goto('/cart');
  await expect(page.getByRole('heading', { name: 'Shopping Cart' })).toBeVisible({ timeout: 10000 });
  await expect(page.getByText('Order Summary')).toBeVisible();
}

test.describe('Learner E2E tests', () => {
  test.describe.configure({ mode: 'serial' });

  test('phase 1: learner can sign in and open core learner routes', async ({ page, context }) => {
    await loginAsLearner(page);

    const cookies = await context.cookies();
    expect(cookies.some((cookie) => cookie.name === 'access_token')).toBe(true);
    expect(cookies.some((cookie) => cookie.name === 'refresh_token')).toBe(true);

    await openLearnerRoute(page, '/my-courses');
    await expect(page.getByRole('heading', { name: 'My Courses' })).toBeVisible({ timeout: 10000 });

    await openLearnerRoute(page, '/profile');
    await expect(page.getByRole('heading', { name: 'My Profile' })).toBeVisible({ timeout: 10000 });
  });

  test('phase 2: learner can open an owned course, toggle completion, and persist notes', async ({ page }) => {
    const noteText = `Learner note ${Date.now()}`;

    await loginAsLearner(page);
    const coursePath = await resolveOwnedCoursePath(page);

    await page.goto(coursePath);
    await expect(page).toHaveURL(/\/watch\/.+/, { timeout: 15000 });
    await expect(page.getByText(/Course Progress/i)).toBeVisible({ timeout: 10000 });

    const completionButton = page.getByRole('button', { name: /Mark Complete|Completed/i });
    const initialLabel = ((await completionButton.textContent()) || '').trim();
    await completionButton.click();

    if (/Mark Complete/i.test(initialLabel)) {
      await expect(page.getByRole('button', { name: /Completed/i })).toBeVisible({ timeout: 10000 });
    } else {
      await expect(page.getByRole('button', { name: /Mark Complete/i })).toBeVisible({ timeout: 10000 });
    }

    await page.getByRole('button', { name: 'Notes' }).click();
    const notesField = page.getByPlaceholder('Take notes for this lesson...');
    await notesField.fill(noteText);
    await expect(notesField).toHaveValue(noteText);

    await page.reload();
    await expect(page.getByText(/Course Progress/i)).toBeVisible({ timeout: 10000 });
    await page.getByRole('button', { name: 'Notes' }).click();
    await expect(page.getByPlaceholder('Take notes for this lesson...')).toHaveValue(noteText);

    await page.getByRole('button', { name: 'Resources' }).click();
    await expect(page.getByText(/Downloadable Resources|No resources available/i)).toBeVisible();

    await page.getByRole('button', { name: 'Announcements' }).click();
    await expect(page.getByRole('heading', { name: 'Course Announcements' })).toBeVisible();
  });

  test('phase 3: learner can update profile and edit profile details', async ({ page }) => {
    const profileImageUrl = `${learnerProfileImagePrefix}-${Date.now()}.png`;
    const phoneValue = '+66912345678';
    const bioValue = `Learner E2E bio ${Date.now()}`;

    await loginAsLearner(page);

    await openLearnerRoute(page, '/profile');
    const profileImageField = page.getByPlaceholder('https://example.com/avatar.jpg');
    await profileImageField.fill(profileImageUrl);
    await page.getByRole('button', { name: 'Save Changes' }).click();
    await expect(page.getByText('Profile updated successfully!').first()).toBeVisible({ timeout: 10000 });
    await expect(profileImageField).toHaveValue(profileImageUrl);

    await openLearnerRoute(page, '/edit-profile');
    await expect(page.getByRole('heading', { name: 'Edit Profile' })).toBeVisible({ timeout: 10000 });
    await page.getByPlaceholder('+1 234 567 8900').fill(phoneValue);
    await page.getByPlaceholder('Tell us about yourself...').fill(bioValue);
    await page.getByRole('button', { name: 'Save Changes' }).click();
    await expect(page.getByText('Profile updated successfully!').first()).toBeVisible({ timeout: 10000 });
    await expect(page.getByPlaceholder('+1 234 567 8900')).toHaveValue(phoneValue);
    await expect(page.getByPlaceholder('Tell us about yourself...')).toHaveValue(bioValue);
  });

  test('phase 4: learner can review purchase history and certificates', async ({ page }) => {
    await loginAsLearner(page);

    await openLearnerRoute(page, '/purchase-history');
    await expect(page.getByRole('heading', { name: 'Purchase History' })).toBeVisible({ timeout: 10000 });
    await expect(page.getByText(/Order #/).first()).toBeVisible({ timeout: 10000 });

    await openLearnerRoute(page, '/certificates');
    await expect(page.getByRole('heading', { name: 'My Certificates' })).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('button', { name: /Preview/ }).first()).toBeVisible({ timeout: 10000 });
    await page.getByRole('button', { name: /Preview/ }).first().click();
    await expect(page.getByRole('heading', { name: 'Certificate Preview' })).toBeVisible({ timeout: 10000 });
    await page.getByRole('button', { name: 'Close' }).click();
    await expect(page.getByRole('heading', { name: 'Certificate Preview' })).toBeHidden({ timeout: 10000 });
  });

  test('phase 5: learner can manage cart and complete checkout', async ({ page }) => {
    await loginAsLearner(page);
    await ensureCartHasItem(page);

    await page.addInitScript(() => {
      Math.random = () => 0.99;
    });
    await page.goto('/checkout');
    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible({ timeout: 10000 });

    await page.getByPlaceholder('1234 5678 9012 3456', { exact: true }).fill('4111111111111111');
    await page.getByPlaceholder('John Doe', { exact: true }).fill('Course Pro Learner');
    await page.getByPlaceholder('MM/YY', { exact: true }).fill('12/34');
    await page.getByPlaceholder('123', { exact: true }).fill('123');
    await page.getByPlaceholder('John', { exact: true }).fill('Course');
    await page.getByPlaceholder('Doe', { exact: true }).fill('Learner');
    await page.getByPlaceholder('john@example.com', { exact: true }).fill(learnerEmail);
    await page.getByRole('combobox').selectOption('VN');

    await page.getByRole('button', { name: /Pay \$/ }).click();
    await expect(page).toHaveURL('/my-courses', { timeout: 20000 });
    await expect(page.getByRole('heading', { name: 'My Courses' })).toBeVisible({ timeout: 10000 });
  });
});
