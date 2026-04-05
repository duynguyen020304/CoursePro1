// Auth API - Thin re-export from api.ts
// This file is deprecated. All auth calls should use authApi from api.ts.
// This re-export exists only for backward compatibility during migration.

// Re-export everything from api.ts authApi
export { authApi } from './api';

// Also re-export userSchema type for consumers
export type { User } from './api';
