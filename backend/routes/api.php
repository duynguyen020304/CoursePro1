<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseInstructorController;
use App\Http\Controllers\CourseCategoryController;
use App\Http\Controllers\CourseImageController;
use App\Http\Controllers\CourseObjectiveController;
use App\Http\Controllers\CourseRequirementController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\InstructorCourseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:5,15');
Route::post('/forgot-password-jwt', [AuthController::class, 'forgotPasswordJwt'])
    ->middleware('throttle:5,15');
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Google OAuth routes
Route::post('/auth/google', [AuthController::class, 'googleLogin']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

// Search
Route::get('/courses/search', [SearchController::class, 'search']);

// Public course browsing
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{course}', [CourseController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category:slug}', [CategoryController::class, 'show']);

// Public instructor browsing
Route::get('/instructors', [InstructorController::class, 'index']);
Route::get('/instructors/{instructor}', [InstructorController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User management
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/change-password', [AuthController::class, 'changePassword']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Student routes
    Route::get('/student/profile', [StudentController::class, 'profile']);
    Route::post('/student/has-purchased', [StudentController::class, 'hasPurchasedCourse']);

    // Instructor routes
    Route::get('/instructor/profile', [InstructorController::class, 'profile']);
    Route::post('/instructor', [InstructorController::class, 'store']);
    Route::put('/instructor', [InstructorController::class, 'update']);

    // Admin only routes (protected by role middleware)
    Route::middleware('permission:admin.access')->prefix('admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('students', StudentController::class);
        Route::apiResource('instructors', InstructorController::class);
        Route::apiResource('roles', RoleController::class);

        // Role permission management
        Route::get('roles/{id}/permissions', [RoleController::class, 'getPermissions']);
        Route::post('roles/{id}/permissions', [RoleController::class, 'assignPermissions']);
        Route::put('roles/{id}/permissions', [RoleController::class, 'syncPermissions']);
        Route::delete('roles/{id}/permissions/{permissionId}', [RoleController::class, 'removePermission']);
        Route::get('permissions', [RoleController::class, 'getAllPermissions']);

        // User role assignment
        Route::put('users/{id}/role', [UserController::class, 'assignRole']);

        // Course management
        Route::apiResource('courses', CourseController::class);
        Route::apiResource('categories', CategoryController::class);

        // Course meta
        Route::apiResource('course-images', CourseImageController::class);
        Route::apiResource('course-objectives', CourseObjectiveController::class);
        Route::apiResource('course-requirements', CourseRequirementController::class);

        // Course content
        Route::apiResource('chapters', ChapterController::class);
        Route::apiResource('lessons', LessonController::class);
        Route::apiResource('videos', VideoController::class);
        Route::apiResource('resources', ResourceController::class);

        // Payments management
        Route::put('/payments/{payment}/status', [PaymentController::class, 'updateStatus']);
        Route::put('/orders/{order}/payment', [OrderController::class, 'updatePayment']);
    });

    // Course relationships (assign instructor/category to course)
    // Read operations - accessible to all authenticated users
    Route::prefix('courses/{course}')->group(function () {
        Route::get('/instructors', [CourseInstructorController::class, 'index']);
        Route::get('/categories', [CourseCategoryController::class, 'index']);
        Route::get('/images', [CourseImageController::class, 'index']);
        Route::get('/objectives', [CourseObjectiveController::class, 'index']);
        Route::get('/requirements', [CourseRequirementController::class, 'index']);
        Route::get('/chapters', [ChapterController::class, 'index']);
        Route::get('/chapters/{chapter}/lessons', [LessonController::class, 'index']);
    });

    // Course write operations - permission-based course management only
    Route::middleware('permission:courses.manage.any,courses.manage.own,admin.access')->group(function () {
        Route::prefix('courses/{course}')->group(function () {
            Route::post('/instructors', [CourseInstructorController::class, 'store']);
            Route::delete('/instructors/{instructor}', [CourseInstructorController::class, 'destroy']);
            Route::post('/categories', [CourseCategoryController::class, 'store']);
            Route::delete('/categories/{category}', [CourseCategoryController::class, 'destroy']);
            Route::post('/images', [CourseImageController::class, 'store']);
            Route::put('/images/{image}', [CourseImageController::class, 'update']);
            Route::delete('/images/{image}', [CourseImageController::class, 'destroy']);
            Route::post('/objectives', [CourseObjectiveController::class, 'store']);
            Route::put('/objectives/{objective}', [CourseObjectiveController::class, 'update']);
            Route::delete('/objectives/{objective}', [CourseObjectiveController::class, 'destroy']);
            Route::post('/requirements', [CourseRequirementController::class, 'store']);
            Route::put('/requirements/{requirement}', [CourseRequirementController::class, 'update']);
            Route::delete('/requirements/{requirement}', [CourseRequirementController::class, 'destroy']);
            Route::post('/chapters', [ChapterController::class, 'store']);
            Route::put('/chapters/{chapter}', [ChapterController::class, 'update']);
            Route::delete('/chapters/{chapter}', [ChapterController::class, 'destroy']);
            Route::post('/chapters/{chapter}/lessons', [LessonController::class, 'store']);
        });

        // Lesson write operations
        Route::prefix('lessons/{lesson}')->group(function () {
            Route::put('/', [LessonController::class, 'update']);
            Route::delete('/', [LessonController::class, 'destroy']);
            Route::post('/videos', [VideoController::class, 'store']);
            Route::put('/videos/{video}', [VideoController::class, 'update']);
            Route::delete('/videos/{video}', [VideoController::class, 'destroy']);
            Route::post('/resources', [ResourceController::class, 'store']);
            Route::put('/resources/{resource}', [ResourceController::class, 'update']);
            Route::delete('/resources/{resource}', [ResourceController::class, 'destroy']);
        });
    });

    // Lesson read operations - accessible to all authenticated users
    Route::prefix('lessons/{lesson}')->group(function () {
        Route::get('/', [LessonController::class, 'show']);
        Route::get('/videos', [VideoController::class, 'index']);
        Route::get('/resources', [ResourceController::class, 'index']);
    });

    // Cart
    Route::get('/cart', [CartController::class, 'getUserCart']);
    Route::post('/cart/items', [CartItemController::class, 'store']);
    Route::delete('/cart/items/{cartItem}', [CartItemController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clearCart']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/payment', [PaymentController::class, 'complete']);

    // Reviews
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    // Instructor routes (instructor and admin only)
    Route::middleware('permission:instructor.access,admin.access')->prefix('instructor')->group(function () {
        // Instructor dashboard stats
        Route::get('/stats', [InstructorCourseController::class, 'stats']);

        // Instructor courses
        Route::get('/courses', [InstructorCourseController::class, 'index']);
        Route::post('/courses', [InstructorCourseController::class, 'store']);
        Route::get('/courses/{course}', [InstructorCourseController::class, 'show']);
        Route::put('/courses/{course}', [InstructorCourseController::class, 'update']);
        Route::delete('/courses/{course}', [InstructorCourseController::class, 'destroy']);

        // Course images
        Route::post('/courses/{course}/images', [InstructorCourseController::class, 'addImage']);
        Route::delete('/courses/{course}/images/{image}', [InstructorCourseController::class, 'deleteImage']);
    });
});

