<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-20 | Updated: 2026-04-20 -->

# Resources

## Purpose
Frontend assets and Blade templates. Contains CSS, JavaScript, and email view templates.

## Key Files
| File | Description |
|------|-------------|
| `css/app.css` | Global stylesheet |
| `js/app.js` | Main JavaScript entry point |
| `js/bootstrap.js` | Laravel bootstrap (Axios, CSRF) |
| `views/welcome.blade.php` | Default welcome page |

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `views/emails/` | Email templates for transactional emails |

## For AI Agents

### Working In This Directory
- This is a Laravel API backend - frontend is React-based
- Resources here are minimal (mostly email templates)
- Email templates use Blade syntax

### Common Patterns
- Email templates: HTML with Blade variables
- CSS: Import for Tailwind (if used in backend)
- JS: Axios configuration for API calls

## Email Templates

### Password Reset
- View: `emails.password-reset`
- Variables: `$code`, `$expiresAt`

### Email Verification
- View: `emails.email-verification`
- Variables: verification code

## Dependencies

### Internal
- `app/Mail/` - Mailable classes use these views

### External
- Laravel Blade - Template engine
- Axios - HTTP client (bootstrap.js)

<!-- MANUAL: -->
