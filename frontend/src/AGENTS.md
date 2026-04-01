<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# Source

## Purpose
React application source code containing all components, pages, layouts, contexts, services, and utilities for the CoursePro1 frontend.

## Key Files
| File | Description |
|------|-------------|
| `main.jsx` | React entry point, renders App component |
| `App.jsx` | Root component with routing and providers |
| `index.css` | Global Tailwind CSS styles |
| `App.css` | Component-specific styles |

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `components/` | Shared UI components (Header, Footer) |
| `contexts/` | React Context providers (Auth, Cart) |
| `layouts/` | Page layout wrappers (Public, User, Admin) |
| `pages/` | Route pages organized by audience (see `pages/AGENTS.md`) |
| `services/` | API client and methods (see `services/AGENTS.md`) |
| `hooks/` | Custom React hooks |
| `utils/` | Utility functions |
| `types/` | TypeScript/JSDoc type definitions |
| `assets/` | Static assets (images, icons) |

## For AI Agents

### Working In This Directory
- **Entry Point**: `main.jsx` renders `<App />` into root div
- **App Component**: Sets up React Router with routes and providers
- **Imports**: Use ES6 imports, barrel exports from `index.js` files
- **Components**: Function components with hooks (no class components)
- **Styling**: Tailwind CSS utility classes, avoid inline styles

### Component Patterns
```jsx
// Basic component structure
function ComponentName({ prop1, prop2 }) {
  // Hooks at top
  const [state, setState] = useState(initialValue);

  // Event handlers
  const handleClick = () => { ... };

  // Render
  return <div className="tailwind-classes">...</div>;
}
```

### State Management
- **Local**: `useState`, `useReducer`
- **Global**: React Context (`AuthContext`, `CartContext`)
- **Persistence**: `localStorage` for auth token, user data, cart

### API Integration
- Import from `services/api.js`
- Use async/await with try/catch
- Loading states with boolean flags
- Error states display to user

## Dependencies

### Internal
- `src/services/api.js` - API client
- `src/contexts/AuthContext.jsx` - Auth state
- `src/contexts/CartContext.jsx` - Cart state
- `src/layouts/` - Layout components

### External
- `react`, `react-dom` - Core React
- `react-router-dom` - Routing
- `@tanstack/react-query` - Data fetching
- `axios` - HTTP client
- `react-hook-form` - Forms
- `tailwindcss` - Styling

<!-- MANUAL: Custom src notes can be added below -->
