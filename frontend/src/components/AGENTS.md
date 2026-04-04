<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-04 -->

# Components

## Purpose
Shared React UI components used across multiple pages. Provides consistent navigation, footer, and authentication buttons.

## Key Files

| File | Purpose |
|------|---------|
| `Header.tsx` | Top navigation with logo, nav links, auth buttons, cart badge |
| `Footer.tsx` | Site footer with links, social, copyright |
| `GoogleLoginButton.tsx` | Google OAuth sign-in button |

## Component Patterns

### Header
- Responsive navigation with mobile menu
- Shows user avatar when authenticated
- Cart item count badge from CartContext
- Conditional rendering: guest vs authenticated user

### Footer
- Four-column layout: About, Courses, Support, Legal
- Social media links
- Copyright with current year

### GoogleLoginButton
- Calls `/api/auth/google` endpoint
- Redirects to Google OAuth flow
- Handles OAuth callback

## For AI Agents

### Working In This Directory
- Function components only (no class components)
- Use Tailwind CSS utility classes
- Import icons from `@heroicons/react`
- Destructure props explicitly

### Styling
- Tailwind CSS utility classes (no inline styles)
- Responsive design with breakpoint prefixes (`md:`, `lg:`)
- Use existing color tokens from design system

<!-- MANUAL: Custom components notes can be added below -->
