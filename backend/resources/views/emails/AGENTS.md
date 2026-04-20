<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-20 | Updated: 2026-04-20 -->

# Emails

## Purpose
Blade email templates for transactional emails sent by the application.

## Key Files
| File | Description |
|------|-------------|
| `password-reset.blade.php` | Password reset email template |
| `email-verification.blade.php` | Email verification template |

## For AI Agents

### Working In This Directory
- Use Blade syntax `{{ $variable }}` for output
- Use `{!! $html !!}` for unescaped HTML
- Email HTML should be responsive and inline-styled

### Common Patterns
- Subject line: Clear and actionable
- Call-to-action: Primary button for main action
- Expiry info: Include for time-sensitive codes
- Plain text fallback: Consider text-only versions

## Templates

### Password Reset
- Purpose: Send password reset verification code
- Variables: `$code`, `$expiresAt`
- CTA: Link to reset password page

### Email Verification
- Purpose: Verify email ownership
- Variables: verification code
- CTA: Link to verification endpoint

## Dependencies

### Internal
- `app/Mail/PasswordResetMail.php`
- `app/Mail/EmailVerificationMail.php`

<!-- MANUAL: -->
