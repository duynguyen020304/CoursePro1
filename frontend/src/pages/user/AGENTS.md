<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# User Pages

## Purpose
Authenticated user pages requiring login. Includes personal course management, profile settings, purchase history, certificates, and video learning interface.

## Key Files

| File | Route | Purpose |
|------|-------|---------|
| `MyCourses.jsx` | `/my-courses` | User's purchased courses grid |
| `Profile.jsx` | `/profile` | View/edit profile form |
| `EditProfile.jsx` | `/edit-profile` | Edit profile page |
| `PurchaseHistory.jsx` | `/purchase-history` | Order history with course details |
| `Certificates.jsx` | `/certificates` | Earned certificates with PDF generation |
| `WatchVideo.jsx` | `/watch/:courseId/:lessonId?` | Video player with lesson navigation |

## For AI Agents

### Working In This Directory
- **Auth Required**: All pages require `isAuthenticated` from AuthContext
- **Redirect**: Redirect to `/signin` if not authenticated
- **User Data**: Fetch user-specific data from API
- **Layout**: Wrapped by UserLayout (max-width container)

### Page Patterns

**My Courses (MyCourses.jsx)**:
- Grid of purchased course cards
- Filter by category, status
- Progress indicators
- "Continue Learning" CTAs

**Profile Management (Profile.jsx, EditProfile.jsx)**:
- Display user information
- Edit form with validation
- Password change option
- Avatar upload (if implemented)

**Purchase History (PurchaseHistory.jsx)**:
- Order list with dates
- Course details per order
- Payment status badges
- Download invoice option

**Certificates (Certificates.jsx)**:
- List of earned certificates
- PDF generation with jsPDF
- Preview modal
- Download/share options

**Video Player (WatchVideo.jsx)**:
- Video player component
- Lesson navigation sidebar
- Progress tracking
- Resources download tab
- Notes tab
- Announcements tab
- Mark complete button

## Dependencies

### Internal
- `src/layouts/UserLayout.jsx` - Page wrapper
- `src/contexts/AuthContext.jsx` - Auth check
- `src/services/api.js` - User data API calls
- `src/components/` - Reusable UI components

### External
- `jspdf` - PDF generation for certificates
- `react-player` or native `<video>` - Video playback

<!-- MANUAL: Custom user pages notes can be added below -->
