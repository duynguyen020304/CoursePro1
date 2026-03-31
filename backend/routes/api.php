<?php

use Illuminate\Http\Request;
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
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Search
Route::get('/courses/search', [SearchController::class, 'search']);

// Public course browsing
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{course}', [CourseController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Public instructor browsing
Route::get('/instructors', [InstructorController::class, 'index']);
Route::get('/instructors/{instructor}', [InstructorController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User management
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/change-password', [AuthController::class, 'changePassword']);

    // Student routes
    Route::get('/student/profile', [StudentController::class, 'profile']);
    Route::post('/student/has-purchased', [StudentController::class, 'hasPurchasedCourse']);

    // Instructor routes
    Route::get('/instructor/profile', [InstructorController::class, 'profile']);
    Route::post('/instructor', [InstructorController::class, 'store']);
    Route::put('/instructor', [InstructorController::class, 'update']);

    // Admin only routes
    Route::prefix('admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('students', StudentController::class);
        Route::apiResource('instructors', InstructorController::class);
        Route::apiResource('roles', RoleController::class);

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
    Route::prefix('courses/{course}')->group(function () {
        // Instructors
        Route::get('/instructors', [CourseInstructorController::class, 'index']);
        Route::post('/instructors', [CourseInstructorController::class, 'store']);
        Route::delete('/instructors/{instructor}', [CourseInstructorController::class, 'destroy']);

        // Categories
        Route::get('/categories', [CourseCategoryController::class, 'index']);
        Route::post('/categories', [CourseCategoryController::class, 'store']);
        Route::delete('/categories/{category}', [CourseCategoryController::class, 'destroy']);

        // Images
        Route::get('/images', [CourseImageController::class, 'index']);
        Route::post('/images', [CourseImageController::class, 'store']);
        Route::put('/images/{image}', [CourseImageController::class, 'update']);
        Route::delete('/images/{image}', [CourseImageController::class, 'destroy']);

        // Objectives
        Route::get('/objectives', [CourseObjectiveController::class, 'index']);
        Route::post('/objectives', [CourseObjectiveController::class, 'store']);
        Route::put('/objectives/{objective}', [CourseObjectiveController::class, 'update']);
        Route::delete('/objectives/{objective}', [CourseObjectiveController::class, 'destroy']);

        // Requirements
        Route::get('/requirements', [CourseRequirementController::class, 'index']);
        Route::post('/requirements', [CourseRequirementController::class, 'store']);
        Route::put('/requirements/{requirement}', [CourseRequirementController::class, 'update']);
        Route::delete('/requirements/{requirement}', [CourseRequirementController::class, 'destroy']);

        // Chapters
        Route::get('/chapters', [ChapterController::class, 'index']);
        Route::post('/chapters', [ChapterController::class, 'store']);
        Route::put('/chapters/{chapter}', [ChapterController::class, 'update']);
        Route::delete('/chapters/{chapter}', [ChapterController::class, 'destroy']);

        // Lessons (nested under chapter)
        Route::get('/chapters/{chapter}/lessons', [LessonController::class, 'index']);
        Route::post('/chapters/{chapter}/lessons', [LessonController::class, 'store']);
    });

    // Lesson nested routes
    Route::prefix('lessons/{lesson}')->group(function () {
        Route::get('/', [LessonController::class, 'show']);
        Route::put('/', [LessonController::class, 'update']);
        Route::delete('/', [LessonController::class, 'destroy']);

        // Videos
        Route::get('/videos', [VideoController::class, 'index']);
        Route::post('/videos', [VideoController::class, 'store']);
        Route::put('/videos/{video}', [VideoController::class, 'update']);
        Route::delete('/videos/{video}', [VideoController::class, 'destroy']);

        // Resources
        Route::get('/resources', [ResourceController::class, 'index']);
        Route::post('/resources', [ResourceController::class, 'store']);
        Route::put('/resources/{resource}', [ResourceController::class, 'update']);
        Route::delete('/resources/{resource}', [ResourceController::class, 'destroy']);
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
});
