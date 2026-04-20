<!-- Generated: 2026-04-01 | Updated: 2026-04-20 -->

# CoursePro1

## Purpose
A comprehensive, full-stack e-learning platform built with **Laravel 13** (Backend), **React 19** (Frontend), and **PostgreSQL 18**. Provides complete solution for online education with features for students, instructors, and administrators including course management, video streaming, shopping cart, payments, and multi-provider authentication.

## Technology Stack

### Backend
- **Framework**: Laravel 13.2.0 (PHP 8.3+)
- **Database**: PostgreSQL 18 via Docker
- **Authentication**: Laravel Sanctum + Custom JWT refresh tokens
- **Video Storage**: AWS S3 with direct-to-S3 multipart uploads
- **Email**: PHPMailer
- **Testing**: PHPUnit 12.5+

### Frontend
- **Framework**: React 19.2.5 with TypeScript 6.0.2
- **Build Tool**: Vite 8.0.8
- **Styling**: Tailwind CSS 4.2.2
- **State Management**: React Context + TanStack Query 5.99.0
- **Routing**: React Router DOM 7.14.0
- **Forms**: React Hook Form 7.72.1 + Zod 4.3.6 validation
- **Charts**: Chart.js 4.5.1
- **PDF**: jsPDF 4.2.1
- **Testing**: Vitest 4.1.4 + Playwright 1.59.1

### DevOps
- **Package Manager**: Bun (primary) with npm fallback
- **Architecture**: Monorepo with separate backend/frontend directories
- **Proxy**: Vite dev server proxies /api to Laravel backend

## Key Files
| File | Description |
|------|-------------|
| `docker-compose.postgres-only.yml` | PostgreSQL Docker configuration |
| `backend/composer.json` | PHP dependencies - Laravel 13, Sanctum, Tinker |
| `backend/.env` | Laravel environment configuration |
| `backend/routes/api.php` | Main API route definitions (50+ endpoints) |
| `frontend/package.json` | React dependencies - React 19, Vite, TanStack Query |
| `frontend/.env` | React/Vite environment variables |
| `frontend/vite.config.js` | Vite config with React plugin, Tailwind 4, API proxy |

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `backend/` | Laravel API backend (see `backend/AGENTS.md`) |
| `frontend/` | React frontend application (see `frontend/AGENTS.md`) |
| `backend/app/` | Core application code (controllers, models, services) |
| `backend/database/` | Migrations and seeders (80+ migration files) |
| `backend/routes/` | API and web route definitions |
| `frontend/src/` | React source code (components, pages, contexts) |

## Project Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    React Frontend                           │
│            React 19, TypeScript, Vite 8                     │
│            Tailwind CSS 4, React Router 7                   │
│            State: Context + TanStack Query                  │
│                     port 5173                               │
└──────────────────────┬──────────────────────────────────────┘
                       │ HTTP/JSON (Axios)
                       │ Cookie-based auth (XSRF)
                       ↓
┌─────────────────────────────────────────────────────────────┐
│                  Laravel Backend                            │
│         Laravel 13, PHP 8.3+, Sanctum Auth                  │
│         UUID primary keys, Soft deletes, RBAC               │
│                     port 8000                               │
└──────────────────────┬──────────────────────────────────────┘
                       │ Eloquent ORM
                       ↓
┌─────────────────────────────────────────────────────────────┐
│                  PostgreSQL 18                              │
│            Docker: coursepro_postgres                       │
│              Database: ecourse                              │
│                 Port: 5434                                  │
└─────────────────────────────────────────────────────────────┘
```

## Key Features

### Authentication
- **Multi-provider**: Email/password + Google OAuth
- **Token strategy**: Laravel Sanctum access tokens + custom JWT refresh tokens
- **Security**: HMAC-SHA256 hashed refresh tokens, XSRF protection
- **Account linking**: Anti-account-takeover logic for OAuth

### Role-Based Access Control (RBAC)
- **Three roles**: Admin, Student, Instructor
- **50+ granular permissions**: Fine-grained access control
- **Permission-based routing**: Middleware guards on protected endpoints
- **Role mapping**: Static permission-to-role mapping

### Course Management
- **Hierarchical content**: Course → Chapters → Lessons → Videos/Resources
- **Multi-instructor support**: Courses can have multiple instructors
- **Rich metadata**: Objectives, requirements, categories, images
- **Video upload**: Direct-to-S3 multipart upload for large files

### E-commerce
- **Shopping cart**: Persistent cart with guest support
- **Order management**: Order history, payment tracking
- **Payment processing**: Multiple payment methods
- **Reviews**: Course reviews with ratings

### User Experience
- **Responsive design**: Mobile-first with Tailwind CSS
- **Real-time updates**: TanStack Query for optimistic updates
- **Video streaming**: Progressive video playback with progress tracking
- **Certificates**: PDF certificate generation on course completion

## For AI Agents

### Working In This Directory
- **Backend Framework**: Laravel 13.x with PHP 8.3+
- **Frontend Framework**: React 19 with TypeScript, Vite 8
- **Database**: PostgreSQL 18 via Docker
  - Database: `ecourse`
  - Host: `localhost:5434` (local) or `postgres:5432` (Docker)
  - Credentials: `root` / `rootpassword`
- **Environment**: Copy `.env.example` files and configure
- **Dependencies**:
  - Backend: `cd backend && composer install`
  - Frontend: `cd frontend && bun install` or `npm install`

### Architecture Patterns
- **Backend**: 
  - Service Layer pattern (AuthService, VideoUploadService, SeedDataService)
  - Repository pattern through Eloquent ORM
  - Middleware-based authorization
  - Trait-based code reuse (HasAuditColumns)
  - UUID primary keys throughout
  - Soft deletes on all models
- **Frontend**:
  - React Context for global state (Auth, Cart)
  - TanStack Query for server state
  - Zod schemas for validation (46 schemas)
  - Centralized API client with interceptors
  - Route guards for protected pages

### Common Patterns
- **API responses**: Consistent format `{ success, data, message }`
- **Error handling**: Interceptors for 401, 419 (CSRF), auth recovery
- **Loading states**: Consistent spinner pattern
- **Form validation**: Zod schemas with react-hook-form
- **Database**: Audit columns (`is_active`, `deleted_at`, timestamps)

## Commands

### Development
```bash
# Start PostgreSQL
docker-compose -f docker-compose.postgres-only.yml up -d

# Backend development
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve

# Frontend development
cd frontend
bun install  # or npm install
cp .env.example .env
bun run dev  # or npm run dev
```

### Testing
```bash
# Backend tests
cd backend
./vendor/bin/phpunit

# Frontend tests
cd frontend
bun run lint       # ESLint
bun run test       # Vitest unit tests
bun run test:run   # Run once
bun run build      # Production build verification

# E2E tests
npx playwright test
```

### Production Build
```bash
# Frontend build
cd frontend
bun run build
# Output: frontend/dist/
```

## CI/CD
None configured — manual testing only.

## Database Schema Overview

### Core Tables
- `users` - User accounts with UUID primary keys
- `user_accounts` - Multi-provider authentication (email, Google)
- `roles` - User roles (admin, student, instructor)
- `permissions` - Granular permissions
- `refresh_tokens` - HMAC-SHA256 hashed refresh tokens

### Course Tables
- `courses` - Course catalog
- `course_chapters` - Course sections
- `course_lessons` - Lessons within chapters
- `course_videos` - Video content with S3 metadata
- `course_resources` - Lesson resources
- `course_images` - Course images (with primary flag)
- `course_objectives` - Learning objectives
- `course_requirements` - Course requirements
- `categories` - Hierarchical categories

### Commerce Tables
- `carts`, `cart_items` - Shopping cart
- `orders`, `order_details` - Order management
- `payments` - Payment records
- `reviews` - Course reviews

### Profile Tables
- `instructors` - Instructor profiles
- `students` - Student profiles

## API Endpoints Overview

### Public Routes
- `POST /api/login`, `/api/signup` - Authentication
- `POST /api/forgot-password`, `/api/verify-code`, `/api/reset-password` - Password recovery
- `POST /api/auth/google` - Google OAuth
- `GET /api/courses` - Browse courses
- `GET /api/categories`, `/api/instructors` - Browse content

### Protected Routes (auth required)
- `GET /api/user` - Current user info
- `GET/PUT /api/user/profile` - Profile management
- `GET/POST/DELETE /api/cart/*` - Cart operations
- `GET/POST /api/orders` - Order management
- `POST /api/orders/{id}/payment` - Complete payment

### Admin Routes (admin.access permission)
- `/api/admin/users/*` - User management
- `/api/admin/courses/*` - Course CRUD
- `/api/admin/roles` - Role/permission management
- `/api/admin/revenue` - Revenue analytics

### Instructor Routes (instructor.access permission)
- `/api/instructor/stats` - Dashboard stats
- `/api/instructor/courses` - Course management

## Documentation
- `README.md` - Full setup guide, DB schema, env config
- `backend/AGENTS.md` - Backend architecture, API endpoints, auth, DB schema
- `frontend/AGENTS.md` - Frontend architecture, routes, components, Zod schemas
- `backend/app/AGENTS.md` - Controllers, models, services
- `frontend/src/AGENTS.md` - React source structure, component patterns
- `backend/database/AGENTS.md` - Migrations, seeders, factories
- `backend/app/Contracts/` - Service interfaces for dependency injection
- `backend/app/Support/` - RBAC permission mapping and seed data
- `backend/app/Mail/` - Email mailable classes
- `backend/config/` - Laravel configuration files
- `backend/resources/` - Frontend assets and Blade templates
- `backend/tests/` - PHPUnit test suite
- `frontend/src/utils/` - Environment validation and API helpers
- `frontend/src/__tests__/` - Vitest and Playwright test suites

## Statistics
- **Backend**: 27 controllers, 26 models, 80+ migrations, 50+ API endpoints
- **Frontend**: 49 TSX components, 60 TS files, 46 Zod schemas, 40+ routes
- **Code**: ~25,000+ lines of TypeScript code
- **Testing**: Vitest + Playwright configured
