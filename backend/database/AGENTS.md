<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# Database

## Purpose
Database layer containing migrations, seeders, and factories for the MySQL database. Defines the complete schema for the e-learning platform including users, courses, content, cart, orders, and payments.

## Key Files
| File | Description |
|------|-------------|
| `migrations/` | 43 migration files defining schema |
| `seeders/DatabaseSeeder.php` | Master seeder orchestrating all seeders |

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `migrations/` | Database migrations (schema definitions) |
| `seeders/` | Sample data seeders (see `seeders/AGENTS.md`) |
| `factories/` | Model factories for testing |

## For AI Agents

### Working In This Directory
- **Database**: MySQL 8.0
- **Migrations**: Run with `php artisan migrate`
- **Seeders**: Run with `php artisan db:seed`
- **Fresh Start**: `php artisan migrate:fresh --seed` drops and reseeds

### Migration Conventions
- Timestamp format: `YYYY_MM_DD_HHMMSS_create_table_name.php`
- Use `Schema::create()` for new tables
- Use `Schema::table()` for modifications
- Foreign keys use `foreignId()->constrained()`
- Primary keys: UUID strings (`$table->uuid('id')->primary()`)

### Seeder Conventions
- Each model has corresponding seeder class
- Call from `DatabaseSeeder`: `$this->call([SeederClass::class])`
- Use factory patterns for generating test data
- Seed order matters (foreign key dependencies)

## Database Schema

### User & Auth Tables
| Table | Columns |
|-------|---------|
| `roles` | role_id, role_name |
| `permissions` | permission_id, name |
| `permission_role` | role_id, permission_id (pivot) |
| `users` | user_id (UUID), role_id, first_name, last_name, email |
| `user_accounts` | account_id (UUID), user_id, provider, provider_id, email, password |
| `password_reset_tokens` | email, token |
| `sessions` | id, user_id, ip_address, payload, last_activity |
| `refresh_tokens` | id, user_id, token (hashed), expires_at |
| `instructors` | instructor_id, user_id, biography |
| `students` | student_id, user_id |

### Course Content Tables
| Table | Columns |
|-------|---------|
| `courses` | course_id, title, description, price, difficulty, language, created_by |
| `categories` | id, name, parent_id, sort_order |
| `course_chapters` | chapter_id, course_id, title, description, sort_order |
| `course_lessons` | lesson_id, course_id, chapter_id, title, content, sort_order |
| `course_videos` | video_id, lesson_id, url, title, duration, sort_order |
| `course_resources` | resource_id, lesson_id, resource_path, title, sort_order |
| `course_images` | image_id, course_id, image_path, caption, sort_order |
| `course_objectives` | requirement_id, course_id, objective |
| `course_requirements` | requirement_id, course_id, requirement |

### Relationship Tables
| Table | Columns |
|-------|---------|
| `course_instructor` | course_id, instructor_id (composite PK) |
| `course_category` | course_id, category_id (composite PK) |

### E-commerce Tables
| Table | Columns |
|-------|---------|
| `carts` | cart_id, user_id |
| `cart_items` | cart_item_id, cart_id, course_id, quantity |
| `orders` | order_id, user_id, course_id, total_amount, status |
| `order_details` | order_id, course_id, price (composite PK) |
| `payments` | payment_id, order_id, amount, payment_method, payment_status, transaction_id |
| `reviews` | review_id, user_id, course_id, rating, review_text |

### Laravel System Tables
| Table | Purpose |
|-------|---------|
| `cache`, `cache_locks` | Cache storage |
| `jobs`, `job_batches`, `failed_jobs` | Queue system |
| `personal_access_tokens` | Sanctum API tokens |

## Dependencies

### Internal
- `backend/app/Models/` - Models correspond to tables
- `backend/database/seeders/` - Seeders populate tables

<!-- MANUAL: Custom database notes can be added below -->
