<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# Routes

## Purpose
Laravel route definitions for API endpoints and web routes. The `api.php` file contains 180+ RESTful endpoints for the e-learning platform, organized by feature (auth, users, courses, cart, orders).

## Key Files
| File | Description |
|------|-------------|
| `api.php` | Main API routes with Sanctum middleware |
| `web.php` | Basic web routes (welcome page) |
| `console.php` | Artisan CLI commands |

## For AI Agents

### Working In This Directory
- **API Routes** (`api.php`): All endpoints use `Route::prefix('api')`
- **Middleware**: Protected routes use `auth:sanctum`
- **Route Groups**: Admin routes grouped under `/admin/*` prefix
- **HTTP Verbs**: `GET` for retrieval, `POST` for create, `PUT` for update, `DELETE` for remove
- **Route Names**: Use dot notation for resource routes (e.g., `courses.list`)

### Route Structure

**Public Routes (no auth required):**
```php
POST /api/login
POST /api/signup
POST /api/forgot-password
POST /api/verify-code
POST /api/reset-password
GET  /api/courses/search
GET  /api/courses
GET  /api/courses/{id}
GET  /api/categories
GET  /api/categories/{id}
GET  /api/instructors
GET  /api/instructors/{id}
```

**Protected Routes (auth:sanctum):**
```php
GET  /api/user
GET/PUT /api/user/profile
PUT  /api/user/change-password
GET/POST/DELETE /api/cart/*
GET/POST /api/orders
POST /api/orders/{id}/payment
```

**Admin Routes (auth:sanctum + admin role):**
```php
GET/POST/PUT/DELETE /api/admin/users/*
GET/POST/PUT/DELETE /api/admin/courses/*
GET/POST/PUT/DELETE /api/admin/instructors/*
GET/POST/PUT/DELETE /api/admin/orders/*
```

### Common Patterns
- Resource controllers: `Route::apiResource('courses', CourseController::class)`
- Nested routes: `Route::prefix('courses/{course}')->group(...)`
- Middleware chaining: `->middleware(['auth:sanctum'])`
- Route model binding implicit with type hinting in controllers

## Dependencies

### Internal
- `backend/app/Http/Controllers/` - Controllers referenced in routes
- `backend/app/Models/` - Models for route model binding

<!-- MANUAL: Custom routes notes can be added below -->
