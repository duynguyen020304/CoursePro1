<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-20 | Updated: 2026-04-20 -->

# Config

## Purpose
Laravel configuration files for application behavior, database connections, authentication, and third-party services.

## Key Files
| File | Description |
|------|-------------|
| `app.php` | Core application config (debug, URL, timezone, locale) |
| `auth.php` | Authentication guards and providers |
| `database.php` | PostgreSQL and Redis connections |
| `sanctum.php` | Laravel Sanctum API token authentication |
| `mail.php` | Email configuration (PHPMailer) |
| `cors.php` | CORS settings for SPA |
| `video_uploads.php` | AWS S3 video upload configuration |
| `filesystems.php` | Storage disks (local, S3) |

## Configuration Categories

### Authentication
- Guards: `web` (session), `api` (Sanctum tokens)
- Providers: Eloquent user provider
- Password reset: Laravel default with custom tokens

### Database
- Default: PostgreSQL (`ecourse` database)
- Connection: localhost:5434 (local), postgres:5432 (Docker)
- Redis: Cache and session driver

### API Authentication
- Sanctum stateful domains: SPA domains
- Token expiration: 4 hours (access tokens)
- Personal access tokens: Enabled

### File Storage
- Local disk: `storage/app`
- S3 disk: Video uploads with direct uploads

### CORS
- Allow origins: SPA development and production
- Allow methods: GET, POST, PUT, DELETE, OPTIONS
- Allow headers: Authorization, Content-Type, XSRF-TOKEN

## For AI Agents

### Working In This Directory
- All config files return arrays
- Use `config('filename.key')` to access values
- Environment variables: `env('KEY', 'default')`

### Common Patterns
- Database credentials in `.env` only
- Feature flags via config values
- Service credentials via environment variables

## Dependencies

### External
- Laravel Framework - Config system
- PostgreSQL - Default database
- Redis - Cache/session store
- AWS S3 - Video storage

<!-- MANUAL: -->
