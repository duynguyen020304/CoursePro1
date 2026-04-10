<!-- Generated: 2026-04-01 | Updated: 2026-07-04 -->

# CoursePro1

## Purpose
A comprehensive, full-stack e-learning platform built with **Laravel 13** (Backend), **React 19** (Frontend), and **MySQL 8.0**. Provides complete solution for online education with features for students, instructors, and administrators including course management, video streaming, shopping cart, and payments.

## Key Files
| File | Description |
|------|-------------|
| `README.md` | Project documentation and setup guide |
| `docker-compose.postgres-only.yml` | PostgreSQL Docker configuration |
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
- **Database**: PostgreSQL 18 via Docker
  - Database: `ecourse`
  - Host: `localhost:5434` (or `postgres:5432` from Docker)
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
│                  PostgreSQL 18                              │
│            Docker: coursepro_postgres                       │
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

## Commands
```bash
docker-compose -f docker-compose.postgres-only.yml up -d   # Start PostgreSQL
cd backend && composer install && php artisan serve      # Backend dev
cd frontend && npm install && npm run dev                 # Frontend dev
cd backend && ./vendor/bin/phpunit                        # Backend tests
cd frontend && npm run lint && npm run build              # Frontend checks
npx playwright test                                       # E2E tests
```

## CI/CD
None configured — manual testing only.

## Quick Start
1. Start PostgreSQL: `docker-compose -f docker-compose.postgres-only.yml up -d`
2. Backend: `cd backend && composer install && php artisan migrate:fresh --seed && php artisan serve`
3. Frontend: `cd frontend && npm install && npm run dev`

## Documentation
- `README.md` - Full setup guide, DB schema, env config
- `backend/AGENTS.md` - Backend architecture, API endpoints, auth, DB schema
- `frontend/AGENTS.md` - Frontend architecture, routes, components, Zod schemas
- `backend/app/AGENTS.md` - Controllers, models, service providers
- `frontend/src/AGENTS.md` - React source structure, component patterns
- `backend/database/AGENTS.md` - Migrations, seeders, factories

<!-- MANUAL: Project-specific notes can be added below -->
