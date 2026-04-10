<?php

namespace App\Services;

use App\Contracts\ISeedDataService;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseChapter;
use App\Models\CourseImage;
use App\Models\CourseLesson;
use App\Models\CourseObjective;
use App\Models\CourseRequirement;
use App\Models\CourseVideo;
use App\Models\CourseResource;
use App\Models\Instructor;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Review;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Models\UserAccount;
use Database\Seeders\CourseSeeder;
use Database\Seeders\OrderSeeder;
use App\Models\Cart;
use App\Models\CartItem;
use App\Support\SeedData\DefaultCategories;
use App\Support\SeedData\DefaultInstructors;
use App\Support\SeedData\DefaultPermissions;
use App\Support\SeedData\DefaultRoles;
use App\Support\SeedData\DefaultStudents;
use App\Support\SeedData\DefaultUsers;
use App\Support\RbacPermissionMap;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Idempotent Database Seeder Service
 *
 * Implements safe, repeatable seeding with:
 * - Natural key lookups (role_code, slug, email, name)
 * - Duplicate cleanup with warning
 * - Update existing, insert missing, skip ID conflicts
 * - Junction table resolution via natural keys
 * - Hierarchical two-pass approach for parent references
 */
class SeedDataService implements ISeedDataService
{
    /**
     * Output interface for command line feedback
     */
    private ?object $command = null;

    public function setCommand(?object $command): void
    {
        $this->command = $command;
    }

    private function info(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }

    private function warn(string $message): void
    {
        if ($this->command) {
            $this->command->warn($message);
        }
    }

    private function error(string $message): void
    {
        if ($this->command) {
            $this->command->error($message);
        }
    }

    /**
     * Master method chaining all seed operations in dependency order
     */
    public function seedAll(): void
    {
        $this->info('Starting idempotent database seeding...');
        $this->info('==========================================');

        // Seed in dependency order
        $this->seedRoles();
        $this->seedUsers();
        $this->seedPermissions();
        $this->seedCategories();
        $this->seedInstructors();
        $this->seedStudents();
        $this->seedCourses();
        $this->seedOrders();

        $this->info('==========================================');
        $this->info('Database seeding completed successfully!');
    }

    /**
     * Seed roles with natural key lookup by role_id
     */
    public function seedRoles(): void
    {
        $this->info('Seeding roles...');

        $roles = DefaultRoles::getData();
        $existingRoles = Role::pluck('role_name', 'role_code')->toArray();

        foreach ($roles as $roleData) {
            $roleCode = $roleData['role_code'];

            if (isset($existingRoles[$roleCode])) {
                if ($existingRoles[$roleCode] !== $roleData['role_name']) {
                    $this->warn("Role code '{$roleCode}' exists with different name '{$existingRoles[$roleCode]}'. Skipping.");
                }
                continue;
            }

            Role::create([
                'role_id' => $roleData['role_id'],
                'role_code' => $roleCode,
                'role_name' => $roleData['role_name'],
                'is_active' => true,
            ]);

            $this->info("  Created role: {$roleCode}");
        }

        $this->info('Roles seeded: ' . count($roles) . ' total.');
    }

    /**
     * Seed permissions with natural key lookup by name
     */
    public function seedPermissions(): void
    {
        $this->info('Seeding permissions...');

        $permissions = DefaultPermissions::getData();
        $existingPermissions = Permission::pluck('display_name', 'name')->toArray();

        foreach ($permissions as $perm) {
            $name = $perm['name'];

            // Update if exists with different display_name
            if (isset($existingPermissions[$name])) {
                if ($existingPermissions[$name] !== $perm['display_name']) {
                    Permission::where('name', $name)->update([
                        'display_name' => $perm['display_name'],
                        'description' => $perm['description'],
                    ]);
                    $this->info("  Updated permission: {$name}");
                }
                continue;
            }

            // Insert new permission
            Permission::create([
                'permission_id' => (string) Str::uuid(),
                'name' => $name,
                'display_name' => $perm['display_name'],
                'description' => $perm['description'],
                'is_active' => true,
            ]);

            $this->info("  Created permission: {$name}");
        }

        // Assign default permissions to roles
        $this->assignRolePermissions();

        $this->info('Permissions seeded: ' . count($permissions) . ' total.');
    }

    /**
     * Assign default permissions to roles based on RbacPermissionMap
     */
    private function assignRolePermissions(): void
    {
        $this->info('Assigning default permissions to roles...');

        foreach (RbacPermissionMap::defaultRolePermissions() as $roleCode => $permissionNames) {
            $role = Role::where('role_code', $roleCode)->first();

            if (!$role) {
                $this->warn("Role '{$roleCode}' not found. Skipping permission assignment.");
                continue;
            }

            $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('permission_id')->toArray();
            $role->permissions()->syncWithoutDetaching($permissionIds);

            $this->info("  Assigned " . count($permissionIds) . " permissions to role: {$roleCode}");
        }
    }

    /**
     * Seed categories with hierarchical two-pass approach
     * Pass 1: Create all categories
     * Pass 2: Resolve parent references
     */
    public function seedCategories(): void
    {
        $this->info('Seeding categories (two-pass for hierarchy)...');

        $categories = DefaultCategories::getData();
        $existingCategories = Category::pluck('name', 'slug')->toArray();
        $slugToIdMap = Category::pluck('id', 'slug')->toArray();

        // Pass 1: Create/update all categories without parents
        foreach ($categories as $categoryData) {
            $slug = $categoryData['slug'];

            // Check if exists
            if (isset($existingCategories[$slug])) {
                // Update name if changed
                $category = Category::where('slug', $slug)->first();
                if ($category->name !== $categoryData['name']) {
                    $category->update(['name' => $categoryData['name']]);
                    $this->info("  Updated category: {$slug}");
                }
                continue;
            }

            // Insert new category (parent_id will be set in pass 2)
            Category::create([
                'id' => (string) Str::uuid(),
                'name' => $categoryData['name'],
                'slug' => $slug,
                'parent_id' => null,  // Will be set in pass 2
                'sort_order' => $categoryData['sort_order'],
                'is_active' => true,
            ]);

            $this->info("  Created category: {$slug}");
        }

        // Refresh the map after inserts
        $slugToIdMap = Category::pluck('id', 'slug')->toArray();

        // Pass 2: Resolve parent references
        foreach ($categories as $categoryData) {
            if ($categoryData['parent_slug'] === null) {
                continue;  // Skip root categories
            }

            $slug = $categoryData['slug'];
            $parentSlug = $categoryData['parent_slug'];

            // Check if parent exists
            if (!isset($slugToIdMap[$parentSlug])) {
                $this->warn("Parent category '{$parentSlug}' not found for '{$slug}'. Skipping parent assignment.");
                continue;
            }

            $parentId = $slugToIdMap[$parentSlug];
            $categoryId = $slugToIdMap[$slug];

            // Update parent_id
            Category::where('id', $categoryId)->update(['parent_id' => $parentId]);
        }

        $this->info('Categories seeded: ' . count($categories) . ' total.');
    }

    /**
     * Seed users with natural key lookup by email
     * Creates both User and UserAccount records
     */
    public function seedUsers(): void
    {
        $this->info('Seeding users...');

        $users = DefaultUsers::getData();
        $existingAccounts = UserAccount::with('user')
            ->where('provider', 'email')
            ->get()
            ->keyBy('email');

        foreach ($users as $userData) {
            $email = $userData['email'];

            // Check if account exists
            if ($existingAccounts->has($email)) {
                $account = $existingAccounts->get($email);
                $user = $account->user;

                // Update user if needed
                if ($user && ($user->first_name !== $userData['first_name'] || $user->last_name !== $userData['last_name'])) {
                    $user->update([
                        'first_name' => $userData['first_name'],
                        'last_name' => $userData['last_name'],
                        'role_id' => Role::where('role_code', $userData['role_code'])->value('role_id'),
                    ]);
                    $this->info("  Updated user: {$email}");
                }
                continue;
            }

            // Insert new user and account
            $userId = Str::uuid();

            User::create([
                'user_id' => $userId,
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'role_id' => $userData['role_id'],
                'is_active' => true,
            ]);

            UserAccount::create([
                'user_id' => $userId,
                'email' => $email,
                'password' => Hash::make($userData['password']),
                'provider' => 'email',
                'is_verified' => true,
                'is_active' => true,
            ]);

            $this->info("  Created user: {$email}");
        }

        $this->info('Users seeded: ' . count($users) . ' total.');
    }

    /**
     * Seed instructor profiles with natural key lookup by email
     */
    public function seedInstructors(): void
    {
        $this->info('Seeding instructors...');

        $instructors = DefaultInstructors::getData();
        $existingInstructors = Instructor::with('user.userAccount')
            ->get()
            ->keyBy(fn ($i) => $i->user->userAccount->email ?? null);

        foreach ($instructors as $instructorData) {
            $email = $instructorData['email'];

            // Check if instructor exists via email
            if ($existingInstructors->has($email)) {
                $instructor = $existingInstructors->get($email);

                // Update biography if changed
                if ($instructor->biography !== $instructorData['biography']) {
                    $instructor->update(['biography' => $instructorData['biography']]);
                    $this->info("  Updated instructor profile: {$email}");
                }
                continue;
            }

            // Check if user account exists, otherwise create the backing user/account pair here.
            $account = UserAccount::where('email', $email)->where('provider', 'email')->first();
            if (!$account) {
                $userId = Str::uuid();

                User::create([
                    'user_id' => $userId,
                    'first_name' => $instructorData['first_name'],
                    'last_name' => $instructorData['last_name'],
                    'role_id' => Role::where('role_code', 'instructor')->value('role_id'),
                    'is_active' => true,
                ]);

                $account = UserAccount::create([
                    'user_id' => $userId,
                    'email' => $email,
                    'password' => Hash::make($instructorData['password']),
                    'provider' => 'email',
                    'is_verified' => true,
                    'is_active' => true,
                ]);

                $this->info("  Created user account for instructor: {$email}");
            } else {
                $user = $account->user;

                if ($user && (
                    $user->first_name !== $instructorData['first_name'] ||
                    $user->last_name !== $instructorData['last_name'] ||
                    $user->role?->role_code !== 'instructor'
                )) {
                    $user->update([
                        'first_name' => $instructorData['first_name'],
                        'last_name' => $instructorData['last_name'],
                        'role_id' => Role::where('role_code', 'instructor')->value('role_id'),
                    ]);
                }
            }

            // Insert new instructor profile
            Instructor::create([
                'instructor_id' => Str::uuid(),
                'user_id' => $account->user_id,
                'biography' => $instructorData['biography'],
                'is_active' => true,
            ]);

            // Update user role to instructor if needed
            $user = $account->user;
            if ($user->role?->role_code !== 'instructor') {
                $user->update(['role_id' => Role::where('role_code', 'instructor')->value('role_id')]);
            }

            $this->info("  Created instructor: {$email}");
        }

        $this->info('Instructors seeded: ' . count($instructors) . ' total.');
    }

    /**
     * Seed student profiles with natural key lookup by email
     */
    public function seedStudents(): void
    {
        $this->info('Seeding students...');

        $students = DefaultStudents::getData();
        $existingStudents = Student::with('user.userAccount')
            ->get()
            ->keyBy(fn ($s) => $s->user->userAccount->email ?? null);

        foreach ($students as $studentData) {
            $email = $studentData['email'];

            // Check if student exists via email
            if ($existingStudents->has($email)) {
                continue;  // No updates needed for students
            }

            // Check if user account exists
            $account = UserAccount::where('email', $email)->where('provider', 'email')->first();
            if (!$account) {
                // Create new user account
                $userId = Str::uuid();

                User::create([
                    'user_id' => $userId,
                    'first_name' => $studentData['first_name'],
                    'last_name' => $studentData['last_name'],
                    'role_id' => Role::where('role_code', 'student')->value('role_id'),
                    'is_active' => true,
                ]);

                UserAccount::create([
                    'user_id' => $userId,
                    'email' => $email,
                    'password' => Hash::make($studentData['password']),
                    'provider' => 'email',
                    'is_verified' => true,
                    'is_active' => true,
                ]);

                Student::create([
                    'student_id' => Str::uuid(),
                    'user_id' => $userId,
                    'is_active' => true,
                ]);

                $this->info("  Created student: {$email}");
                continue;
            }

            // Create student profile for existing account
            Student::create([
                'student_id' => Str::uuid(),
                'user_id' => $account->user_id,
                'is_active' => true,
            ]);

            // Update user role to student if needed
            $user = $account->user;
            if ($user->role?->role_code !== 'student') {
                $user->update(['role_id' => Role::where('role_code', 'student')->value('role_id')]);
            }

            $this->info("  Created student profile: {$email}");
        }

        $this->info('Students seeded: ' . count($students) . ' total.');
    }

    /**
     * Seed courses with junction tables for instructors and categories
     * Includes chapters, lessons, videos, resources, objectives, requirements, images
     */
    public function seedCourses(): void
    {
        $this->info('Seeding courses (this includes all related data)...');

        // Skip if courses already exist
        if (Course::count() > 0) {
            $this->info('Courses already seeded. Skipping...');
            return;
        }

        // Get first instructor
        $instructor = Instructor::first();
        if (!$instructor) {
            $this->error('No instructor found. Run seedInstructors() first.');
            return;
        }

        // Use existing CourseSeeder's data (this is a large dataset)
        $this->info('Using existing CourseSeeder data for full courses...');
        $this->callExistingCourseSeeder($instructor);

        $this->info('Courses seeded with all related data.');
    }

    /**
     * Call the existing CourseSeeder for detailed course data
     */
    private function callExistingCourseSeeder(Instructor $instructor): void
    {
        $this->info('  Delegating to existing CourseSeeder for comprehensive course data...');

        $seeder = app(CourseSeeder::class);
        $seeder->setContainer(app());
        $seeder->setCommand($this->command);
        $seeder->run();
    }

    /**
     * Seed orders, cart items, and reviews for testing
     */
    public function seedOrders(): void
    {
        // Skip if orders already exist
        if (Order::count() > 0) {
            $this->info('Seeding orders, cart items, and reviews...');
            $this->info('Orders already seeded. Skipping...');
            return;
        }

        $students = Student::with('user')->get();
        $courses = Course::all();

        if ($students->isEmpty() || $courses->isEmpty()) {
            $this->info('Seeding orders, cart items, and reviews...');
            $this->warn('No students or courses found. Skipping order seeding.');
            return;
        }

        if ($this->command) {
            $seeder = app(OrderSeeder::class);
            $seeder->setContainer(app());
            $seeder->setCommand($this->command);
            $seeder->run();
            return;
        }

        $this->info('Seeding orders, cart items, and reviews...');

        $completedOrdersCount = 0;

        foreach ($students as $student) {
            // Create 1-3 completed orders per student
            $orderCount = rand(1, 3);

            for ($i = 0; $i < $orderCount; $i++) {
                $course = $courses->random();
                $totalAmount = $course->price ?? 0;
                $orderDate = Carbon::now()->subDays(rand(1, 60));

                // Create order
                $order = Order::create([
                    'order_id' => Str::uuid(),
                    'user_id' => $student->user_id,
                    'course_id' => $course->course_id,
                    'order_date' => $orderDate,
                    'total_amount' => $totalAmount,
                    'status' => 'completed',
                    'created_at' => $orderDate,
                    'is_active' => true,
                ]);

                $completedOrdersCount++;

                // Create order detail
                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'course_id' => $course->course_id,
                    'price' => $totalAmount,
                ]);

                // Create payment
                Payment::firstOrCreate(
                    ['order_id' => $order->order_id],
                    [
                        'payment_id' => Str::uuid(),
                        'amount' => $totalAmount,
                        'payment_method' => 'credit_card',
                        'payment_status' => 'completed',
                        'transaction_id' => 'TXN-' . strtoupper(Str::random(10)),
                    ]
                );

                // Create review
                Review::firstOrCreate(
                    ['user_id' => $student->user_id, 'course_id' => $course->course_id],
                    [
                        'review_id' => Str::uuid(),
                        'rating' => rand(4, 5),
                        'review_text' => $this->getRandomReviewText(),
                        'created_at' => Carbon::now()->subDays(rand(1, 30)),
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->info("Created {$completedOrdersCount} completed orders.");

        // Seed cart items for checkout testing
        $this->seedCartItems($students, $courses);
    }

    /**
     * Seed cart items for checkout testing
     */
    private function seedCartItems($students, $courses): void
    {
        $cartsCreated = 0;

        foreach ($students->take(3) as $student) {
            // Create cart
            $cart = Cart::firstOrCreate(
                ['user_id' => $student->user_id],
                ['cart_id' => Str::uuid(), 'is_active' => true]
            );

            $cartsCreated++;

            // Add 1-2 courses to cart
            $cartCourses = $courses->random(rand(1, 2));

            foreach ($cartCourses as $course) {
                CartItem::firstOrCreate(
                    [
                        'cart_id' => $cart->cart_id,
                        'course_id' => $course->course_id,
                    ],
                    ['cart_item_id' => Str::uuid(), 'quantity' => 1, 'is_active' => true]
                );
            }
        }

        $this->info("Created {$cartsCreated} carts with items for checkout testing.");
    }

    /**
     * Get random review text for seeding
     */
    private function getRandomReviewText(): string
    {
        $reviews = [
            'Excellent course! Very informative and well structured.',
            'Great content and helpful instructor. Highly recommended!',
            'Good course overall. Learned a lot from this.',
            'Amazing value for money. The lessons are clear and concise.',
            'Perfect for beginners. The explanations are easy to understand.',
        ];

        return $reviews[array_rand($reviews)];
    }
}
