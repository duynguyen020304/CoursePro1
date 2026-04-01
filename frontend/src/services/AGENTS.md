<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# Services

## Purpose
Centralized API client layer using Axios. Provides organized API methods grouped by feature (auth, user, course, cart, order, etc.) with automatic token injection and error handling.

## Key Files
| File | Description |
|------|-------------|
| `api.js` | Axios instance configuration and all API methods |

## API Client Setup

### Axios Instance
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});
```

### Request Interceptor
Automatically adds `Authorization: Bearer <token>` header to all requests.

### Response Interceptor
Handles 401 Unauthorized by clearing localStorage and redirecting to sign in.

## API Modules

| Module | Methods |
|--------|---------|
| `authApi` | login, signup, forgotPassword, verifyCode, resetPassword, changePassword, logout |
| `userApi` | getProfile, updateProfile |
| `studentApi` | getProfile, hasPurchased |
| `instructorApi` | getProfile, create, update |
| `courseApi` | list, get, search, + nested (instructors, categories, images, objectives, requirements, chapters, lessons) |
| `lessonApi` | get, update, delete, getVideos, addVideo, updateVideo, deleteVideo, getResources, addResource, updateResource, deleteResource |
| `chapterApi` | list, create, update, delete |
| `categoryApi` | list, get |
| `instructorPublicApi` | list, get |
| `cartApi` | get, addItem, removeItem, clear |
| `orderApi` | list, create, get, completePayment |
| `reviewApi` | list, create, update, delete |

## For AI Agents

### Working In This Directory
- **Export Pattern**: All API methods exported as single `api` object
- **Error Handling**: Wrap calls in try/catch, display errors to users
- **Loading States**: Set loading true before call, false after
- **Token Handling**: Tokens automatically added via interceptor

### Usage Pattern
```javascript
import { api } from '@/services/api';

// In component
const handleLogin = async (credentials) => {
  setLoading(true);
  try {
    const response = await api.authApi.login(credentials);
    // Handle success
  } catch (error) {
    // Handle error
  } finally {
    setLoading(false);
  }
};
```

## Dependencies

### Internal
- `src/contexts/AuthContext.jsx` - Auth methods used in context
- `src/pages/` - API calls from page components

### External
- `axios` - HTTP client library

<!-- MANUAL: Custom services notes can be added below -->
