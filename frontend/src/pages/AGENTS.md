<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# Pages

## Purpose
React page components that serve as route handlers. Organized by audience: **32 pages** total across public (13), user (7), admin (6), and instructor (5).

## Subdirectories
| Directory | Pages | Purpose |
|-----------|-------|---------|
| `public/` | 13 | Publicly accessible pages |
| `user/` | 7 | Authenticated user pages |
| `admin/` | 6 | Admin dashboard pages |
| `instructor/` | 5 | Instructor pages |

## For AI Agents

### Working In This Directory
- **Route Mapping**: Each component corresponds to a route in `App.jsx`
- **Layouts**: Pages are wrapped by layouts (PublicLayout, UserLayout, AdminLayout)
- **Auth Guards**: User and admin pages check authentication status
- **Data Fetching**: Use TanStack Query or useEffect for API calls
- **Loading States**: Show spinners during async operations

### Page Organization

**Public Pages** (`src/pages/public/`) — 13 pages:
- Home, Courses, CourseDetail, CategoryPage
- SignIn, SignUp, ForgotPassword, VerifyCode, ResetPassword, AuthCallback
- Cart, Checkout

**User Pages** (`src/pages/user/`) — 7 pages:
- MyCourses, Profile, EditProfile, PurchaseHistory
- Certificates, WatchVideo

**Admin Pages** (`src/pages/admin/`) — 6 pages:
- Dashboard, CourseManagement, UserManagement
- RoleManagement, Revenue, UploadVideo

**Instructor Pages** (`src/pages/instructor/`) — 5 pages:
- Dashboard, MyCourses, CreateCourse, EditCourse, Profile

## Dependencies

### Internal
- `src/components/` - Shared UI components
- `src/contexts/AuthContext.jsx` - Auth state for protected pages
- `src/contexts/CartContext.jsx` - Cart state for cart pages
- `src/services/api.js` - API calls for data fetching

<!-- MANUAL: Custom pages notes can be added below -->
