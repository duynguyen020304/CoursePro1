<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# Frontend

## Purpose
React 19 frontend application for the CoursePro1 e-learning platform. Built with Vite, Tailwind CSS 4, and React Router 7. Provides user interfaces for public browsing, authenticated user features (my courses, profile, certificates), and admin dashboard management.

## Key Files
| File | Description |
|------|-------------|
| `package.json` | Dependencies - React 19, Vite, Tailwind CSS 4, TanStack Query, Zustand |
| `vite.config.js` | Vite config with React plugin, Tailwind 4, API proxy |
| `index.html` | Entry HTML with root div |
| `.env` | Environment: `VITE_API_URL=http://localhost:8000/api` |
| `eslint.config.js` | ESLint flat config with React Hooks plugin |

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `src/` | Source code root (see `src/AGENTS.md`) |
| `public/` | Static public assets |
| `dist/` | Build output (generated) |

## For AI Agents

### Working In This Directory
- **Framework**: React 19 with Vite bundler
- **Styling**: Tailwind CSS 4 (utility-first CSS framework)
- **Routing**: React Router 7 with nested layouts
- **State Management**: React Context (Auth, Cart) + localStorage
- **Forms**: react-hook-form with validation
- **Data Fetching**: TanStack Query (React Query)
- **HTTP Client**: Axios with interceptors for auth tokens
- **Charts**: Chart.js for analytics
- **PDF**: jsPDF for certificate generation
- **Video**: Swiper for carousels, native `<video>` for playback
- **Dependencies**: Run `npm install` after changes to `package.json`
- **Dev Server**: Run `npm run dev` to start Vite dev server

### Testing Requirements
- Run linting with `npm run lint`
- Build verification with `npm run build`
- Test in development mode before committing

### Common Patterns
- **Context Layer**: `AuthContext`, `CartContext` for global state
- **Layouts**: `PublicLayout`, `UserLayout`, `AdminLayout` for page wrappers
- **Services**: `api.js` provides centralized API client with interceptors
- **Pages organized by audience**: `public/`, `user/`, `admin/`
- **Loading states**: Consistent spinner pattern during async operations
- **Error handling**: try/catch with error state display
- **Protected routes**: Check `isAuthenticated` from `AuthContext`

## Dependencies

### Internal
- `src/services/api.js` - Centralized API client
- `src/contexts/AuthContext.jsx` - Authentication state
- `src/contexts/CartContext.jsx` - Shopping cart state
- `src/layouts/` - Page layout components

### External
- `react` v19 - UI framework
- `react-router-dom` v7 - Client-side routing
- `@tanstack/react-query` - Data fetching/caching
- `zustand` - State management
- `axios` - HTTP client
- `react-hook-form` - Form handling
- `tailwindcss` v4 - Styling
- `swiper` - Carousel/slider
- `chart.js` - Data visualization
- `jspdf` - PDF generation
- `@heroicons/react` - Icon library

## Architecture

### Directory Structure
```
src/
├── main.jsx              # Entry point
├── App.jsx               # Root component with routing
├── index.css             # Global styles
├── components/           # Shared UI (Header, Footer)
├── contexts/             # React Context providers
│   ├── AuthContext.jsx   # Auth state, login/logout
│   └── CartContext.jsx   # Cart state management
├── layouts/              # Page layout wrappers
│   ├── PublicLayout.jsx  # Public pages
│   ├── UserLayout.jsx    # Authenticated user pages
│   └── AdminLayout.jsx   # Admin dashboard
├── pages/                # Route pages
│   ├── public/           # Public pages (Home, Courses, SignIn)
│   ├── user/             # User pages (MyCourses, Profile)
│   └── admin/            # Admin pages (Dashboard, Management)
└── services/             # API layer
    └── api.js            # Axios instance + API methods
```

### Routes

**Public Routes**: `/`, `/courses`, `/courses/:id`, `/signin`, `/signup`, `/forgot-password`, `/cart`, `/checkout`, `/categories/:id`

**Protected User Routes**: `/my-courses`, `/profile`, `/purchase-history`, `/certificates`, `/watch/:courseId/:lessonId`

**Protected Admin Routes**: `/admin/dashboard`, `/admin/courses`, `/admin/users`, `/admin/revenue`, `/admin/upload-video`

### API Integration
- Axios instance with base URL from `VITE_API_URL`
- Request interceptor adds `Authorization: Bearer <token>` header
- Response interceptor handles 401 by clearing auth and redirecting
- All API methods organized in modules: `authApi`, `courseApi`, `orderApi`, etc.

<!-- MANUAL: Custom frontend notes can be added below -->
