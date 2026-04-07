<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-07-04 -->

# Source

## Purpose
React application source code containing all components, pages, layouts, contexts, services, and utilities for the CoursePro1 frontend.

## Key Files
| File | Description |
|------|-------------|
| `main.tsx` | React entry point, renders App component |
| `App.tsx` | Root component with routing and providers |
| `index.css` | Global Tailwind CSS styles |
| `App.css` | Component-specific styles |

## Subdirectories
| Directory | Files | Purpose |
|-----------|-------|---------|
| `components/` | 3 | Shared UI components (Header, Footer, GoogleLoginButton) |
| `contexts/` | 2 | React Context providers (Auth, Cart) |
| `layouts/` | 4 | Page layout wrappers (Public, User, Admin, Instructor) |
| `pages/` | 29 | Route pages organized by audience |
| `services/` | 3 | API client (`api.ts`, `authApi.ts`, `index.ts`) |
| `schemas/` | 46 | Zod validation schemas by domain |
| `hooks/` | 0 | Custom hooks (exported from contexts) |
| `utils/` | 3 | Utility functions (env, apiValidator) |
| `types/` | 0 | Types defined in schemas |
| `assets/` | 3 | Static images |

## For AI Agents

### Working In This Directory
- **Entry Point**: `main.jsx` renders `<App />` into root div
- **App Component**: Sets up React Router with routes and providers
- **Imports**: Use ES6 imports, barrel exports from `index.js` files
- **Components**: Function components with hooks (no class components)
- **Styling**: Tailwind CSS utility classes, avoid inline styles
- **Pages**: Organized by audience (`public/`, `user/`, `admin/`, `instructor/`)
- **Schemas**: Zod validation in `schemas/` - 60+ schemas, all extend `baseEntitySchema`

<!-- MANUAL: Custom src notes can be added below -->
