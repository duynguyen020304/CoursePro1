<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-04 -->

# Schemas

## Purpose
Zod validation schemas for API requests and responses. Organized by domain (auth, user, admin, course, order, common). Provides type-safe validation at runtime.

## Key Stats
- **60+ schema files** across 6 domains
- **Barrel exports** via `index.ts` files per domain

## Schema Organization

| Domain | Files | Purpose |
|--------|-------|---------|
| `auth/` | 6 | signin, signup, forgotPassword, verifyCode, resetPassword, changePassword |
| `user/` | 3 | profile, editProfile, apiResponses |
| `admin/` | 5 | user, role, roleManagement, review, revenue |
| `course/` | 7 | createCourse, chapter, lesson, category, uploadVideo, instructorPublic, apiResponses |
| `order/` | 3 | cart, checkout, apiResponses |
| `common/` | 6 | email, password, uuid, pagination, apiResponse, env |

## Common Schemas

### `common/email.schema.ts`
- Email format validation
- Used across auth and user schemas

### `common/password.schema.ts`
- Minimum 8 characters
- Requires uppercase, lowercase, number, special char

### `common/pagination.schema.ts`
- `page`: positive integer (default: 1)
- `per_page`: 1-100 range (default: 10)

### `common/apiResponse.schema.ts`
- Standard API response wrapper
- Fields: success, message, data (optional)

## For AI Agents

### Working In This Directory
- Use Zod `z.object()` for schema definitions
- Export via barrel `index.ts` files
- Import from parent: `import { signInSchema } from '@/schemas/auth'`
- Use `.parse()` for sync validation, `.safeParse()` for error handling

### Validation Pattern
```typescript
import { signInSchema } from '@/schemas/auth';

const result = signInSchema.safeParse(formData);
if (!result.success) {
  // Handle validation error
}
```

### Dynamic Import Pattern
Tests use dynamic imports to handle conditional schemas:
```typescript
const module = await import(`@/schemas/auth/${schemaName}.schema.ts`);
```

<!-- MANUAL: Custom schemas notes can be added below -->
