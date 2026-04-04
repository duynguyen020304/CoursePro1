<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-04 -->

# Layouts

## Purpose
Page layout wrappers providing consistent shell structure for different user roles. Each layout includes sidebar navigation and content area.

## Key Files

| File | Audience | Sidebar Items |
|------|---------|---------------|
| `PublicLayout.tsx` | Guests | None (minimal) |
| `UserLayout.tsx` | Students | Dashboard, My Courses, Profile |
| `AdminLayout.tsx` | Admins | Full admin menu with all management sections |
| `InstructorLayout.tsx` | Instructors | Course management, profile, stats |

## Layout Structure

### PublicLayout
- Minimal layout for unauthenticated users
- No sidebar
- Used by: Home, Courses, SignIn, SignUp, Cart

### UserLayout
- Student dashboard layout
- Left sidebar with navigation
- Used by: MyCourses, Profile, Certificates, WatchVideo

### AdminLayout
- Full admin sidebar with all management sections
- Stats cards in header
- Used by: Dashboard, UserManagement, CourseManagement, Revenue

### InstructorLayout
- Instructor-specific sidebar
- Course creation/editing navigation
- Used by: CreateCourse, EditCourse, MyCourses, Profile

## For AI Agents

### Working In This Directory
- Layouts wrap page content with consistent structure
- Import layout components in `App.tsx` route definitions
- Use `<Outlet />` from react-router-dom for nested content
- Sidebar state (collapsed/expanded) managed locally

### Navigation Pattern
```jsx
<AdminLayout>
  <Outlet /> {/* Page content renders here */}
</AdminLayout>
```

<!-- MANUAL: Custom layouts notes can be added below -->
