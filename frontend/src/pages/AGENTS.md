<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# Pages

## Purpose
React page components that serve as route handlers. Organized by audience: public pages (accessible to all), user pages (require authentication), and admin pages (require admin role).

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `public/` | Publicly accessible pages (see `public/AGENTS.md`) |
| `user/` | Authenticated user pages (see `user/AGENTS.md`) |
| `admin/` | Admin dashboard pages (see `admin/AGENTS.md`) |

## For AI Agents

### Working In This Directory
- **Route Mapping**: Each component corresponds to a route in `App.jsx`
- **Layouts**: Pages are wrapped by layouts (PublicLayout, UserLayout, AdminLayout)
- **Auth Guards**: User and admin pages check authentication status
- **Data Fetching**: Use TanStack Query or useEffect for API calls
- **Loading States**: Show spinners during async operations

### Page Organization

**Public Pages** (`src/pages/public/`):
- Home, Courses, CourseDetail, CategoryPage
- SignIn, SignUp, ForgotPassword, VerifyCode, ResetPassword
- Cart, Checkout
- About, FAQ, Contact, Privacy, Terms

**User Pages** (`src/pages/user/`):
- MyCourses, Profile, EditProfile, PurchaseHistory
- Certificates, WatchVideo

**Admin Pages** (`src/pages/admin/`):
- Dashboard, CourseManagement, UserManagement
- Revenue, UploadVideo

## Dependencies

### Internal
- `src/components/` - Shared UI components
- `src/contexts/AuthContext.jsx` - Auth state for protected pages
- `src/contexts/CartContext.jsx` - Cart state for cart pages
- `src/services/api.js` - API calls for data fetching

<!-- MANUAL: Custom pages notes can be added below -->
