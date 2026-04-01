<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# Public Pages

## Purpose
Publicly accessible pages that do not require authentication. Includes landing page, course browsing, authentication forms, and informational pages.

## Key Files

| File | Route | Purpose |
|------|-------|---------|
| `Home.jsx` | `/` | Landing page with hero, featured courses, categories, testimonials |
| `Courses.jsx` | `/courses` | Course listing with filters (search, category, difficulty, price) |
| `CourseDetail.jsx` | `/courses/:id` | Individual course page with syllabus, reviews, add-to-cart |
| `CategoryPage.jsx` | `/categories/:id` | Courses filtered by category |
| `SignIn.jsx` | `/signin` | Login form with validation |
| `SignUp.jsx` | `/signup` | Registration form with password confirmation |
| `ForgotPassword.jsx` | `/forgot-password` | 3-step password reset flow |
| `VerifyCode.jsx` | `/verify-code` | Email verification code input |
| `ResetPassword.jsx` | `/reset-password` | New password form |
| `Cart.jsx` | `/cart` | Shopping cart with order summary |
| `Checkout.jsx` | `/checkout` | Multi-payment checkout with card preview |

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
