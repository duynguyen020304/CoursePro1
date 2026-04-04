<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# Seeders

## Purpose
Database seeders populate the database with sample/test data. The master `DatabaseSeeder` orchestrates all other seeders in the correct order to respect foreign key constraints.

## Key Files
| File | Purpose |
|------|---------|
| `DatabaseSeeder.php` | Master seeder that calls all other seeders |
| `CategorySeeder.php` | 20 categories (8 main + 12 subcategories) |
| `InstructorSeeder.php` | 5 instructors with Vietnamese names |
| `StudentSeeder.php` | 10 test students |
| `CourseSeeder.php` | 5 full courses with chapters, lessons |
| `OrderSeeder.php` | Completed orders, payments, reviews, cart items |

## Seeder Execution Order

```php
// DatabaseSeeder.php
public function run(): void
{
    // 1. Create roles
    Role::create(['role_id' => 'admin', 'role_name' => 'Admin']);
    Role::create(['role_id' => 'student', 'role_name' => 'Student']);
    Role::create(['role_id' => 'instructor', 'role_name' => 'Instructor']);

    // 2. Create admin user
    User::create([...]);

    // 3. Create test student
    User::create([...]);

    // 4. Run seeders in order
    $this->call([
        CategorySeeder::class,    // Categories first (no dependencies)
        InstructorSeeder::class,  // Instructors (depends on roles/users)
        StudentSeeder::class,     // Students (depends on roles/users)
        CourseSeeder::class,      // Courses (depends on categories, instructors)
        OrderSeeder::class,       // Orders last (depends on courses, students)
    ]);
}
```

## Sample Data Summary

| Seeder | Data Created |
|--------|--------------|
| `DatabaseSeeder` | Orchestrates all seeders |
| `CategorySeeder` | 20 categories (8 main + 12 subcategories) |
| `RoleSeeder` | admin, student, instructor roles |
| `PermissionSeeder` | RBAC permissions |
| `InstructorSeeder` | 5 instructors with Vietnamese names |
| `StudentSeeder` | 10 students |
| `CourseSeeder` | 5 full courses with chapters, lessons |
| `OrderSeeder` | 24 completed orders, 3 carts with items |

## For AI Agents

### Working In This Directory
- **Order Matters**: Seeders run in dependency order
- **Check Existing**: Use checks to prevent duplicate seeding
- **Factories**: Consider using model factories for large datasets
- **Foreign Keys**: Ensure referenced records exist before creating

### Common Patterns
```php
// Seeder with check
public function run(): void
{
    if (Category::count() > 0) {
        $this->command->info('Categories already seeded. Skipping...');
        return;
    }

    Category::create([
        'id' => Str::uuid(),
        'name' => 'Technology',
        'sort_order' => 1,
    ]);
}

// Seeder with progress
public function run(): void
{
    $this->command->info('Seeding categories...');

    $categories = [
        ['name' => 'Technology', 'sort_order' => 1],
        ['name' => 'Business', 'sort_order' => 2],
        // ...
    ];

    foreach ($categories as $category) {
        Category::create([
            'id' => Str::uuid(),
            ...$category,
        ]);
    }

    $this->command->info('Categories seeded successfully.');
}
```

## Dependencies

### Internal
- `backend/app/Models/` - Models being seeded
- `backend/database/migrations/` - Schema must be migrated first

<!-- MANUAL: Custom seeders notes can be added below -->
