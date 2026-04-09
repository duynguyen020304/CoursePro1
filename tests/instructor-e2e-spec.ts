import { test, expect, type Locator, type Page } from '@playwright/test';

const instructorEmail = 'nguyen.tuan@example.com';
const instructorPassword = 'Instructor@123';

async function loginAsInstructor(page: Page) {
  await page.goto('/signin');
  await expect(page.getByRole('heading', { name: 'Sign in to your account' })).toBeVisible();

  await page.getByLabel('Email address').fill(instructorEmail);
  await page.getByLabel('Password').fill(instructorPassword);
  await page.getByRole('button', { name: 'Sign in' }).click();

  await expect(page).toHaveURL('/', { timeout: 15000 });
}

async function openInstructorRoute(page: Page, path: string) {
  await page.goto(path);
  await expect(page).toHaveURL(path, { timeout: 15000 });
}

async function expectVisible(locator: Locator) {
  await expect(locator).toBeVisible({ timeout: 10000 });
}

async function fillCourseForm(page: Page, title: string) {
  await page.getByLabel('Course Title *').fill(title);
  await page.getByLabel('Description *').fill(`Instructor E2E description for ${title}`);
  await page.getByLabel('Price ($) *').fill('29.99');

  await page.getByLabel('Difficulty').selectOption({ label: 'Beginner' }).catch(async () => {
    await page.getByLabel('Difficulty').selectOption({ index: 1 });
  });

  await page.getByLabel('Language').selectOption({ label: 'English' }).catch(async () => {
    const languageSelect = page.getByLabel('Language');
    const optionCount = await languageSelect.locator('option').count();
    if (optionCount > 1) {
      await languageSelect.selectOption({ index: 1 });
    }
  });

  const categories = page.getByLabel(/Categories/);
  const availableValues = await categories.locator('option').evaluateAll((options) =>
    options
      .map((option) => ({
        value: (option as HTMLOptionElement).value,
        disabled: (option as HTMLOptionElement).disabled,
      }))
      .filter((option) => option.value && !option.disabled)
      .map((option) => option.value)
  );

  if (availableValues.length > 0) {
    await categories.selectOption(availableValues.slice(0, 1));
  }

  const objectiveInputs = page.locator('input[placeholder="e.g., Build real-world web applications"]');
  if (await objectiveInputs.count()) {
    await objectiveInputs.first().fill(`Understand ${title}`);
  }

  const requirementInputs = page.locator('input[placeholder="e.g., Basic understanding of HTML and CSS"]');
  if (await requirementInputs.count()) {
    await requirementInputs.first().fill('Basic computer skills');
  }
}

async function createCourseViaUi(page: Page, title: string) {
  await loginAsInstructor(page);
  await openInstructorRoute(page, '/instructor/courses/create');
  await expectVisible(page.getByRole('heading', { name: 'Create New Course' }));
  await fillCourseForm(page, title);
  await page.getByRole('button', { name: 'Create Course' }).click();
  await expect(page).toHaveURL('/instructor/courses', { timeout: 15000 });
  await expectVisible(page.getByText(title));
}

test.describe('Instructor E2E tests', () => {
  test.describe.configure({ mode: 'serial' });

  let createdCourseTitle = '';
  let editedCourseTitle = '';

  test('phase 1: instructor can sign in, verify auth, and open dashboard', async ({ page, context }) => {
    await loginAsInstructor(page);

    const cookies = await context.cookies();
    expect(cookies.some((cookie) => cookie.name === 'access_token')).toBe(true);
    expect(cookies.some((cookie) => cookie.name === 'refresh_token')).toBe(true);

    await openInstructorRoute(page, '/instructor/dashboard');

    await expectVisible(page.getByText('Instructor Portal'));
    await expectVisible(page.locator('aside').getByRole('link', { name: /Dashboard/i }));
    await expectVisible(page.locator('aside').getByRole('link', { name: /My Courses/i }));
    await expectVisible(page.locator('aside').getByRole('link', { name: /Create Course/i }));
    await expectVisible(page.locator('aside').getByRole('link', { name: /Profile/i }));
    await expectVisible(page.getByText('Total Courses'));
    await expectVisible(page.getByText('Total Students'));
    await expectVisible(page.getByText('Recent Courses'));
    await expectVisible(page.getByText('Quick Actions'));
  });

  test('phase 2: instructor dashboard quick actions and my courses surface are usable', async ({ page }) => {
    await loginAsInstructor(page);
    await openInstructorRoute(page, '/instructor/dashboard');

    await page.getByRole('link', { name: 'Create Course' }).click();
    await expect(page).toHaveURL('/instructor/courses/create', { timeout: 15000 });
    await expectVisible(page.getByRole('heading', { name: 'Create New Course' }));

    await openInstructorRoute(page, '/instructor/courses');
    await expectVisible(page.getByRole('heading', { name: 'My Courses' }));

    const createLinks = page.getByRole('link', { name: /\+ Create Course|Create Your First Course/i });
    await expect(createLinks.first()).toBeVisible({ timeout: 10000 });

    const tableVisible = await page.locator('table').isVisible().catch(() => false);
    const emptyStateVisible = await page.getByText('No courses yet').isVisible().catch(() => false);
    expect(tableVisible || emptyStateVisible).toBeTruthy();
  });

  test('phase 3: instructor can create a course', async ({ page }) => {
    createdCourseTitle = `Instructor E2E ${Date.now()}`;

    await loginAsInstructor(page);
    await openInstructorRoute(page, '/instructor/courses/create');

    await expectVisible(page.getByRole('heading', { name: 'Create New Course' }));
    await fillCourseForm(page, createdCourseTitle);
    await page.getByRole('button', { name: 'Create Course' }).click();

    await expect(page).toHaveURL('/instructor/courses', { timeout: 15000 });
    await expectVisible(page.locator('main').getByRole('heading', { name: 'My Courses', level: 1 }));
    await expectVisible(page.getByText(createdCourseTitle));
  });

  test('phase 4: instructor can edit course details and author chapter content', async ({ page }) => {
    const chapterTitle = `Instructor Chapter ${Date.now()}`;
    const lessonTitle = `Instructor Lesson ${Date.now()}`;
    if (!createdCourseTitle) {
      createdCourseTitle = `Instructor E2E ${Date.now()}`;
      await createCourseViaUi(page, createdCourseTitle);
    }

    editedCourseTitle = `${createdCourseTitle} Updated`;

    await loginAsInstructor(page);
    await openInstructorRoute(page, '/instructor/courses');

    const courseRow = page.locator('tr', { hasText: createdCourseTitle }).first();
    await expectVisible(courseRow);
    await courseRow.getByRole('link', { name: 'Edit' }).click();

    await expect(page).toHaveURL(/\/instructor\/courses\/.+\/edit/, { timeout: 15000 });
    await expectVisible(page.getByRole('heading', { name: 'Edit Course' }));

    await page.locator('input[name="title"]').fill(editedCourseTitle);
    await page.locator('textarea[name="description"]').fill(`Updated description for ${editedCourseTitle}`);

    page.once('dialog', async (dialog) => {
      expect(dialog.message()).toContain('Course updated successfully');
      await dialog.accept();
    });

    await page.getByRole('button', { name: 'Save Changes' }).click();
    await expect(page.locator('input[name="title"]')).toHaveValue(editedCourseTitle);

    await page.getByRole('button', { name: 'Course Content' }).click();
    await expectVisible(page.getByRole('heading', { name: 'Add New Chapter' }));

    await page.getByPlaceholder('Chapter title').fill(chapterTitle);
    await page.getByPlaceholder('Description (optional)').fill('Instructor E2E chapter description');
    await page.getByRole('button', { name: 'Add' }).click();
    await expectVisible(page.getByText(chapterTitle));

    const chapterCard = page.locator('div.border.rounded-lg', { hasText: chapterTitle }).first();
    await chapterCard.getByPlaceholder('New lesson title').fill(lessonTitle);
    await chapterCard.getByRole('button', { name: '+ Add Lesson' }).click();
    await expectVisible(chapterCard.getByText(lessonTitle));
  });

  test('phase 5: instructor can see edited course in the list and update profile', async ({ page }) => {
    const biography = `Instructor biography ${Date.now()}`;

    if (!editedCourseTitle) {
      createdCourseTitle = `Instructor E2E ${Date.now()}`;
      await createCourseViaUi(page, createdCourseTitle);

      editedCourseTitle = `${createdCourseTitle} Updated`;
      const chapterTitle = `Instructor Chapter ${Date.now()}`;
      const lessonTitle = `Instructor Lesson ${Date.now()}`;

      const courseRow = page.locator('tr', { hasText: createdCourseTitle }).first();
      await expectVisible(courseRow);
      await courseRow.getByRole('link', { name: 'Edit' }).click();
      await expect(page).toHaveURL(/\/instructor\/courses\/.+\/edit/, { timeout: 15000 });

      await page.locator('input[name="title"]').fill(editedCourseTitle);
      await page.locator('textarea[name="description"]').fill(`Updated description for ${editedCourseTitle}`);
      page.once('dialog', async (dialog) => {
        await dialog.accept();
      });
      await page.getByRole('button', { name: 'Save Changes' }).click();

      await page.getByRole('button', { name: 'Course Content' }).click();
      await page.getByPlaceholder('Chapter title').fill(chapterTitle);
      await page.getByPlaceholder('Description (optional)').fill('Instructor E2E chapter description');
      await page.getByRole('button', { name: 'Add' }).click();
      const chapterCard = page.locator('div.border.rounded-lg', { hasText: chapterTitle }).first();
      await chapterCard.getByPlaceholder('New lesson title').fill(lessonTitle);
      await chapterCard.getByRole('button', { name: '+ Add Lesson' }).click();
    }

    await loginAsInstructor(page);
    await openInstructorRoute(page, '/instructor/courses');
    await expectVisible(page.getByText(editedCourseTitle));

    await openInstructorRoute(page, '/instructor/profile');
    await expectVisible(page.getByRole('heading', { name: 'Instructor Profile' }));
    await expectVisible(page.getByText('Account Information'));
    await page.getByLabel('Biography').fill(biography);
    await page.getByRole('button', { name: 'Save Profile' }).click();

    await expectVisible(page.getByText('Profile updated successfully!'));
    await expect(page.getByLabel('Biography')).toHaveValue(biography);
  });
});
