<!-- Generated: 2026-03-30 | Updated: 2026-03-30 -->

# CoursePro1

## Purpose
A comprehensive, full-stack e-learning platform built with PHP, MySQL/Oracle, and modern web technologies. Provides complete solution for online education with features for students, instructors, and administrators including course management, video streaming, shopping cart, and AI-powered recommendations.

## Key Files
| File | Description |
|------|-------------|
| `config.php` | Application configuration - BASE_URI, BASE_URL computation |
| `.env` | Environment variables (API_BASE_URL, JWT_SECRET_KEY) |
| `index.php` | Application entry point |
| `home.php` | Homepage with course listings |
| `courses.php` | Course catalog page |
| `course-detail.php` | Individual course detail page |
| `cart.php` | Shopping cart page |
| `checkout.php` | Checkout and payment page |
| `signin.php` / `signup.php` | Authentication pages |
| `composer.json` | PHP dependencies (firebase/php-jwt, phpmailer) |

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `api/` | REST API endpoints (see `api/AGENTS.md`) |
| `controller/` | PHP controllers for page logic (see `controller/AGENTS.md`) |
| `model/` | Data models, DTOs, business logic (see `model/AGENTS.md`) |
| `service/` | Service layer for database operations (see `service/AGENTS.md`) |
| `admin/` | Admin panel pages and assets (see `admin/AGENTS.md`) |
| `template/` | Shared page templates (header, footer, sidebar) |
| `public/` | Static assets (CSS, JS, images, fonts) |
| `tests/` | PHPUnit test suites |
| `ci_cd/` | CI/CD scripts and configurations |

## For AI Agents

### Working In This Directory
- **Environment**: Configure `.env` file with `API_BASE_URL` (e.g., `http://localhost:8001/api/`) and `JWT_SECRET_KEY`
- **Database**: Supports MySQL and Oracle - configuration in `config.php`
- **PHP Version**: Requires PHP 8.0+
- **Dependencies**: Run `composer install` after changes to `composer.json`

### Architecture Pattern
```
Page (PHP) → Controller → Service → Model (DTO/BLL) → Database
                ↓
              API (REST)
```

### Testing Requirements
- Run tests with `./vendor/bin/phpunit`
- Tests located in `tests/` directory
- Each API has corresponding test file

### Common Patterns
- JWT authentication via `model/auth_helper.php`
- Configuration via `model/config.php` (loads `.env`)
- API responses use `service/service_response.php`
- All APIs use centralized auth via `AuthHelper::requireAuth()`

## Dependencies

### Internal
- `model/config.php` - Centralized configuration
- `model/auth_helper.php` - JWT authentication
- `model/api.php` - API client class

### External
- `firebase/php-jwt` - JWT token handling
- `phpmailer/phpmailer` - Email functionality
- `vlucas/phpdotenv` - Environment variables (if used)

## Security Notes
- JWT tokens expire after 24 hours
- Passwords hashed with bcrypt
- SQL injection prevention via prepared statements
- XSS protection via input sanitization

<!-- MANUAL: Project-specific notes can be added below -->