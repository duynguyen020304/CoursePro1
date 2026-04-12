# CoursePro1 Frontend

React 19 frontend application for the CoursePro1 e-learning platform. Built with Vite 8, TypeScript, Tailwind CSS 4, and React Router 7.

## 🚀 Quick Start

```bash
# Install dependencies
bun install
# or
npm install

# Copy environment file
cp .env.example .env

# Start development server
bun run dev
# or
npm run dev

# Build for production
bun run build
# or
npm run build
```

The application will be available at `http://localhost:5173`

## 📋 Prerequisites

- **Node.js**: 18+ or Bun 1.0+
- **Package Manager**: Bun (recommended) or npm
- **Backend API**: Laravel backend running on port 8000

## 🛠️ Technology Stack

### Core Framework
- **React 19.2.5** - Latest React with concurrent features
- **TypeScript 6.0.2** - Strict type checking enabled
- **Vite 8.0.8** - Lightning-fast build tool

### UI & Styling
- **Tailwind CSS 4.2.2** - Utility-first CSS framework
- **@tailwindcss/vite 4.2.2** - Tailwind Vite integration
- **Swiper 12.1.3** - Touch slider/carousel
- **Framer Motion 12.38.0** - Animation library
- **Chart.js 4.5.1** - Data visualization

### State Management & Data Fetching
- **TanStack React Query 5.99.0** - Powerful server state management
- **React Context** - Client state (Auth, Cart)
- **Zustand 5.0.12** - Lightweight state management

### Routing & Forms
- **React Router DOM 7.14.0** - Client-side routing
- **React Hook Form 7.72.1** - Performant form handling
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
- **ESLint 9.39.4** - Code linting

## 📁 Project Structure

```
src/
├── main.tsx                    # Application entry point
├── App.tsx                     # Root component with routing
├── index.css                   # Global styles
├── vite-env.d.ts              # Vite type definitions
│
├── components/                 # Shared UI components
│   ├── Header.tsx             # Navigation header
│   ├── Footer.tsx             # Site footer
│   ├── GoogleLoginButton.tsx  # OAuth button
│   ├── AdminRoute.tsx         # Admin route guard
│   └── PermissionRoute.tsx    # Permission guard
│
├── contexts/                   # React Context providers
│   ├── AuthContext.tsx        # Authentication state
│   └── CartContext.tsx        # Shopping cart state
│
├── layouts/                    # Page layout wrappers
│   ├── PublicLayout.tsx       # Public pages
│   ├── UserLayout.tsx         # Authenticated users
│   ├── AdminLayout.tsx        # Admin dashboard
│   └── InstructorLayout.tsx   # Instructor portal
│
├── pages/                      # Route components
│   ├── public/                # Public pages
│   ├── user/                  # User pages
│   ├── admin/                 # Admin pages
│   └── instructor/            # Instructor pages
│
├── services/                   # API layer
│   └── api.ts                 # Centralized API client
│
├── schemas/                    # Zod validation schemas
│   ├── auth/                  # Auth schemas
│   ├── course/                # Course schemas
│   └── ...                    # More schemas
│
├── utils/                      # Utility functions
│   ├── env.ts                 # Environment validation
│   └── apiValidator.ts        # API validation
│
└── __tests__/                  # Test files
```

## 🔧 Configuration

### Environment Variables

Create a `.env` file in the root directory:

```env
# API Configuration
VITE_API_URL=http://localhost:8000/api

# Google OAuth
VITE_GOOGLE_CLIENT_ID=your-google-client-id
```

**Required Variables:**
- `VITE_API_URL` - Backend API endpoint
- `VITE_GOOGLE_CLIENT_ID` - Google OAuth client ID

### Vite Configuration

The `vite.config.js` includes:
- React plugin
- Tailwind CSS plugin
- API proxy to backend (port 8000)
- Vitest testing configuration

## 🎨 Styling

### Tailwind CSS 4

This project uses Tailwind CSS 4 with the Vite plugin. Custom styles are in `src/index.css`.

**Color Scheme:**
- Primary: `indigo-600`
- Secondary: Gray scales (`gray-50` to `gray-900`)
- Status: `green-500` (success), `red-500` (error), `amber-500` (warning)

**Common Patterns:**
```tsx
// Responsive containers
<div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

// Grid layouts
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">

// Flex patterns
<div className="flex items-center space-x-4">

// Loading spinner
<div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
```

## 📊 State Management

### React Context
- **AuthContext**: User authentication, permissions, auth methods
- **CartContext**: Shopping cart state and operations

### TanStack Query
- Server state caching and data fetching
- 83+ queries/mutations across the application
- Automatic refetching and cache management
- Optimistic updates for better UX

### Local State
- React `useState` for UI-specific state
- 115 occurrences across 24 files

## 🌐 Routing

### Public Routes
- `/` - Home page
- `/courses` - Course listing
- `/courses/:id` - Course details
- `/signin`, `/signup` - Authentication
- `/forgot-password`, `/reset-password` - Password recovery
- `/cart`, `/checkout` - Shopping flow
- `/categories/:slug` - Category pages

### Protected User Routes
- `/my-courses` - Enrolled courses
- `/profile` - User profile
- `/certificates` - Certificate list
- `/watch/:courseId/:lessonId` - Video player

### Admin Routes
- `/admin/dashboard` - Admin overview
- `/admin/courses` - Course management
- `/admin/users` - User management
- `/admin/roles` - Role/permission management
- `/admin/revenue` - Revenue analytics

### Instructor Routes
- `/instructor/dashboard` - Instructor overview
- `/instructor/courses` - Course management
- `/instructor/courses/create` - Create course
- `/instructor/courses/:courseId/edit` - Edit course

## 🔐 Authentication

### Multi-Provider Authentication
- **Email/Password**: Traditional authentication
- **Google OAuth**: OAuth integration via GoogleLoginButton

### Token Management
- Access tokens stored in cookies (XSRF protected)
- Automatic token refresh on expiry
- Smart auth recovery with public path detection

### Permission System
- Role-based access control (Admin, Student, Instructor)
- Permission-based route guards
- Granular permission checking via AuthContext

## 📡 API Integration

### Centralized API Client

The `src/services/api.ts` provides a centralized API client with:

- **Axios instance** with base URL configuration
- **Request interceptors** for auth token injection
- **Response interceptors** for error handling
- **CSRF protection** with automatic token refresh
- **Smart auth recovery** for public paths

### API Methods

```typescript
// Authentication
authApi.login(credentials)
authApi.signup(data)
authApi.googleLogin(code, redirectUri)

// Courses
courseApi.list(params)
courseApi.get(id)
courseApi.search(query)

// Cart & Orders
cartApi.get()
cartApi.addItem(courseId, quantity)
orderApi.create()

// Instructor
instructorApi.getStats()
instructorApi.createCourse(data)
```

## ✅ Validation

### Zod Schemas

46 validation schemas organized by domain:
- `auth/` - Authentication schemas
- `user/` - User-related schemas
- `course/` - Course schemas
- `instructor/` - Instructor schemas
- `cart/` - Cart schemas
- `order/` - Order schemas
- `common/` - Shared schemas

### BaseEntity Schema

All entity schemas extend the base entity schema:

```typescript
import { extendBaseEntity } from '@/schemas/common';

const courseSchema = extendBaseEntity(z.object({
  title: z.string(),
  price: z.number(),
}));
```

**BaseEntity Fields:**
- `id` (UUID string)
- `is_active` (boolean, default true)
- `is_deleted` (boolean, default false)
- `created_at` (nullable datetime)
- `updated_at` (nullable datetime)

## 🧪 Testing

### Vitest (Unit Testing)

```bash
# Run tests in watch mode
bun run test
# or
npm run test

# Run tests once
bun run test:run
# or
npm run test:run
```

### Playwright (E2E Testing)

```bash
# Run E2E tests
npx playwright test

# Run with UI
npx playwright test --ui
```

### ESLint

```bash
# Run linter
bun run lint
# or
npm run lint
```

## 📦 Build

### Development Build

```bash
bun run dev
# or
npm run dev
```

### Production Build

```bash
bun run build
# or
npm run build

# Preview production build
bun run preview
# or
npm run preview
```

Build output is generated in the `dist/` directory.

## 🎯 Key Features

### User Experience
- **Responsive Design**: Mobile-first approach
- **Real-time Updates**: TanStack Query for optimistic updates
- **Video Streaming**: Progressive video playback
- **Certificates**: PDF generation on course completion
- **Toast Notifications**: User feedback for actions

### Admin Dashboard
- **Revenue Analytics**: Chart.js visualizations
- **User Management**: Role and permission assignment
- **Course Management**: Full CRUD operations
- **Video Upload**: Direct-to-S3 upload interface

### Instructor Portal
- **Course Creation**: Rich course editor
- **Chapter Management**: Organize course content
- **Student Analytics**: Track student progress
- **Profile Management**: Instructor profiles

## 📚 Scripts

```json
{
  "dev": "vite",
  "build": "vite build",
  "lint": "eslint .",
  "preview": "vite preview",
  "test": "vitest",
  "test:run": "vitest --run"
}
```

## 🐛 Troubleshooting

### Common Issues

**Port 5173 already in use:**
```bash
# Kill process on port 5173 (Windows)
netstat -ano | findstr :5173
taskkill /PID <PID> /F

# Or use a different port in vite.config.js
server: {
  port: 3000
}
```

**API connection errors:**
- Ensure backend is running on port 8000
- Check `VITE_API_URL` in `.env` file
- Verify CORS settings in backend

**Module resolution errors:**
```bash
# Clear node_modules and reinstall
rm -rf node_modules
bun install
```

## 📖 Documentation

- **Project Overview**: See root `AGENTS.md`
- **Frontend Architecture**: See `frontend/AGENTS.md`
- **Backend API**: See `backend/AGENTS.md`

## 🤝 Contributing

1. Follow the existing code style
2. Run tests before committing
3. Use TypeScript strict mode
4. Follow the component structure patterns
5. Add Zod schemas for new data models

## 📄 License

[Your License Here]

## 📞 Support

For issues and questions, please refer to the main project documentation.
