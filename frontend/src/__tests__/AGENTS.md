<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-20 | Updated: 2026-04-20 -->

# Tests

## Purpose
Frontend test suite using Vitest for unit testing and Playwright for E2E testing. Tests are organized by feature (admin, schemas, components).

## Test Files

### Schema Tests
| File | Description |
|------|-------------|
| `schemas/auth/signin.test.ts` | Sign-in schema validation |
| `schemas/auth/signup.test.ts` | Sign-up schema validation |
| `schemas/common.test.ts` | Common schema tests (baseEntity, env) |
| `schemas/order/apiResponses.test.ts` | Order API response schemas |

### Component Tests
| File | Description |
|------|-------------|
| `admin/UploadVideo.test.tsx` | Video upload component tests |
| `admin/uploadVideo.upload.test.ts` | Upload flow tests |

## Test Configuration

### Vitest (`vitest.config.ts`)
- Environment: jsdom
- Setup file: `src/vitest-setup.ts`
- Coverage: v8

### Playwright
- Config: Root directory
- Tests: E2E testing (configured but minimal tests)

## For AI Agents

### Working In This Directory
- Unit tests use Vitest + Testing Library
- Component tests use `@testing-library/react`
- E2E tests use Playwright

### Common Patterns

```typescript
// Schema test pattern
import { describe, it, expect } from 'vitest';
import { z } from 'zod';
import { mySchema } from '@/schemas/...';

describe('mySchema', () => {
  it('accepts valid data', () => {
    const result = mySchema.safeParse(validData);
    expect(result.success).toBe(true);
  });
});
```

## Running Tests

```bash
# Run unit tests (watch mode)
bun run test

# Run once
bun run test:run

# Lint
bun run lint

# E2E tests
npx playwright test
```

## Dependencies

### External
- `vitest` v4.1.4 - Test runner
- `@vitest/coverage-v8` v4.1.4 - Code coverage
- `@testing-library/react` v16.3.2 - React testing utilities
- `playwright` v1.59.1 - E2E testing

<!-- MANUAL: -->
