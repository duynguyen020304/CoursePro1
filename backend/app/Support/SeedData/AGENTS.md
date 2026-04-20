<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-20 | Updated: 2026-04-20 -->

# SeedData

## Purpose
Idempotent seed data classes for database initialization. Each class handles a specific domain (roles, users, courses) with natural key lookups to prevent duplicates.

## Key Files
| File | Description |
|------|-------------|
| `DefaultRoles.php` | Default roles (admin, instructor, student) with UUIDs |
| `DefaultPermissions.php` | All RBAC permissions (50+ permissions) |
| `DefaultCategories.php` | Hierarchical course categories |
| `DefaultUsers.php` | Default admin and test users |
| `DefaultInstructors.php` | Default instructor profiles |
| `DefaultStudents.php` | Default student profiles |

## For AI Agents

### Working In This Directory
- All seed operations are idempotent (safe to run multiple times)
- Uses natural keys for lookups (email, role_id, slug)
- Implements two-pass approach for hierarchical data (categories)

### Common Patterns
- Natural key lookups before insert (firstOrFail, updateOrCreate)
- Hierarchical data: first pass creates entities, second pass links parents
- Dependency order: roles → permissions → users → profiles → categories → courses

## Dependencies

### Internal
- `App\Models\*` - All domain models
- `App\Support\RbacPermissionMap` - Permission definitions

<!-- MANUAL: -->
