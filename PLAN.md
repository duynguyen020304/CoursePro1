# CoursePro1 Implementation Plan

## Overview

This plan covers two major improvements:

1. **Authentication Guards**: Prevent authenticated users from accessing login/register pages
2. **Category Slug Routing**: Replace numeric IDs with SEO-friendly slugs in category URLs

---

## Phase 1: Authentication Guard for Public Pages

### Goal
Prevent already authenticated users from accessing `/signin`, `/signup`, and password recovery pages. Redirect them to the homepage if they try to access these pages while logged in.

### Files to Modify

#### 1. `frontend/src/App.jsx`
**Changes:**
- Create a new `PublicOnlyRoute` component (opposite of `ProtectedRoute`)
- Wrap `/signin`, `/signup`, `/forgot-password`, `/verify-code`, `/reset-password` routes with this component
- Redirect authenticated users to `/` (homepage)

**Implementation:**
```jsx
// Public Only Route Component (for auth pages like login/register)
function PublicOnlyRoute({ children }) {
  const { isAuthenticated, loading } = useAuth();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  return isAuthenticated ? <Navigate to="/" replace /> : children;
}
```

**Routes to wrap:**
- `/signin`
- `/signup`
- `/forgot-password`
- `/verify-code`
- `/reset-password`

### Acceptance Criteria
- [ ] Authenticated user navigating to `/signin` is redirected to homepage
- [ ] Authenticated user navigating to `/signup` is redirected to homepage
- [ ] Authenticated user navigating to `/forgot-password` is redirected to homepage
- [ ] Unauthenticated users can still access these pages normally
- [ ] Loading state is shown while checking authentication status

---

## Phase 2: Category Slug-Based Routing

### Goal
Replace numeric category IDs in URLs with human-readable, SEO-friendly slugs.
- Before: `http://localhost:5173/categories/5`
- After: `http://localhost:5173/categories/data-science`

### 2.1 Backend Changes

#### 2.1.1 Database Migration: Add Slug Column to Categories

**File: `backend/database/migrations/YYYY_MM_DD_HHMMSS_add_slug_to_categories_table.php`**

**Migration Steps:**
1. Add `slug` column (string, unique, indexed)
2. Backfill existing categories with slugs (based on name)
3. Update model to include slug in fillable

**Rollback:**
- Remove `slug` column

#### 2.1.2 Update Category Model

**File: `backend/app/Models/Category.php`**

**Changes:**
- Add `slug` to `$fillable` array
- Add slug generation logic (observer or model event)
- Update relationships if needed

**Implementation:**
```php
protected $fillable = ['name', 'slug', 'parent_id', 'sort_order', 'created_at'];

// Optional: Auto-generate slug from name
public static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        $model->created_at = $model->freshTimestamp();
        if (empty($model->slug)) {
            $model->slug = \Str::slug($model->name);
        }
    });

    static::updating(function ($model) {
        if ($model->isDirty('name')) {
            $model->slug = \Str::slug($model->name);
        }
    });
}
```

#### 2.1.3 Update Category Routes

**File: `backend/routes/api.php`**

**Changes:**
- Update route binding to use slug instead of ID
- Option A: Route model binding with `getRouteKeyName()`
- Option B: Explicit binding in RouteServiceProvider

**Implementation:**
Add to Category model:
```php
public function getRouteKeyName()
{
    return 'slug';
}
```

#### 2.1.4 Update CategoryController

**File: `backend/app/Http/Controllers/CategoryController.php`**

**Changes:**
- Update `show()` method to accept slug instead of ID
- Update any queries that filter by category

### 2.2 Frontend Changes

#### 2.2.1 Update CategoryPage Component

**File: `frontend/src/pages/public/CategoryPage.jsx`**

**Changes:**
- Update `useParams()` to use slug instead of id
- Update API calls to pass slug instead of ID
- Update breadcrumb/navigation if present

**Before:**
```jsx
const { id } = useParams();
const categoryRes = await categoryApi.get(id);
```

**After:**
```jsx
const { slug } = useParams();
const categoryRes = await categoryApi.get(slug);
```

#### 2.2.2 Update App.jsx Routes

**File: `frontend/src/App.jsx`**

**Changes:**
- Update route from `/categories/:id` to `/categories/:slug`

**Before:**
```jsx
<Route path="/categories/:id" element={<CategoryPage />} />
```

**After:**
```jsx
<Route path="/categories/:slug" element={<CategoryPage />} />
```

#### 2.2.3 Update Category Links Throughout App

**Files to search and update:**
- `frontend/src/components/Header.jsx`
- `frontend/src/pages/public/Courses.jsx`
- `frontend/src/pages/public/CategoryPage.jsx` (course cards linking to subcategories)
- Any other components linking to categories

**Pattern to find:**
```jsx
to={`/categories/${category.id}`}
// or
href={`/categories/${category.id}`}
```

**Replace with:**
```jsx
to={`/categories/${category.slug}`}
```

#### 2.2.4 Update API Service (Optional)

**File: `frontend/src/services/api.js`**

**Changes:**
- No changes needed if API endpoint is the same (`/categories/{slug}`)
- Update comments/documentation to reflect slug usage

### 2.3 Additional Considerations

#### Category List Display
**File: `frontend/src/pages/public/Courses.jsx` or wherever categories are listed**

Ensure category list fetches and displays slugs:
- Backend API should include `slug` in category responses
- Frontend should store and use `slug` for navigation

#### Breadcrumbs
If breadcrumbs are implemented on category pages, ensure they use slugs.

### Acceptance Criteria
- [ ] Category migration created and run successfully
- [ ] Category model updated with slug support
- [ ] Category routes accept slugs instead of IDs
- [ ] CategoryPage component uses slug from URL params
- [ ] All category links throughout the app use slugs
- [ ] Existing category data backfilled with slugs
- [ ] Navigation to categories works correctly
- [ ] API responses include category slugs

---

## Phase 3: Testing and Verification

### 3.1 Authentication Guard Tests

**Manual Testing:**
1. Log in as a user
2. Try to navigate to `/signin` → Should redirect to `/`
3. Try to navigate to `/signup` → Should redirect to `/`
4. Try to navigate to `/forgot-password` → Should redirect to `/`
5. Log out
6. Verify all pages are accessible when not authenticated

### 3.2 Category Slug Tests

**Manual Testing:**
1. Visit `/categories/data-science` (or any valid slug) → Should load category page
2. Visit `/categories/1` (old ID format) → Should 404 or redirect (based on implementation)
3. Click category links from Courses page → Should use slug URLs
4. Click category links from Header → Should use slug URLs
5. Verify category pages display correct data

**Backend Testing:**
1. Create new category → Slug auto-generated
2. Update category name → Slug updates (if implementing auto-update)
3. API returns slug in category objects

---

## Implementation Order

1. **Phase 1** (Auth Guard) - Can be done independently
   - Simple, isolated change
   - Immediate user experience improvement

2. **Phase 2.1** (Backend Slug Support) - Must be done before frontend
   - Migration
   - Model updates
   - Controller updates

3. **Phase 2.2** (Frontend Slug Integration) - Depends on 2.1
   - Route updates
   - Component updates
   - Link updates throughout app

4. **Phase 3** (Testing) - After all implementation
   - Manual testing
   - Bug fixes if needed

---

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Breaking existing category bookmarks | Medium | Consider adding redirect from old ID URLs to new slug URLs |
| Category slug collisions | Low | Add unique constraint and handle collision in slug generation |
| Auth redirect loop | Low | Test thoroughly with various auth states |
| Non-English category names | Medium | Ensure `Str::slug()` handles Unicode properly |

---

## Files Summary

### Files to Create:
- `backend/database/migrations/YYYY_MM_DD_HHMMSS_add_slug_to_categories_table.php`

### Files to Modify:
- `frontend/src/App.jsx` (Auth guard + route param change)
- `frontend/src/pages/public/CategoryPage.jsx` (slug param)
- `frontend/src/services/api.js` (possibly update comments)
- `backend/app/Models/Category.php` (slug field + route key)
- `backend/app/Http/Controllers/CategoryController.php` (if changes needed)
- `backend/routes/api.php` (if explicit binding needed)

### Files to Search for Category Links:
- `frontend/src/components/Header.jsx`
- `frontend/src/pages/public/Courses.jsx`
- `frontend/src/pages/public/Home.jsx`
- Any other component with category navigation

---

## Post-Implementation

1. Run database migration: `php artisan migrate`
2. Clear cache: `php artisan cache:clear`
3. Test all affected routes manually
4. Consider seeding categories with proper slugs if running fresh
