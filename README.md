# CoursePro1 - E-Learning Platform

A comprehensive, full-stack e-learning platform built with **Laravel (Backend)**, **React (Frontend)**, and **MySQL** database.

## Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | Laravel 11 (PHP 8.2+) |
| Frontend | React 19 + Vite |
| Database | MySQL 8.0 |
| Docker | Docker Compose |

## Database Setup

### MySQL Configuration

| Setting | Value |
|---------|-------|
| **Database Name** | `ecourse` |
| **Host** | `localhost` (local) / `mysql` (Docker) |
| **Port** | `3306` |
| **Username** | `root` |
| **Password** | `rootpassword` |
| **Character Set** | `utf8mb4` |
| **Collation** | `utf8mb4_unicode_ci` |

### Starting MySQL with Docker

```bash
# Start MySQL container
docker-compose -f docker-compose.mysql-only.yml up -d

# Check container status
docker-compose -f docker-compose.mysql-only.yml ps

# View logs
docker-compose -f docker-compose.mysql-only.yml logs -f
```

### Running Migrations

```bash
# Navigate to backend directory
cd backend

# Run migrations
php artisan migrate
```

### Seeding the Database

```bash
# Seed database with sample data
php artisan db:seed

# Or combine migrate + seed
php artisan migrate --seed
```

### Sample Data

The database seeders create:

- **Roles**: 3 (admin, student, instructor)
- **Users**:
  - Admin: `admin@example.com` / `password`
  - Test Student: `student@example.com` / `password`
- **Categories**: 20 (8 main + 12 subcategories)
- **Instructors**: 5 with Vietnamese names and biographies
- **Students**: 10 test accounts (password: `Student@123`)
- **Courses**: 5 full courses with chapters, lessons, objectives, requirements
- **Orders**: Completed orders with payments and reviews
- **Cart Items**: Sample carts for checkout testing

## Project Structure

```
CoursePro1/
├── backend/          # Laravel API backend
│   ├── app/
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   └── .env
├── frontend/         # React frontend
│   ├── src/
│   └── .env
├── docker-compose.mysql-only.yml
├── .env              # Root environment config
└── AGENTS.md         # AI agent documentation
```

## Environment Configuration

### Backend (.env)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecourse
DB_USERNAME=root
DB_PASSWORD=rootpassword
```

### Root (.env)

```env
DB_HOST=localhost
DB_NAME=ecourse
DB_USER=root
DB_PASSWORD=rootpassword
DB_PORT=3306
```

## Docker Setup

The project uses `docker-compose.mysql-only.yml` for MySQL database:

```yaml
services:
  mysql:
    image: mysql:8.0
    container_name: coursepro_mysql
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: ecourse
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
```

## Development

### Backend

```bash
cd backend
composer install
php artisan serve
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

## Database Schema

### Core Tables
- `users` - User accounts with UUID primary keys
- `roles` - User roles (admin, student, instructor)
- `instructors` - Instructor profiles
- `students` - Student profiles
- `courses` - Course catalog
- `course_chapters`, `course_lessons` - Course content
- `course_videos`, `course_resources` - Lesson media
- `categories` - Course categories (supports hierarchy)
- `carts`, `cart_items` - Shopping cart
- `orders`, `order_details` - Order management
- `payments` - Payment records
- `reviews` - Course reviews

See `backend/database/migrations/` for full schema details.

## API Documentation

API endpoints are available under `/api/` when running the Laravel backend.

## Audit Columns

All application tables include standardized audit columns:
- `is_active` (boolean, default true) — whether the record is active
- `created_at` (timestamp) — when the record was created
- `updated_at` (timestamp) — when the record was last updated
- `deleted_at` (timestamp, nullable) — soft delete timestamp (Laravel SoftDeletes)

Pivot tables (`permission_role`, `course_instructor`, `course_category`) use `is_deleted` (boolean) instead of `deleted_at`.

## License

Proprietary - CoursePro1
