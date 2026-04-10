<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-07-04 -->

# Backend

## Purpose
Laravel 13.x backend API for the CoursePro1 e-learning platform. Provides RESTful endpoints for authentication, user management, course content, shopping cart, orders, and payments. Uses PostgreSQL 18 with Laravel Sanctum for API token authentication.

## Key Files
| File | Description |
|------|-------------|
| `composer.json` | PHP dependencies - Laravel Framework, Sanctum, Tinker |
| `.env` | Environment configuration (database, cache, session, mail) |
| `artisan` | Laravel CLI command tool |
| `phpunit.xml` | PHPUnit testing configuration |
| `vite.config.js` | Vite bundler for frontend assets |
| `routes/api.php` | Main API route definitions (~110 endpoints) |

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `app/` | Core application code (see `app/AGENTS.md`) |
| `config/` | Laravel configuration files (see `config/AGENTS.md`) |
| `database/` | Migrations, seeders, factories (see `database/AGENTS.md`) |
| `routes/` | API and web route definitions (see `routes/AGENTS.md`) |
| `resources/` | Views, CSS, JS source files (see `resources/AGENTS.md`) |
| `tests/` | Backend test suites (see `tests/AGENTS.md`) |
| `bootstrap/` | Laravel bootstrap files |
| `storage/` | Application storage (logs, uploads) |
| `public/` | Public assets (entry point) |

## For AI Agents

### Working In This Directory
- **Framework**: Laravel 13.x with PHP 8.3+
- **Database**: PostgreSQL 18 via Docker (`docker-compose.postgres-only.yml`)
  - Database: `ecourse`
  - Host: `localhost:5434` (local) or `postgres:5432` (Docker)
  - Credentials: `root` / `rootpassword`
- **Authentication**: Laravel Sanctum for API tokens
- **Environment**: Copy `.env.example` to `.env` and configure
- **Dependencies**: Run `composer install` after changes to `composer.json`
- **Generate key**: Run `php artisan key:generate` for new installations

### Testing Requirements
- Run tests with `./vendor/bin/phpunit`
- Tests located in `tests/` directory
- Feature tests in `tests/Feature/`, Unit tests in `tests/Unit/`
- Ensure tests pass before committing

### Common Patterns
- UUID-based primary keys for all models
- API responses use consistent format: `{ success, data, message }`
- Controllers extend base `Controller` class
- Models use Eloquent ORM with relationships
- Protected routes use `auth:sanctum` middleware
- Admin routes prefixed with `/admin/*`

## Dependencies

### Internal
- `backend/routes/api.php` - Route definitions
- `backend/app/Models/` - Eloquent models
- `backend/app/Http/Controllers/` - Controllers

### External
- `laravel/framework` v13.x - Web framework
- `laravel/sanctum` - API authentication
- `firebase/php-jwt` - JWT handling
- `phpmailer/phpmailer` - Email functionality

## Large Files (>300 lines)

| File | Lines | Purpose |
|------|-------|---------|
| `app/Http/Controllers/AuthController.php` | 405 | Login, signup, password reset, Google OAuth, refresh tokens |
| `app/Http/Controllers/InstructorCourseController.php` | 360 | Instructor course CRUD with stats |
| `app/Services/AuthService.php` | 261 | Google OAuth, token management, anti-takeover |

## Architecture

- **25 Controllers** — Auth, User, Student, Instructor, Course, Cart, Order, Payment, Review, etc.
- **23 Models** — Most use UUID primary keys (see `app/AGENTS.md` for exceptions)
- **3 Middleware** — CheckRole, CheckPermission, UseAccessTokenFromCookie
- **70 Migrations** — User, Course, Cart, Order, Payment, Reviews, Permissions, OAuth, audit columns, etc.
- **No Request classes** — Validation in controllers
- **No Observers/Events** — Direct model operations

## Database Schema

### Core Tables
- `users` - User accounts with UUID primary keys
- `roles` - User roles (admin, student, instructor)
- `instructors`, `students` - Role-specific profiles
- `courses` - Course catalog with pricing, difficulty, language
- `course_chapters`, `course_lessons` - Course content hierarchy
- `course_videos`, `course_resources` - Lesson media and materials
- `categories` - Hierarchical course categories
- `carts`, `cart_items` - Shopping cart system
- `orders`, `order_details`, `payments` - Order management
- `reviews` - Course reviews with ratings

### Audit Columns

All models use the `HasAuditColumns` trait which provides:

**SoftDeletes**: Models use Laravel's SoftDeletes trait internally. Deleted records have `deleted_at` set and are excluded from default queries.

**Query Scopes**:
- `Model::active()` — returns only active records (`where('is_active', true)`)
- `Model::notDeleted()` — explicit alias for SoftDeletes default behavior
- `Model::withTrashed()` — include soft-deleted records (from SoftDeletes)
- `Model::onlyTrashed()` — only soft-deleted records (from SoftDeletes)

**API Query Parameters**:
- `?include_deleted=true` — include soft-deleted records in list endpoints
- `?is_active=false` — filter by active status

**Automatic Behavior**:
- `is_active` defaults to `true` on new records
- `is_active` is cast as boolean automatically
- `deleted_at` is cast as datetime automatically

**Usage**:
```php
use App\Models\Traits\HasAuditColumns;

class MyModel extends Model
{
    use HasAuditColumns;
    // ... rest of model
}
```

## API Endpoints

### Public
- `POST /login`, `/signup` - Authentication
- `POST /forgot-password`, `/verify-code`, `/reset-password` - Password recovery
- `GET /courses`, `/courses/{id}` - Browse courses
- `GET /categories`, `/instructors` - Browse categories and instructors

### Protected (auth:sanctum)
- `GET /user` - Current user info
- `GET/PUT /user/profile` - Profile management
- `GET/POST/DELETE /cart/*` - Cart operations
- `GET/POST /orders` - Order management
- `POST /orders/{id}/payment` - Complete payment

### Admin Only
- `GET/POST/PUT/DELETE /admin/users/*` - User management
- `GET/POST/PUT/DELETE /admin/courses/*` - Course CRUD
- `GET/POST/PUT/DELETE /admin/instructors/*` - Instructor management
- `GET/POST/PUT/DELETE /admin/orders/*` - Order management

<!-- MANUAL: Custom backend notes can be added below -->
