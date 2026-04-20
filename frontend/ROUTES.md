# Frontend Routes Documentation

> Auto-generated documentation of all frontend routes organized by user role.
>
> **Last Updated:** 2026-04-20

---

## Table of Contents

- [Public/Guest Routes](#publicguest-routes)
- [Authenticated User Routes](#authenticated-user-routes)
- [Admin Routes](#admin-routes)
- [Instructor Routes](#instructor-routes)
- [Route Guards](#route-guards)
- [Layout Components](#layout-components)
- [Permission System](#permission-system)

---

## Public/Guest Routes

*No authentication required*

**Layout:** `PublicLayout` (Header + Footer + Outlet)

**Definition Location:** `frontend/src/App.tsx` (Lines 87-109)

| Route Path | Component | Description |
|------------|-----------|-------------|
| `/` | Home | Landing page |
| `/courses` | Courses | Course listing page |
| `/courses/:id` | CourseDetail | Individual course details |
| `/courses/:id/watch` | WatchVideo | Course video watching |
| `/signin` | SignIn | User login |
| `/signup` | SignUp | User registration |
| `/forgot-password` | ForgotPassword | Password reset request |
| `/verify-email` | VerifyEmail | Email verification page |
| `/verify-code` | VerifyCode | Code verification page |
| `/reset-password` | ResetPassword | Password reset form |
| `/auth/callback` | AuthCallback | OAuth callback handler |
| `/cart` | Cart | Shopping cart |
| `/checkout` | Checkout | Checkout process |
| `/categories` | Courses | Categories listing (reuses Courses component) |
| `/categories/:slug` | CategoryPage | Specific category page |
| `/instructors` | Courses | Instructors listing (reuses Courses component) |
| `/about` | Home | About page (reuses Home component) |
| `/faq` | Home | FAQ page (reuses Home component) |
| `/contact` | Home | Contact page (reuses Home component) |
| `/privacy` | Home | Privacy policy (reuses Home component) |
| `/terms` | Home | Terms of service (reuses Home component) |

**Route Guards:** None - All publicly accessible

**404 Handler:** `*` → Redirects to `/`

---

## Authenticated User Routes

*Requires authentication + specific permissions*

**Layout:** `UserLayout` (Header + Footer + Outlet)

**Definition Location:** `frontend/src/App.tsx` (Lines 112-132)

| Route Path | Component | Permissions Required | Description |
|------------|-----------|---------------------|-------------|
| `/my-courses` | MyCourses | `courses.learn` OR `courses.consume.own` | User's enrolled courses |
| `/profile` | Profile | `profile.view.own` OR `profile.view` | User profile view |
| `/edit-profile` | EditProfile | `profile.edit.own` OR `profile.edit` | Edit user profile |
| `/purchase-history` | PurchaseHistory | `purchase-history.view` OR `orders.view.own` OR `orders.view` | Order history |
| `/certificates` | Certificates | `certificates.view.own` OR `certificates.view` | User certificates |
| `/watch/:courseId/:lessonId?` | WatchVideo | `courses.consume.own` OR `lessons.watch` | Watch course lesson |

**Route Guards:**
1. **ProtectedRoute** - Checks authentication, redirects to `/signin` if not authenticated
2. **PermissionRoute** - Checks specific permissions, redirects to `/` if not authorized

---

## Admin Routes

*Requires admin role + specific permissions*

**Layout:** `AdminLayout` (Sidebar + Header + Outlet)

**Definition Location:** `frontend/src/App.tsx` (Lines 135-148)

| Route Path | Component | Permissions Required | Description |
|------------|-----------|---------------------|-------------|
| `/admin/dashboard` | AdminDashboard | `dashboard.admin.view` OR `dashboard.view` | Admin overview |
| `/admin/courses` | CourseManagement | `courses.view.any` OR `courses.view` OR `courses.manage` | Course management |
| `/admin/users` | UserManagement | `users.view` OR `users.manage` | User management |
| `/admin/roles` | RoleManagement | `roles.view` OR `roles.manage` | Role & permission management |
| `/admin/revenue` | Revenue | `revenue.view` OR `analytics.view` | Revenue analytics |
| `/admin/upload-video` | UploadVideo | `videos.manage.any` OR `videos.manage` OR `courses.manage` OR `courses.edit` | Video upload interface |

**Route Guards:**
1. **AdminRoute** - Checks authentication + `admin.access` permission, redirects to `/signin` if not authenticated, `/` if not admin
2. **PermissionRoute** - Additional permission checks for each route

**Additional Navigation Items:** All filtered dynamically based on user permissions (see `AdminLayout.tsx` Lines 25-31)

> **Missing Route:** `/admin/orders` is referenced in Dashboard component but NOT implemented in routing configuration

---

## Instructor Routes

*Requires instructor role + specific permissions*

**Layout:** `InstructorLayout` (Sidebar + Header + Outlet)

**Definition Location:** `frontend/src/App.tsx` (Lines 151-163)

| Route Path | Component | Permissions Required | Description |
|------------|-----------|---------------------|-------------|
| `/instructor/dashboard` | InstructorDashboard | `dashboard.instructor.view` OR `dashboard.view` OR `instructor.dashboard.view` | Instructor dashboard |
| `/instructor/courses` | InstructorCourses | `instructor.courses.view` OR `courses.view.own` OR `courses.manage.own` OR `courses.manage` | Instructor's courses |
| `/instructor/courses/create` | CreateCourse | `instructor.courses.create` OR `courses.create` | Create new course |
| `/instructor/courses/:courseId/edit` | EditCourse | `instructor.courses.edit` OR `courses.manage.own` OR `courses.edit.own` OR `courses.manage.any` OR `courses.edit.any` | Edit existing course |
| `/instructor/profile` | InstructorProfile | `instructor.profile.view` OR `instructor.profile.edit` OR `profile.view.own` OR `profile.edit.own` | Instructor profile |

**Route Guards:**
1. **ProtectedRoute** - Checks authentication
2. **InstructorLayout** - Checks `instructor.access` OR `admin.access` permission, redirects to `/` if not authorized
3. **PermissionRoute** - Additional permission checks for each route

**Additional Navigation Items:** All filtered dynamically based on user permissions (see `InstructorLayout.tsx` Lines 28-33)

---

## Route Guards

### ProtectedRoute
**Location:** `frontend/src/App.tsx` (Lines 68-80)

**Purpose:** Basic authentication check

**Logic:**
```tsx
isAuthenticated ? children : <Navigate to="/signin" />
```

**Usage:** Wraps all authenticated routes

**Redirects:** `/signin` if not authenticated

---

### AdminRoute
**Location:** `frontend/src/components/AdminRoute.tsx`

**Purpose:** Admin-specific authentication + role check

**Checks:**
- `isAuthenticated`
- `hasPermission('admin.access')`

**Redirects:**
- `/signin` if not authenticated
- `/` if not admin

**Prevents:** Transient admin layout exposure to non-admin users

---

### PermissionRoute
**Location:** `frontend/src/components/PermissionRoute.tsx`

**Purpose:** Granular permission checking

**Props:**
| Prop | Type | Description |
|------|------|-------------|
| `anyOf` | `string[]` | Array of permissions (passes if user has ANY) |
| `allOf` | `string[]` | Array of permissions (passes if user has ALL) |
| `redirectTo` | `string` | Custom redirect path (default: `/`) |

**Usage:** Wraps individual routes within layouts

**Redirects:** Custom redirect path if permissions fail

---

## Layout Components

### PublicLayout
**Location:** `frontend/src/layouts/PublicLayout.tsx`

**Components:** Header + Footer + Outlet

**Usage:** All public/guest routes

---

### UserLayout
**Location:** `frontend/src/layouts/UserLayout.tsx`

**Components:** Header + Footer + Outlet

**Usage:** Authenticated user routes

---

### AdminLayout
**Location:** `frontend/src/layouts/AdminLayout.tsx`

**Components:** Sidebar + Header + Outlet

**Features:**
- Dynamic navigation based on permissions
- Admin-only access

**Usage:** Admin routes only

---

### InstructorLayout
**Location:** `frontend/src/layouts/InstructorLayout.tsx`

**Components:** Sidebar + Header + Outlet

**Features:**
- Dynamic navigation based on permissions
- Permission check: `instructor.access` OR `admin.access`

**Usage:** Instructor routes only

---

## Permission System

All permissions are checked via the `useAuth()` hook from `AuthContext.tsx`.

### Key Permission Functions

| Function | Description |
|----------|-------------|
| `hasPermission(permissionName)` | Check single permission |
| `hasAnyPermission(permissions[])` | Check if user has ANY of the permissions |
| `hasAllPermissions(permissions[])` | Check if user has ALL permissions |
| `hasRole(roleName)` | Check if user has specific role |

### Permission Storage

- Stored in `userPermissions` array in AuthContext
- Fetched from user's role permissions
- Permission format: `resource.action.scope`
  - Example: `courses.view.own`, `courses.manage.any`

### Permission Format Breakdown

| Part | Description | Examples |
|------|-------------|----------|
| `resource` | The resource being accessed | `courses`, `users`, `roles` |
| `action` | The action being performed | `view`, `edit`, `create`, `delete`, `manage` |
| `scope` | The scope of access | `own`, `any`, `all` |

---

## Summary Statistics

| Role | Route Count | Authentication | Special Requirements |
|------|-------------|----------------|---------------------|
| **Guest/Public** | 22 | None | None |
| **Authenticated User** | 6 | Required | Specific permissions per route |
| **Admin** | 6 | Required | `admin.access` + specific permissions |
| **Instructor** | 5 | Required | `instructor.access` + specific permissions |

**Total Implemented Routes:** 39

**Missing/Unimplemented:** 1 (`/admin/orders` - referenced but not defined)

---

## Missing/Unimplemented Routes

Based on code analysis, the following routes are **referenced but not implemented**:

### `/admin/orders`
- **Referenced in:** `Dashboard` component (lines 225, 253)
- **Intended purpose:** Order management
- **Backend support:** `GET /orders`, `GET /orders/{order}` endpoints exist
- **Status:** Not defined in routing configuration

---

## File Locations

| File | Purpose |
|------|---------|
| `frontend/src/App.tsx` | Main routing configuration |
| `frontend/src/components/ProtectedRoute.tsx` | Authentication guard |
| `frontend/src/components/AdminRoute.tsx` | Admin role guard |
| `frontend/src/components/PermissionRoute.tsx` | Permission guard |
| `frontend/src/layouts/PublicLayout.tsx` | Public layout wrapper |
| `frontend/src/layouts/UserLayout.tsx` | User layout wrapper |
| `frontend/src/layouts/AdminLayout.tsx` | Admin layout wrapper |
| `frontend/src/layouts/InstructorLayout.tsx` | Instructor layout wrapper |
| `frontend/src/contexts/AuthContext.tsx` | Auth & permission providers |
