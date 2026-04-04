<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# CoursePro1

## Purpose
A comprehensive, full-stack e-learning platform built with **Laravel 13** (Backend), **React 19** (Frontend), and **MySQL 8.0**. Provides complete solution for online education with features for students, instructors, and administrators including course management, video streaming, shopping cart, and payments.

## Key Files
| File | Description |
|------|-------------|
| `README.md` | Project documentation and setup guide |
| `docker-compose.mysql-only.yml` | MySQL 8.0 Docker configuration |
| `.env` | Root environment variables |
| `backend/.env` | Laravel environment configuration |
| `frontend/.env` | React/Vite environment configuration |

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `backend/` | Laravel API backend (see `backend/AGENTS.md`) |
| `frontend/` | React frontend application (see `frontend/AGENTS.md`) |
| `backend/app/` | Core application code (see `backend/app/AGENTS.md`) |
| `backend/database/` | Migrations and seeders (see `backend/database/AGENTS.md`) |
| `backend/routes/` | API route definitions (see `backend/routes/AGENTS.md`) |
| `frontend/src/` | React source code (see `frontend/src/AGENTS.md`) |

## For AI Agents

### Working In This Directory
- **Backend Framework**: Laravel 13.x with PHP 8.3+
- **Frontend Framework**: React 19 with Vite
- **Database**: MySQL 8.0 via Docker
  - Database: `ecourse`
  - Host: `localhost:3306` (or `mysql:3306` from Docker)
  - Credentials: `root` / `rootpassword`
- **Environment**: Copy `.env.example` files and configure
- **Dependencies**:
  - Backend: `cd backend && composer install`
  - Frontend: `cd frontend && npm install`

### Architecture Pattern
```
┌─────────────────────────────────────────────────────────────┐
│                    React Frontend                           │
│            (Vite, Tailwind CSS, React Router)               │
│                     port 5173                               │
└──────────────────────┬──────────────────────────────────────┘
                       │ HTTP/JSON (Axios)
                       ↓
┌─────────────────────────────────────────────────────────────┐
│                  Laravel Backend                            │
│         (Sanctum Auth, Eloquent ORM, REST API)              │
│                     port 8000                               │
└──────────────────────┬──────────────────────────────────────┘
                       │ Eloquent ORM
                       ↓
┌─────────────────────────────────────────────────────────────┐
│                    MySQL 8.0                                │
│              Docker: coursepro_mysql                        │
│              Database: ecourse                              │
└─────────────────────────────────────────────────────────────┘
```

### Testing Requirements
- Backend tests: `cd backend && ./vendor/bin/phpunit`
- Frontend lint: `cd frontend && npm run lint`
- Frontend build: `cd frontend && npm run build`

### Common Patterns
- **Backend**: Laravel Sanctum for API auth, UUID primary keys, Eloquent relationships
- **Frontend**: React Context for state (Auth, Cart), react-hook-form for forms
- **API**: Consistent response format `{ success, data, message }`
- **Database**: 30+ migrations, seeders for test data

## Dependencies

### Backend
- `laravel/framework` v13.x - Web framework
- `laravel/sanctum` - API authentication
- `firebase/php-jwt` - JWT handling
- `phpmailer/phpmailer` - Email functionality

### Frontend
- `react` v19 - UI framework
- `react-router-dom` v7 - Client-side routing
- `@tanstack/react-query` - Data fetching
- `axios` - HTTP client
- `react-hook-form` - Form handling
- `tailwindcss` v4 - Styling
- `chart.js` - Analytics charts
- `jspdf` - PDF generation

## Conventions

### Backend
- **UUID Primary Keys**: All models use `public $incrementing = false; protected $keyType = 'string'`
- **API Response**: `{ success: bool, data: mixed, message: string }`
- **Middleware Chain**: `auth:sanctum` → `UseAccessTokenFromCookie` → `CheckRole`/`CheckPermission`
- **Google OAuth Anti-Takeover**: Check existing Google-linked account before creating new
- **Refresh Tokens**: Hashed with HMAC-SHA256 before storage

### Frontend
- **TypeScript**: Strict mode enabled, `checkJs: true`
- **ESLint**: Allows unused `ALL_CAPS` variables (React components)
- **Zod Schemas**: 60+ validation schemas organized by domain
- **Environment**: `VITE_*` prefix only, runtime validation via Zod
- **State**: React Context (Auth, Cart) + TanStack Query (server state)
- **Axios**: XSRF token handling, automatic Bearer token injection

## Build & Test Commands

```bash
# Backend
cd backend && composer test          # PHPUnit tests
cd backend && ./vendor/bin/phpunit  # Direct PHPUnit

# Frontend
cd frontend && npm test            # Vitest watch
cd frontend && npm run test:run    # Vitest single run
cd frontend && npm run lint         # ESLint
cd frontend && npm run build       # Production build

# E2E
npx playwright test                 # Run E2E tests

# Database
cd backend && php artisan migrate:fresh --seed
```

## CI/CD Status

**No CI/CD pipelines configured** — manual testing only. No GitHub Actions, GitLab CI, or other CI systems.

## Database Schema

### Core Tables
- `users`, `roles`, `instructors`, `students` - User management
- `courses`, `categories` - Course catalog
- `course_chapters`, `course_lessons`, `course_videos` - Course content
- `carts`, `cart_items` - Shopping cart
- `orders`, `order_details`, `payments` - Order management
- `reviews` - Course reviews

## Quick Start

```bash
# Start MySQL Docker
docker-compose -f docker-compose.mysql-only.yml up -d

# Backend setup
cd backend
composer install
php artisan migrate
php artisan db:seed
php artisan serve

# Frontend setup
cd frontend
npm install
npm run dev
```

## Documentation

- `README.md` - Setup guide and project overview
- `backend/AGENTS.md` - Backend architecture and API docs
- `frontend/AGENTS.md` - Frontend architecture and components
- `backend/database/AGENTS.md` - Database schema and seeders

<!-- MANUAL: Project-specific notes can be added below -->
