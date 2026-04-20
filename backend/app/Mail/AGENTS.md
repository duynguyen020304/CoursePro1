<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-20 | Updated: 2026-04-20 -->

# Mail

## Purpose
Email mailable classes for transactional emails. Uses Laravel's Mailable system with PHPMailer for email delivery.

## Key Files
| File | Description |
|------|-------------|
| `PasswordResetMail.php` | Password reset email with verification code |
| `EmailVerificationMail.php` | Email verification email |

## For AI Agents

### Working In This Directory
- All mailables use constructor property promotion
- Public properties are automatically available to views
- Uses Laravel 13's Mailable API (envelope(), content(), attachments())

### Common Patterns
- Subject format: "Action - CoursePro1"
- View location: `resources/views/emails/`
- Queueable for async sending

## Email Templates

### Password Reset
- View: `emails.password-reset`
- Data: `code` (string), `expiresAt` (Carbon)

### Email Verification
- View: `emails.email-verification`
- Data: verification code and expiry

## Dependencies

### Internal
- `resources/views/emails/` - Blade email templates

### External
- `phpmailer/phpmailer` v6.10 - Email transport
- `illuminate/mail` - Laravel mail component

<!-- MANUAL: -->
