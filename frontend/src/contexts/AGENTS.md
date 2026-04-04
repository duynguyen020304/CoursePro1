<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# Contexts

## Purpose
React Context providers for global state management. Provides authentication state (user, token, login/logout) and shopping cart state (items, count, add/remove) with localStorage persistence.

## Key Files
| File | Description |
|------|-------------|
| `AuthContext.jsx` | Authentication state and methods |
| `CartContext.jsx` | Shopping cart state and methods |

## AuthContext

### State
| Property | Type | Description |
|----------|------|-------------|
| `user` | object | Current user object from API |
| `isAuthenticated` | boolean | Whether user is logged in |
| `loading` | boolean | Loading state during auth operations |

### Methods
| Method | Parameters | Description |
|--------|------------|-------------|
| `login` | email, password | Authenticate user, store token |
| `signup` | userData | Register new user |
| `logout` | none | Clear auth state, redirect |
| `updateUser` | userData | Update current user profile |
| `hasRole` | role | Check if user has role |
| `hasPermission` | permission | Check if user has permission |

### Storage
- Token: `localStorage.token`
- User: `localStorage.user`

### Permissions
- `canAccess(auth, permission)` — Check permission from `user.permissions` array
- `hasRole(role)` — Check role from `user.role.role_name`
- Permissions loaded on login, refreshed on profile update

## CartContext

### State
| Property | Type | Description |
|----------|------|-------------|
| `cart` | object | Current cart object |
| `items` | array | Cart items array |
| `itemCount` | number | Total items in cart (for badge) |
| `loading` | boolean | Loading state |

### Methods
| Method | Parameters | Description |
|--------|------------|-------------|
| `fetchCart` | none | Get current user's cart from API |
| `addItem` | courseId, quantity | Add course to cart |
| `removeItem` | cartItemId | Remove item from cart |
| `clearCart` | none | Empty the cart |

### Storage
- Cart: `localStorage.cart`

## For AI Agents

### Working In This Directory
- **Context Creation**: Use `createContext()` and `useContext()` hooks
- **Provider Pattern**: Wrap app with context providers in `App.jsx`
- **Persistence**: Sync state with localStorage
- **Loading States**: Set loading during async operations

### Usage Pattern
```jsx
// In any component
import { useContext } from 'react';
import { AuthContext } from '../contexts/AuthContext';

function MyComponent() {
  const { user, isAuthenticated, logout } = useContext(AuthContext);

  return (
    <div>
      {isAuthenticated ? (
        <p>Welcome, {user.first_name}!</p>
      ) : (
        <Link to="/signin">Sign In</Link>
      )}
    </div>
  );
}
```

## Dependencies

### Internal
- `src/services/api.js` - API calls for auth and cart
- `src/App.jsx` - Providers configured here

<!-- MANUAL: Custom contexts notes can be added below -->
