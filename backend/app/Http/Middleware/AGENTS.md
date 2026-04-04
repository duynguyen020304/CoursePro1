<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-04 -->

# Middleware

## Purpose
HTTP middleware for authentication, authorization, and request preprocessing. Handles role-based access control and token extraction.

## Key Files

| File | Purpose |
|------|---------|
| `CheckRole.php` | Role-based access control (admin, instructor, student) |
| `CheckPermission.php` | Permission-based access control |
| `UseAccessTokenFromCookie.php` | Extracts Bearer token from cookie for Sanctum auth |

## Middleware List

### `CheckRole`
- Checks if authenticated user has required role
- Used on admin/instructor routes
- Redirects to `/unauthorized` if role mismatch

### `CheckPermission`
- Fine-grained permission checking beyond roles
- Checks specific permissions (e.g., `create-course`, `manage-users`)

### `UseAccessTokenFromCookie`
- Prepended to middleware stack
- Extracts `access_token` cookie value
- Sets as Bearer token for `auth:sanctum` middleware

## For AI Agents

### Working In This Directory
- Middleware registered in `bootstrap/app.php`
- Chain middleware: `->middleware(['auth:sanctum', UseAccessTokenFromCookie::class, CheckRole:admin])`
- Custom middleware must implement `Middleware` contract

### Middleware Order
1. `UseAccessTokenFromCookie` (token extraction)
2. `auth:sanctum` (authentication)
3. `CheckRole` / `CheckPermission` (authorization)

<!-- MANUAL: Custom middleware notes can be added below -->
