<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# Admin Pages

## Purpose
Admin dashboard pages for platform management. Requires admin role authentication. Includes dashboard overview, course management, user management, revenue analytics, and content upload.

## Key Files

| File | Route | Lines | Purpose |
|------|-------|-------|---------|
| `Dashboard.jsx` | `/admin/dashboard` | 313 | Admin overview with stats, notifications |
| `CourseManagement.jsx` | `/admin/courses` | — | Course list table |
| `UserManagement.jsx` | `/admin/users` | — | User list with role badges |
| `RoleManagement.jsx` | `/admin/roles` | 340 | Role/permission management |
| `Revenue.jsx` | `/admin/revenue` | 360 | Revenue analytics with Chart.js |
| `UploadVideo.jsx` | `/admin/upload-video` | 377 | Video upload form |

## For AI Agents

### Working In This Directory
- **Admin Auth Required**: Check for admin role in addition to authentication
- **Redirect**: Redirect non-admin users to `/signin` or `/unauthorized`
- **Layout**: Wrapped by AdminLayout with sidebar navigation
- **Data Tables**: Display lists with pagination, search, filters
- **Forms**: Create/edit modals with validation

### Page Patterns

**Dashboard (Dashboard.jsx)**:
- Stats cards (users, courses, orders, revenue)
- Notifications panel
- Recent orders table
- Quick action links
- Activity charts (optional)

**Course Management (CourseManagement.jsx)**:
- Course data table
- Search and filters
- Create course modal
- Edit course functionality
- Delete confirmation

**User Management (UserManagement.jsx)**:
- User list table
- Search by name/email
- Role badges (admin, student, instructor)
- Edit user role
- Deactivate user

**Revenue Analytics (Revenue.jsx)**:
- Date range picker
- Chart.js trend chart (daily/weekly/monthly)
- Top courses by revenue
- Recent transactions table
- Export functionality

**Upload Video (UploadVideo.jsx)**:
- Course selector dropdown
- Chapter selector
- Lesson selector
- Drag-and-drop video upload
- Free preview toggle
- Submit for review

## Dependencies

### Internal
- `src/layouts/AdminLayout.jsx` - Admin sidebar layout
- `src/contexts/AuthContext.jsx` - Admin role check
- `src/services/api.js` - Admin API endpoints
- `src/components/` - Reusable table, form, modal components

### External
- `chart.js` - Revenue trend visualization
- `react-hook-form` - Form validation
- `swiper` - Optional carousels

<!-- MANUAL: Custom admin pages notes can be added below -->
