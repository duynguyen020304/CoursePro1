<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-20 | Updated: 2026-04-20 -->

# Utils

## Purpose
Utility functions and helpers for the frontend application. Environment variable validation and API response validation.

## Key Files
| File | Description |
|------|-------------|
| `env.ts` | Runtime environment variable validation with Zod |
| `apiValidator.ts` | API response validation helpers |
| `index.ts` | Barrel export for utils |

## For AI Agents

### Working In This Directory
- Environment variables are validated at app startup
- Validation errors show as toast notifications (non-blocking)
- Use `env` from utils instead of `import.meta.env` directly

### Common Patterns

```typescript
// Import validated environment
import { env } from '@/utils/env';

// Use environment variables
const apiUrl = env.VITE_API_URL;

// Check if environment is valid
import { isEnvValid } from '@/utils/env';
if (!isEnvValid()) {
  // Handle invalid environment
}
```

## Environment Variables

### Required (validated via Zod)
- `VITE_API_URL` - Backend API endpoint
- `VITE_GOOGLE_CLIENT_ID` - Google OAuth client ID

### Validation Behavior
- Validates on app startup
- Shows toast error if invalid
- Non-blocking (app still renders)
- Returns safe defaults to prevent crashes

## Dependencies

### Internal
- `src/schemas/common/env.schema.ts` - Zod validation schema

### External
- `zod` v4.3.6 - Schema validation
- `react-hot-toast` v2.6.0 - Error notifications

<!-- MANUAL: -->
