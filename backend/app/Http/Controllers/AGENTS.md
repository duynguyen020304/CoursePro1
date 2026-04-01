<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# Controllers

## Purpose
Laravel controllers handling HTTP requests and returning JSON API responses. Contains 24 controllers organized by feature: authentication, users, courses, content management, cart, orders, and payments.

## Key Files
| File | Purpose |
|------|---------|
| `Controller.php` | Base controller class |
| `AuthController.php` | Login, signup, password recovery |
| `UserController.php` | User CRUD, profile management |
| `CourseController.php` | Course listing, details, CRUD |
| `CartController.php`, `CartItemController.php` | Shopping cart operations |
| `OrderController.php`, `PaymentController.php` | Order and payment handling |

## Controllers List

### Authentication & Users
| Controller | Methods |
|------------|---------|
| `AuthController` | login, signup, forgotPassword, verifyCode, resetPassword, changePassword, logout |
| `UserController` | index, show, store, update, destroy, getProfile, updateProfile |
| `StudentController` | index, show, store, update, destroy, getProfile, hasPurchased |
| `InstructorController` | index, show, store, update, destroy, getProfile |
| `RoleController` | index, show, store, update, destroy |

### Courses & Content
| Controller | Methods |
|------------|---------|
| `CourseController` | list, show, store, update, destroy, getCoursesByInstructor |
| `CategoryController` | index, show, store, update, destroy |
| `CourseInstructorController` | attach, detach, sync |
| `CourseCategoryController` | attach, detach, sync |
| `CourseImageController` | index, show, store, update, destroy |
| `CourseObjectiveController` | index, show, store, update, destroy |
| `CourseRequirementController` | index, show, store, update, destroy |
| `ChapterController` | index, show, store, update, destroy, getChaptersByCourse |
| `LessonController` | index, show, store, update, destroy, getLessonsByChapter |
| `VideoController` | index, show, store, update, destroy, getVideosByLesson |
| `ResourceController` | index, show, store, update, destroy, getResourcesByLesson |

### E-commerce
| Controller | Methods |
|------------|---------|
| `CartController` | getCart, clearCart |
| `CartItemController` | addItem, removeItem |
| `OrderController` | index, show, store (create order) |
| `OrderDetailController` | show, store, update |
| `PaymentController` | processPayment, updateStatus |
| `ReviewController` | index, show, store, update, destroy |

### Other
| Controller | Methods |
|------------|---------|
| `SearchController` | search (course search with filters) |

## For AI Agents

### Working In This Directory
- **Base Class**: All controllers extend `Controller`
- **Response Format**: `{ success: bool, data: mixed, message: string }`
- **Type Hinting**: Use model type hints for route model binding
- **Validation**: Validate request data before operations
- **Error Handling**: Return appropriate HTTP status codes (200, 201, 400, 401, 403, 404, 500)

### Common Patterns
```php
// Typical controller method
public function show($id)
{
    $model = Model::findOrFail($id);
    return response()->json([
        'success' => true,
        'data' => $model,
        'message' => 'Resource retrieved successfully'
    ]);
}

// With validation
public function store(Request $request)
{
    $validated = $request->validate([
        'field' => 'required|string|max:255'
    ]);

    $resource = Model::create($validated);

    return response()->json([
        'success' => true,
        'data' => $resource,
        'message' => 'Resource created successfully'
    ], 201);
}
```

## Dependencies

### Internal
- `backend/app/Models/` - Models used in controllers
- `backend/routes/api.php` - Routes pointing to controllers

<!-- MANUAL: Custom controllers notes can be added below -->
