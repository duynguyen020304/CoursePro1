<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-12 -->

# Backend

## Purpose
Laravel 13.x backend API for the CoursePro1 e-learning platform. Provides RESTful endpoints for authentication, user management, course content, shopping cart, orders, and payments. Uses PostgreSQL 18 with Laravel Sanctum for API token authentication and custom JWT refresh tokens.

## Technology Stack

### Core Framework
- **Laravel 13.2.0** - PHP framework (latest version)
- **PHP 8.3+** - Server-side language
- **PostgreSQL 18** - Database (via Docker)
- **Composer** - Dependency management

### Authentication & Security
- **Laravel Sanctum 4.3.1** - API authentication
- **Firebase JWT 6.11** - JSON Web Token handling
- **Custom Refresh Tokens** - HMAC-SHA256 hashed refresh tokens

### Additional Libraries
- **PHPMailer 6.10** - Email sending functionality
- **PHP-FFmpeg 1.3** - Video/audio processing

### Development Tools
- **Laravel Tinker 3.0.0** - REPL for Laravel
- **Laravel Pail 1.2.6** - Log watching
- **Laravel Pint 1.29.0** - Code style fixer
- **PHPUnit 12.5+** - PHP testing framework
- **Mockery 1.6** - Testing mock framework
- **FakerPHP 1.23** - Test data generation

## Key Files
| File | Description |
|------|-------------|
| `composer.json` | PHP dependencies - Laravel Framework, Sanctum, Tinker |
| `.env` | Environment configuration (database, cache, session, mail) |
| `.env.example` | Environment template |
| `artisan` | Laravel CLI command tool |
| `phpunit.xml` | PHPUnit testing configuration |
| `vite.config.js` | Vite bundler for frontend assets |
| `routes/api.php` | Main API route definitions (50+ endpoints) |

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `app/` | Core application code (see `app/AGENTS.md`) |
| `config/` | Laravel configuration files |
| `database/` | Migrations, seeders, factories (see `database/AGENTS.md`) |
| `routes/` | API and web route definitions (see `routes/AGENTS.md`) |
| `resources/` | Views, CSS, JS source files |
| `tests/` | Backend test suites |
| `bootstrap/` | Laravel bootstrap files |
| `storage/` | Application storage (logs, uploads) |
| `public/` | Public assets (entry point) |

## Architecture

### Overall Architecture
- **Service Layer pattern** (AuthService, VideoUploadService, SeedDataService)
- **Repository pattern** through Eloquent ORM
- **Middleware-based authorization** (CheckRole, CheckPermission)
- **Trait-based code reuse** (HasAuditColumns)
- **Interface-based dependency injection** (ISeedDataService)

### Design Patterns
- **UUID primary keys** for all models (not auto-incrementing)
- **Soft deletes** implementation on all models
- **Role-Based Access Control (RBAC)** system
- **Multi-provider authentication** (Email + Google OAuth)

### Directory Structure
```
app/
├── Contracts/           # Interfaces (ISeedDataService)
├── Http/
│   ├── Controllers/     # 27 controllers
│   ├── Middleware/      # 5 custom middleware
│   └── Controllers/Traits/
├── Mail/                # Email classes
├── Models/              # 26 models
│   └── Traits/          # HasAuditColumns
├── Providers/           # AppServiceProvider
├── Services/            # 4 core services
└── Support/
    ├── RbacPermissionMap.php
    └── SeedData/        # 6 seed data classes
```

## Models & Data Structure

### Core Models (26 total)

**User Management:**
- `User` - Core user profile (name, role_id, profile_image, is_active)
- `UserAccount` - Multi-provider authentication (email, Google OAuth)
- `RefreshToken` - JWT refresh tokens with HMAC-SHA256 hashing
- `Role` - RBAC roles (admin, student, instructor)
- `Permission` - Granular permissions

**User Profiles:**
- `Instructor` - Instructor profiles (biography)
- `Student` - Student profiles

**Course Management:**
- `Course` - Main course entity
- `CourseChapter` - Course chapters
- `CourseLesson` - Lessons within chapters
- `CourseVideo` - Video content with S3 metadata
- `CourseResource` - Lesson resources
- `CourseImage` - Course images (with primary flag)
- `CourseObjective` - Learning objectives
- `CourseRequirement` - Course requirements
- `Category` - Course categories (hierarchical)

**Commerce:**
- `Cart` - Shopping cart
- `CartItem` - Cart items
- `Order` - Orders
- `OrderDetail` - Order line items
- `Payment` - Payment records
- `Review` - Course reviews

**Authentication Tokens:**
- `EmailVerificationToken` - Email verification tokens
- `PasswordResetToken` - Password reset tokens

### Model Relationships
- User → UserAccount (1:many, multi-provider)
- User → Role (many:1)
- User → Instructor/Student (1:1 polymorphic)
- Course → Instructor (many:many via course_instructor)
- Course → Category (many:many via course_category)
- Course → Chapters → Lessons → Videos/Resources
- Course → Images, Objectives, Requirements
- User → Cart → CartItems
- User → Orders → OrderDetails
- Order → Payment

## Controllers (27 Total)

### Authentication
- `AuthController` (405 lines) - Login, signup, OAuth, password reset, refresh tokens

### User Management
- `UserController` - User CRUD, role assignment
- `StudentController` - Student operations
- `InstructorController` - Instructor profiles

### Course Management
- `CourseController` - Course CRUD
- `InstructorCourseController` (360 lines) - Instructor-specific course operations
- `ChapterController` - Chapter management
- `LessonController` - Lesson management
- `VideoController` - Video upload with S3 multipart
- `ResourceController` - Lesson resources

### Course Meta Controllers
- `CourseImageController` - Course image management
- `CourseObjectiveController` - Learning objectives
- `CourseRequirementController` - Course requirements
- `CourseInstructorController` - Course-instructor relationships
- `CourseCategoryController` - Course-category relationships
- `CategoryController` - Category CRUD

### Commerce
- `CartController` - Shopping cart operations
- `CartItemController` - Cart item management
- `OrderController` - Order management
- `OrderDetailController` - Order details
- `PaymentController` - Payment processing

### Other
- `ReviewController` - Course reviews
- `RoleController` - Role management
- `SearchController` - Course search

## API Endpoints & Routing

### Public Routes (no authentication)
- `POST /api/login` - Email/password login
- `POST /api/signup` - User registration
- `POST /api/forgot-password` - Password reset request
- `POST /api/auth/google` - Google OAuth
- `GET /api/courses` - Browse courses
- `GET /api/courses/search` - Search courses
- `GET /api/categories` - Browse categories
- `GET /api/instructors` - Browse instructors

### Protected Routes (require authentication)
- `GET /api/user` - Current user info
- `PUT /api/user/profile` - Update profile
- `POST /api/auth/logout` - Logout
- `POST /api/auth/refresh` - Refresh access token

### Email Verified Required
- Cart operations (`/api/cart/*`)
- Order operations (`/api/orders/*`)
- Payment operations (`/api/orders/{order}/payment`)
- Review operations (`/api/reviews/*`)

### Admin Routes (permission: `admin.access`)
- User/Student/Instructor management
- Role/Permission management
- Course/Category full management
- Payment status updates

### Instructor Routes (permission: `instructor.access`)
- `/api/instructor/stats` - Dashboard stats
- `/api/instructor/courses` - Instructor course management
- Course image management

### Permission-Based Course Management
- Routes protected by `permission:courses.manage.any,courses.manage.own,admin.access`
- Chapter, Lesson, Video, Resource creation/editing
- Course metadata (images, objectives, requirements)

## Services

### AuthService (261 lines)
**Purpose:** Google OAuth integration, token management, user account linking

**Key Features:**
- Google token exchange
- User info fetching from Google
- Anti-account-takeover logic (links existing accounts)
- Refresh token creation with HMAC-SHA256 hashing
- Token validation and revocation

### VideoUploadService
**Purpose:** Direct-to-S3 video uploads

**Key Features:**
- Direct-to-S3 video uploads
- Multipart upload support for large files (>50MB)
- Signed URL generation
- Upload completion and abortion

### SeedDataService
**Purpose:** Idempotent database seeding

**Key Features:**
- Idempotent database seeding
- Natural key lookups to prevent duplicates
- Seeds: roles, permissions, categories, users, instructors, students, courses, orders

### PasswordResetService
**Purpose:** Password reset token management

**Key Features:**
- Password reset token generation
- Token validation

## Authentication System

### Multi-Provider Architecture
1. **Email/Password**: Traditional authentication via UserAccount model
2. **Google OAuth**: Via AuthService

### Token Strategy
- **Access Tokens**: Laravel Sanctum personal access tokens
- **Refresh Tokens**: Custom implementation, 7-day expiry, HMAC-SHA256 hashed
- Tokens stored in `personal_access_tokens` and `refresh_tokens` tables

### Middleware
1. `CheckRole` - Role-based authorization (`role:admin,instructor`)
2. `CheckPermission` - Permission-based authorization (`permission:courses.manage`)
3. `EnsureEmailVerified` - Email verification required
4. `UseAccessTokenFromCookie` - Extracts token from cookie for SPA

### Authentication Flow
1. Frontend sends access_token in cookie or Authorization header
2. Middleware validates token via Sanctum
3. User model loaded with role and permissions
4. Controllers check permissions via middleware or model methods

## RBAC System

### Three Roles
1. **Admin** (`11111111-1111-1111-1111-111111111111`) - Full system access
2. **Student** (`22222222-2222-2222-2222-222222222222`) - Learner permissions
3. **Instructor** (`33333333-3333-3333-3333-333333333333`) - Course creation/management

### Permission Mapping (`RbacPermissionMap.php`)
- Static mapping of permissions to roles
- 50+ granular permissions defined
- Examples:
  - `admin.access` - Admin panel access
  - `instructor.access` - Instructor panel access
  - `courses.manage.any` - Manage any course
  - `courses.manage.own` - Manage own courses only
  - `orders.view.own` - View own orders
  - `cart.manage.own` - Manage own cart

## Database Structure

### Database: PostgreSQL (`ecourse`)
- Host: 127.0.0.1
- Port: 5434
- Connection: Via Docker (`docker-compose.postgres-only.yml`)

### Key Tables

**User Management:**
- `users` - UUID primary, role_id FK
- `user_accounts` - Multi-provider auth (provider, provider_account_id)
- `roles` - (admin, student, instructor)
- `permissions` - Granular permissions
- `permission_role` - Junction table
- `refresh_tokens` - HMAC-SHA256 hashed tokens

**User Profiles:**
- `instructors` - Instructor profiles
- `students` - Student profiles

**Course Management:**
- `courses` - UUID primary, created_by FK to instructor
- `course_chapters` - Course sections
- `course_lessons` - Chapter lessons
- `course_videos` - Videos with S3 metadata (storage_key, upload_status)
- `course_resources` - Lesson resources
- `course_images` - Course images (is_primary flag)
- `course_objectives` - Learning objectives
- `course_requirements` - Prerequisites
- `categories` - Hierarchical categories (parent_id)
- `course_instructor` - Junction table
- `course_category` - Junction table

**Commerce:**
- `carts` - Shopping carts
- `cart_items` - Cart line items
- `orders` - Orders
- `order_details` - Order line items
- `payments` - Payment records
- `reviews` - Course reviews

**Authentication:**
- `email_verification_tokens`
- `password_reset_tokens`

### Common Columns (via HasAuditColumns trait)
- `is_active` - Boolean (default true)
- `deleted_at` - Soft deletes timestamp
- `created_at`, `updated_at` - Timestamps

### Audit Columns Features

**SoftDeletes:** Models use Laravel's SoftDeletes trait. Deleted records have `deleted_at` set and are excluded from default queries.

**Query Scopes:**
- `Model::active()` — returns only active records (`where('is_active', true)`)
- `Model::notDeleted()` — explicit alias for SoftDeletes default behavior
- `Model::withTrashed()` — include soft-deleted records
- `Model::onlyTrashed()` — only soft-deleted records

**API Query Parameters:**
- `?include_deleted=true` — include soft-deleted records in list endpoints
- `?is_active=false` — filter by active status

**Automatic Behavior:**
- `is_active` defaults to `true` on new records
- `is_active` is cast as boolean automatically
- `deleted_at` is cast as datetime automatically

**Usage:**
```php
use App\Models\Traits\HasAuditColumns;

class MyModel extends Model
{
    use HasAuditColumns;
    // ... rest of model
}
```

## Configuration Files

### Key Configurations
- `config/sanctum.php` - Sanctum stateful domains, guards, expiration
- `config/database.php` - PostgreSQL connection, Redis config
- `config/video_uploads.php` - S3 video upload settings
- `config/auth.php` - Authentication guards
- `config/mail.php` - Email configuration
- `config/cors.php` - CORS settings

### Environment Variables (`.env.example`)
- Database: PostgreSQL on port 5434
- AWS S3: Video storage configuration
- Google OAuth: Client ID/Secret
- Sanctum: Stateful domains for SPA
- Session: Database driver

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
- **UUID primary keys** for all models
- **API responses** use consistent format: `{ success, data, message }`
- **Controllers** extend base `Controller` class
- **Models** use Eloquent ORM with relationships
- **Protected routes** use `auth:sanctum` middleware
- **Admin routes** prefixed with `/admin/*`
- **Soft deletes** on all models via `HasAuditColumns` trait
- **Service layer** for business logic (AuthService, VideoUploadService, etc.)

### Key Features

**Video Upload System:**
- Direct-to-S3 multipart uploads
- Automatic mode selection (single vs multipart)
- Upload state tracking (uploading → ready)
- S3 metadata storage (storage_key, upload_id, etag)

**Email Verification:**
- Verification tokens with expiration
- Middleware-protected routes requiring verified email
- Resend verification functionality

**Search Functionality:**
- Course search via SearchController
- Category-based browsing
- Instructor profiles

## Dependencies

### Internal
- `routes/api.php` - Route definitions
- `app/Models/` - Eloquent models
- `app/Http/Controllers/` - Controllers
- `app/Services/` - Business logic services

### External
- `laravel/framework` v13.x - Web framework
- `laravel/sanctum` v4.x - API authentication
- `firebase/php-jwt` v6.x - JWT handling
- `phpmailer/phpmailer` v6.x - Email functionality
- `php-ffmpeg/php-ffmpeg` v1.x - Video processing

## Large Files (>300 lines)

| File | Lines | Purpose |
|------|-------|---------|
| `app/Http/Controllers/AuthController.php` | 405 | Login, signup, password reset, Google OAuth, refresh tokens |
| `app/Http/Controllers/InstructorCourseController.php` | 360 | Instructor course CRUD with stats |
| `app/Services/AuthService.php` | 261 | Google OAuth, token management, anti-takeover |

## Notable Architectural Decisions

1. **UUID Primary Keys**: All tables use UUIDs for distributed system compatibility
2. **Multi-Provider Auth**: Separate User and UserAccount models for OAuth flexibility
3. **Soft Deletes**: No data destruction, audit trail maintained
4. **HMAC Token Hashing**: Refresh tokens hashed before storage for security
5. **Service Layer**: Business logic in services, controllers remain thin
6. **Permission-Based RBAC**: Fine-grained permissions with role mapping
7. **Idempotent Seeding**: Seed data safe to run multiple times
8. **S3 Direct Upload**: Frontend uploads directly to S3, backend presigns URLs

## Commands

### Development
```bash
# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database migration
php artisan migrate:fresh --seed

# Start development server
php artisan serve

# Run tests
./vendor/bin/phpunit

# Code styling
./vendor/bin/pint

# Tinker (REPL)
php artisan tinker

# Log watching
php artisan pail
```

### Database
```bash
# Run migrations
php artisan migrate

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback migration
php artisan migrate:rollback

# Create new migration
php artisan make:migration create_table_name
```

### Testing
```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite=Feature

# Run specific test
./vendor/bin/phpunit tests/Feature/ExampleTest.php
```
