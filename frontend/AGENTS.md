<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-12 -->

# Frontend

## Purpose
React 19 frontend application for the CoursePro1 e-learning platform. Built with Vite 8, Tailwind CSS 4, and React Router 7. Provides user interfaces for public browsing, authenticated user features (my courses, profile, certificates), and admin dashboard management.

## Technology Stack

### Core Framework
- **React 19.2.5** - Latest React with concurrent features
- **TypeScript 6.0.2** - Strict type checking enabled
- **Vite 8.0.8** - Build tool and dev server

### UI & Styling
- **Tailwind CSS 4.2.2** - Utility-first CSS framework with Vite plugin
- **@tailwindcss/vite 4.2.2** - Tailwind Vite integration
- **Framer Motion 12.38.0** - Animation library (installed but minimal usage)
- **Swiper 12.1.3** - Touch slider/carousel
- **Chart.js 4.5.1** - Data visualization

### State Management & Data Fetching
- **Zustand 5.0.12** - Lightweight state management (minimal usage)
- **TanStack React Query 5.99.0** - Server state management and data fetching
- **React Context** - Auth and Cart global state

### Routing & Forms
- **React Router DOM 7.14.0** - Client-side routing
- **React Hook Form 7.72.1** - Form management
- **Zod 4.3.6** - Schema validation
- **@hookform/resolvers 5.2.2** - Form validation integration

### API & Utilities
- **Axios 1.15.0** - HTTP client with interceptors
- **jsPDF 4.2.1** - PDF generation for certificates
- **React Hot Toast 2.6.0** - Toast notifications

### Testing & Development
- **Vitest 4.1.4** - Unit testing framework
- **@vitest/coverage-v8 4.1.4** - Code coverage
- **Playwright 1.59.1** - E2E testing
- **@testing-library/react 16.3.2** - React testing utilities
- **ESLint 9.39.4** - Code linting with flat config

## Key Files
| File | Description |
|------|-------------|
| `package.json` | Dependencies - React 19, Vite, Tailwind CSS 4, TanStack Query |
| `vite.config.js` | Vite config with React plugin, Tailwind 4, API proxy to port 8000 |
| `tsconfig.json` | TypeScript config (strict mode, ES2020 target) |
| `index.html` | Entry HTML with root div |
| `.env.example` | Environment template (VITE_API_URL, VITE_GOOGLE_CLIENT_ID) |
| `eslint.config.js` | ESLint flat config with React Hooks plugin |
| `vitest.config.ts` | Vitest configuration with jsdom environment |

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `src/` | Source code root (see `src/AGENTS.md`) |
| `public/` | Static public assets (favicon, icons) |
| `dist/` | Build output (generated) |

## Architecture

### Directory Structure
```
src/
├── main.tsx                    # Application entry point
├── App.tsx                     # Root component with routing setup
├── index.css                   # Global styles with Tailwind
├── vite-env.d.ts              # Vite type definitions
├── vitest-setup.ts            # Test configuration
│
├── components/                 # Shared UI components
│   ├── Header.tsx             # Navigation header with cart
│   ├── Footer.tsx             # Site footer
│   ├── GoogleLoginButton.tsx  # OAuth integration
│   ├── AdminRoute.tsx         # Admin route guard
│   └── PermissionRoute.tsx    # Permission-based guard
│
├── contexts/                   # React Context providers
│   ├── AuthContext.tsx        # Authentication state (428 lines)
│   └── CartContext.tsx        # Shopping cart state (199 lines)
│
├── layouts/                    # Page layout wrappers
│   ├── PublicLayout.tsx       # Public pages layout
│   ├── UserLayout.tsx         # Authenticated user layout
│   ├── AdminLayout.tsx        # Admin dashboard layout
│   └── InstructorLayout.tsx   # Instructor portal layout
│
├── pages/                      # Route components by user type
│   ├── public/                # Public accessible pages (14 pages)
│   ├── user/                  # Authenticated user pages (6 pages)
│   ├── admin/                 # Admin dashboard pages (6 pages)
│   └── instructor/            # Instructor portal pages (5 pages)
│
├── services/                   # API layer
│   ├── api.ts                 # Centralized Axios client (626 lines)
│   └── index.ts               # Service exports
│
├── schemas/                    # Zod validation schemas (46 schemas)
│   ├── auth/                  # Authentication schemas
│   ├── user/                  # User-related schemas
│   ├── course/                # Course schemas
│   ├── instructor/            # Instructor schemas
│   ├── admin/                 # Admin schemas
│   ├── cart/                  # Cart schemas
│   ├── order/                 # Order schemas
│   ├── review/                # Review schemas
│   ├── student/               # Student schemas
│   └── common/                # Shared schemas
│
├── utils/                      # Utility functions
│   ├── env.ts                 # Environment validation
│   ├── apiValidator.ts        # API validation helpers
│   └── index.ts
│
├── hooks/                      # Custom React hooks (empty)
├── types/                      # TypeScript types (empty)
└── __tests__/                  # Test files
```

### State Management

**Primary State Management: React Context + TanStack Query**

**AuthContext** (`src/contexts/AuthContext.tsx` - 428 lines)
- Centralized authentication state
- Manages user session, permissions, and loading states
- Methods: `login`, `signup`, `logout`, `updateUser`
- Permission checking: `hasRole`, `hasPermission`, `hasAnyPermission`, `hasAllPermissions`
- Integrates with TanStack Query for server state
- Query key: `['auth', 'current']` for caching

**CartContext** (`src/contexts/CartContext.tsx` - 199 lines)
- Shopping cart state management
- Methods: `fetchCart`, `addItem`, `removeItem`, `clearCart`
- React Query integration for API calls
- Query key based on user ID: `['cart', user?.user_id ?? 'guest']`

**TanStack Query (React Query)**
- 83 occurrences of `useQuery`/`useMutation` across 23 files
- Query invalidation strategies for data consistency
- Stale-time and cache-time configuration
- Automatic refetching on window focus (disabled where appropriate)

**Local Component State**
- 115 occurrences of `useState` across 24 files
- Used for UI-specific state (forms, modals, toggles)
- `useEffect` hooks for side effects and data fetching

### API Integration

**Centralized API Client** (`src/services/api.ts` - 626 lines)

**Axios Configuration:**
- Base URL: `VITE_API_URL` (defaults to `/api` with proxy)
- `withCredentials: true` (cookie-based auth)
- XSRF token handling (Laravel Sanctum)
- Custom interceptors for error handling

**Error Handling Interceptors:**
- CSRF token mismatch (419) - auto-refresh and retry
- Unauthorized (401) - refresh auth cookies or redirect to login
- Public path detection to avoid unnecessary redirects
- Smart auth recovery with skip paths

**API Organization:**
```typescript
// Auth API
authApi.login(credentials)
authApi.signup(data)
authApi.forgotPassword(email)
authApi.verifyCode(email, code)
authApi.resetPassword(email, code, password)
authApi.logout()
authApi.googleLogin(code, redirectUri)

// User API
userApi.current()
userApi.profile()
userApi.updateProfile(data)

// Course API (50+ methods)
courseApi.list(params)
courseApi.get(id)
courseApi.search(query)
courseApi.getChapters(courseId)
courseApi.addChapter(courseId, data)

// Cart API
cartApi.get()
cartApi.addItem(courseId, quantity)
cartApi.removeItem(itemId)
cartApi.clear()

// Order API
orderApi.list(params)
orderApi.create()
orderApi.get(orderId)
orderApi.completePayment(orderId, paymentMethod)

// Instructor API
instructorApi.getProfile()
instructorApi.getStats()
instructorApi.getCourses()
instructorApi.createCourse(data)
instructorApi.updateCourse(courseId, data)

// Admin APIs
adminUserApi.list(params)
adminUserApi.create(data)
adminUserApi.assignRole(id, roleId)
roleApi.list()
roleApi.create(data)
roleApi.assignPermissions(id, permissions)
permissionApi.list()
```

### Routing Structure

**Public Routes** (no authentication):
- `/` - Home page
- `/courses` - Course listing
- `/courses/:id` - Course details
- `/courses/:id/watch` - Video preview
- `/signin` - Sign in
- `/signup` - Sign up
- `/forgot-password` - Password recovery
- `/verify-email` - Email verification
- `/verify-code` - Code verification
- `/reset-password` - Password reset
- `/auth/callback` - OAuth callback
- `/cart` - Shopping cart
- `/checkout` - Checkout process
- `/categories` - Categories list
- `/categories/:slug` - Category page
- `/instructors` - Instructors list
- Static pages: `/about`, `/faq`, `/contact`, `/privacy`, `/terms`

**Protected User Routes** (authentication required):
- `/my-courses` - Enrolled courses
- `/profile` - User profile
- `/edit-profile` - Edit profile
- `/purchase-history` - Order history
- `/certificates` - Certificates list
- `/watch/:courseId/:lessonId?` - Watch lesson video

**Admin Routes** (admin access required):
- `/admin/dashboard` - Admin dashboard
- `/admin/courses` - Course management
- `/admin/users` - User management
- `/admin/roles` - Role & permission management
- `/admin/revenue` - Revenue analytics
- `/admin/upload-video` - Video upload

**Instructor Routes** (instructor access required):
- `/instructor/dashboard` - Instructor dashboard
- `/instructor/courses` - Course list
- `/instructor/courses/create` - Create new course
- `/instructor/courses/:courseId/edit` - Edit course
- `/instructor/profile` - Instructor profile

**Route Guards:**
- `ProtectedRoute` - Checks authentication
- `AdminRoute` - Checks admin permission
- `PermissionRoute` - Checks specific permissions (anyOf/allOf)

## For AI Agents

### Working In This Directory
- **Framework**: React 19 with Vite 8 bundler
- **Language**: TypeScript 6 with strict mode enabled
- **Styling**: Tailwind CSS 4 (utility-first CSS framework)
- **Routing**: React Router 7 with nested layouts
- **State Management**: React Context (Auth, Cart) + TanStack Query
- **Forms**: react-hook-form with Zod validation
- **Data Fetching**: TanStack Query (React Query) with 83+ queries/mutations
- **HTTP Client**: Axios with interceptors for auth tokens
- **Charts**: Chart.js for analytics
- **PDF**: jsPDF for certificate generation
- **Video**: Swiper for carousels, native `<video>` for playback
- **Dependencies**: Run `bun install` or `npm install` after changes to `package.json`
- **Dev Server**: Run `bun run dev` or `npm run dev` to start Vite dev server (port 5173)

### Testing Requirements
- Run linting with `bun run lint` or `npm run lint`
- Run tests with `bun run test` or `npm run test` (Vitest watch mode)
- Run tests once with `bun run test:run` or `npm run test:run`
- Build verification with `bun run build` or `npm run build`
- Test in development mode before committing

### Common Patterns
- **Context Layer**: `AuthContext`, `CartContext` for global state
- **Layouts**: `PublicLayout`, `UserLayout`, `AdminLayout`, `InstructorLayout` for page wrappers
- **Services**: `api.ts` provides centralized API client with interceptors
- **Pages organized by audience**: `public/`, `user/`, `admin/`, `instructor/`
- **Zod Schemas**: 46 validation schemas organized by domain
- **TanStack Query**: Server state caching and data fetching
- **Loading states**: Consistent spinner pattern during async operations
- **Error handling**: try/catch with error state display
- **Protected routes**: Check `isAuthenticated` from `AuthContext`

### BaseEntity Zod Schema

All entity schemas extend the base entity schema with common audit fields:

```typescript
import { baseEntitySchema, extendBaseEntity } from '@/schemas/common';

// Option 1: Use extendBaseEntity helper
const courseSchema = extendBaseEntity(z.object({
  title: z.string(),
  price: z.number(),
  // ... other fields
}));

// Option 2: Manually add fields
const courseSchema = z.object({
  id: z.string().uuid(),
  is_active: z.boolean().optional().default(true),
  is_deleted: z.boolean().optional().default(false),
  created_at: z.string().datetime().nullable().optional(),
  updated_at: z.string().datetime().nullable().optional(),
  title: z.string(),
  // ... other fields
});
```

**BaseEntity Fields:**
- `id` (UUID string)
- `is_active` (boolean, default true)
- `is_deleted` (boolean, default false)
- `created_at` (nullable datetime)
- `updated_at` (nullable datetime)

### UI Components and Styling

**Styling Framework: Tailwind CSS 4**
- Utility-first approach throughout the application
- Custom CSS in `src/index.css` for:
  - Swiper carousel customization
  - Line clamp utilities
  - Global body styles

**Common UI Patterns:**

**Loading States:**
```tsx
<div className="min-h-screen flex items-center justify-center">
  <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
</div>
```

**Layout Structure:**
- Responsive containers: `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`
- Grid layouts: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3`
- Flex patterns: `flex items-center space-x-4`

**Color Scheme:**
- Primary: `indigo-600` (CTAs, links, branding)
- Secondary: `gray-50` through `gray-900` (neutrals)
- Status: `green-500` (success), `red-500` (error), `amber-500` (warning)

**No Component Library:**
- No Material-UI, Ant Design, or similar libraries
- All components built from scratch with Tailwind

### Environment Variables

**Required** (validated at runtime via Zod):
- `VITE_API_URL` - Backend API endpoint
- `VITE_GOOGLE_CLIENT_ID` - Google OAuth client ID

**Validation** (`src/utils/env.ts`):
- Validates on app startup
- Shows toast error if invalid
- Non-blocking (app still renders)

## Large Files (>300 lines)

| File | Lines | Purpose |
|------|-------|---------|
| `src/pages/public/Checkout.tsx` | 672 | Multi-payment checkout flow |
| `src/pages/instructor/EditCourse.tsx` | 561 | Edit course with chapters/lessons |
| `src/pages/user/WatchVideo.tsx` | 493 | Video player with progress tracking |
| `src/pages/public/Home.tsx` | 423 | Landing page with Swiper sliders |
| `src/pages/admin/Revenue.tsx` | 357 | Revenue analytics with Chart.js |
| `src/pages/admin/UploadVideo.tsx` | 377 | Video upload with selectors |
| `src/pages/instructor/CreateCourse.tsx` | 379 | Course creation form |
| `src/pages/admin/RoleManagement.tsx` | 340 | Role/permission management |
| `src/pages/admin/Dashboard.tsx` | 313 | Admin dashboard with stats |
| `src/pages/user/Certificates.tsx` | 309 | Certificate list with PDF generation |
| `src/contexts/AuthContext.tsx` | 428 | Auth state with permissions |
| `src/services/api.ts` | 626 | Centralized API client |

## Dependencies

### Internal
- `src/services/api.ts` - Centralized API client
- `src/contexts/AuthContext.tsx` - Authentication state
- `src/contexts/CartContext.tsx` - Shopping cart state
- `src/layouts/` - Page layout components

### External
- `react` v19.2.5 - UI framework
- `react-dom` v19.2.5 - React DOM
- `react-router-dom` v7.14.0 - Client-side routing
- `@tanstack/react-query` v5.99.0 - Data fetching/caching
- `axios` v1.15.0 - HTTP client
- `react-hook-form` v7.72.1 - Form handling
- `tailwindcss` v4.2.2 - Styling
- `swiper` v12.1.3 - Carousel/slider
- `chart.js` v4.5.1 - Data visualization
- `jspdf` v4.2.1 - PDF generation
- `zod` v4.3.6 - Schema validation

## NPM Scripts
```json
{
  "dev": "vite",                    // Start dev server
  "build": "vite build",            // Production build
  "lint": "eslint .",               // Run linter
  "preview": "vite preview",        // Preview production build
  "test": "vitest",                 // Run tests in watch mode
  "test:run": "vitest --run"        // Run tests once
}
```

## Build Configuration

### Vite Configuration (`vite.config.js`)
```javascript
{
  plugins: [react(), tailwindcss()],
  server: {
    port: 5173,
    proxy: {
      '/api': 'http://localhost:8000'  // Backend proxy
    }
  },
  test: {
    environment: 'jsdom',
    setupFiles: './src/vitest-setup.ts'
  }
}
```

### TypeScript Configuration
```json
{
  "compilerOptions": {
    "target": "ES2020",
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "jsx": "react-jsx",
    "strict": true,
    "noImplicitAny": true,
    "strictNullChecks": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true
  }
}
```

## Summary Statistics

| Category | Count |
|----------|-------|
| TSX Components | 49 |
| TypeScript Files | 60 |
| API Methods | 50+ |
| Zod Schemas | 46 |
| Route Paths | 40+ |
| Test Files | 6 |
| Large Files (>300 lines) | 11 |
| Estimated Lines of Code | 25,000+ |
