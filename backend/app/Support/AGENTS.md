<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-20 | Updated: 2026-04-20 -->

# Support

## Purpose
Helper classes and support utilities for the application. Contains RBAC permission mapping and seed data definitions.

## Key Files
| File | Description |
|------|-------------|
| `RbacPermissionMap.php` | Static RBAC permission-to-role mapping (50+ permissions) |

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `SeedData/` | Seed data classes for idempotent database seeding |

## For AI Agents

### Working In This Directory
- `RbacPermissionMap` is a `final` class with static methods
- Permission constants use UPPER_SNAKE_CASE
- Role codes: `admin`, `instructor`, `student`

### Common Patterns
- Permission naming: `resource.action` (e.g., `courses.view.any`)
- Permission suffixes:
  - `.any` - Can perform on any resource
  - `.own` - Can perform on own resources only
  - `.manage` - Full CRUD access
  - `.view`, `.create`, `.edit`, `.delete` - Specific actions

## RBAC Permissions

### Admin Permissions (50+)
Full access to all resources including user/role management, revenue analytics, and system administration.

### Instructor Permissions
Course management (own courses only), chapter/lesson/video management, instructor dashboard, profile management.

### Student Permissions
Browse courses, cart management, checkout, view own orders and certificates, write reviews.

## Dependencies

### Internal
- `App\Models\Role` - Role model for permission lookups
- `App\Contracts\ISeedDataService` - Seed service interface

<!-- MANUAL: -->
