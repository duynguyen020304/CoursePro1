<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# App

## Purpose
Core application code containing Laravel controllers, Eloquent models, and service providers. This is where all business logic, data models, and HTTP request handling resides.

## Key Files
| File | Description |
|------|-------------|
| `Http/Controllers/Controller.php` | Base controller class extended by all controllers |

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `Http/Controllers/` | Laravel controllers (see `Http/Controllers/AGENTS.md`) |
| `Models/` | Eloquent models (see `Models/AGENTS.md`) |
| `Providers/` | Service providers for dependency injection |

## For AI Agents

### Working In This Directory
- **Controllers**: Extend base `Controller`, handle HTTP requests, return JSON responses
- **Models**: Extend Eloquent Model, define relationships, use UUID primary keys
- **Response Format**: Consistent structure `{ success: bool, data: mixed, message: string }`
- **Authentication**: Use `auth:sanctum` middleware for protected routes
- **Validation**: Validate input in controllers before model operations

### Common Patterns
- **UUID Primary Keys**: All models use `public $incrementing = false; protected $keyType = 'string';`
- **API Response Format**: `{ success: bool, data: mixed, message: string }`
- **Validation**: Direct in controllers (no Request classes)
- **No Observers/Events**: Direct model operations
- Relationships: `hasMany`, `belongsTo`, `belongsToMany` with proper foreign keys
- Controllers inject services/models via method hinting
- Soft deletes not used by default
- Timestamps may be disabled (`public $timestamps = false`) with manual `boot()` method

## Dependencies

### Internal
- `backend/routes/api.php` - Controllers referenced in routes
- `backend/database/migrations/` - Models match migration schemas
- `backend/app/Providers/` - Service container bindings

### External
- Laravel Eloquent ORM
- Laravel Sanctum for authentication

<!-- MANUAL: Custom app notes can be added below -->
