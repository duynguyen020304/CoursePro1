<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-04 -->

# Services

## Purpose
Custom service classes encapsulating business logic. Currently contains authentication service with OAuth integration.

## Key Files

| File | Description |
|------|-------------|
| `AuthService.php` | Google OAuth login, JWT/refresh token management, anti-takeover logic |

## AuthService

### Anti-Takeover Pattern
Prevents Google OAuth account takeover by checking for existing Google-linked accounts first:
```php
// Step A: Look up existing Google-linked account (anti-takeover: always check this first)
```

### Token Storage
- Access tokens: Stored in `personal_access_tokens` table (Sanctum)
- Refresh tokens: Hashed with HMAC-SHA256 before storage (never plaintext)

## For AI Agents

### Working In This Directory
- Services are auto-loaded via PSR-4 `App\Services\` namespace
- Inject services into controllers via method type-hinting
- All services should be stateless

### Security Notes
- Refresh tokens hashed before database storage
- JWT tokens hashed before storage
- Google OAuth anti-takeover check prevents account hijacking

<!-- MANUAL: Custom services notes can be added below -->
