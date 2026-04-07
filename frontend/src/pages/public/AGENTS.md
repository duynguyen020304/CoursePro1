<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-07-04 -->

# Public Pages

## Purpose
Publicly accessible pages that do not require authentication. Includes landing page, course browsing, authentication forms, and informational pages.

## Key Files

| File | Route | Lines | Purpose |
|------|-------|-------|---------|
| `Home.jsx` | `/` | 420 | Landing page with hero, Swiper sliders, testimonials |
| `Courses.jsx` | `/courses` | 249 | Course listing with filters |
| `CourseDetail.jsx` | `/courses/:id` | 274 | Course page with syllabus, reviews |
| `CategoryPage.tsx` | `/categories/:slug` | — | Courses filtered by category slug |
| `SignIn.jsx` | `/signin` | — | Login form with validation |
| `SignUp.jsx` | `/signup` | 212 | Registration form |
| `ForgotPassword.jsx` | `/forgot-password` | 219 | 3-step password reset |
| `VerifyCode.jsx` | `/verify-code` | — | Verification code input |
| `ResetPassword.jsx` | `/reset-password` | — | New password form |
| `Cart.jsx` | `/cart` | — | Shopping cart |
| `Checkout.jsx` | `/checkout` | 671 | Multi-payment checkout |
| `AuthCallback.jsx` | `/auth/callback` | — | OAuth callback handler |

## For AI Agents

### Working In This Directory
- **No Auth Required**: These pages are accessible without login
- **Forms**: Use react-hook-form for validation
- **API Calls**: Use api.js methods for data fetching
- **Navigation**: Redirect to user pages after successful login
- **Cart**: Cart pages use CartContext for state

### Page Patterns

**Landing Page (Home.jsx)**:
- Hero section with CTA
- Featured courses slider (Swiper)
- Categories grid
- Instructor showcase
- Testimonials
- Final CTA section

**Course Browsing (Courses.jsx, CategoryPage.jsx)**:
- Filter sidebar (search, category, difficulty, price range)
- Course cards grid
- Pagination
- Sorting options

**Auth Forms (SignIn, SignUp, ForgotPassword)**:
- react-hook-form validation
- Error message display
- Loading states
- Success redirects

**Checkout Flow (Cart, Checkout)**:
- Cart items list with remove option
- Order summary (subtotal, total)
- Payment method selection (credit card, PayPal, bank transfer)
- Card preview for course purchase

## Dependencies

### Internal
- `src/components/Header.jsx`, `Footer.jsx` - Page layout
- `src/contexts/AuthContext.jsx` - For auth redirects
- `src/contexts/CartContext.jsx` - For cart operations
- `src/services/api.js` - API calls

<!-- MANUAL: Custom public pages notes can be added below -->
