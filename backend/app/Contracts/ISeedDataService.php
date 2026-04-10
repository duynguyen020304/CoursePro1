<?php

namespace App\Contracts;

/**
 * Interface for idempotent database seeding operations.
 * Implementations must handle duplicate data gracefully by using
 * natural keys for lookups and upsert operations.
 */
interface ISeedDataService
{
    /**
     * Master method that chains all seed operations in dependency order.
     * Safe to run multiple times - adds missing data, updates existing.
     *
     * @return void
     */
    public function seedAll(): void;

    /**
     * Seed roles with natural key lookup by role_id.
     * Default roles: admin, student, instructor
     *
     * @return void
     */
    public function seedRoles(): void;

    /**
     * Seed permissions with natural key lookup by name.
     * Includes all RBAC permissions for the application.
     *
     * @return void
     */
    public function seedPermissions(): void;

    /**
     * Seed categories with hierarchical parent-child relationships.
     * Uses two-pass approach: first pass creates categories,
     * second pass resolves parent references.
     *
     * @return void
     */
    public function seedCategories(): void;

    /**
     * Seed users (admin and test student) with natural key lookup by email.
     * Creates User and UserAccount records.
     *
     * @return void
     */
    public function seedUsers(): void;

    /**
     * Seed instructor profiles with natural key lookup by email.
     * Depends on seedUsers() being called first.
     *
     * @return void
     */
    public function seedInstructors(): void;

    /**
     * Seed student profiles with natural key lookup by email.
     * Depends on seedUsers() being called first.
     *
     * @return void
     */
    public function seedStudents(): void;

    /**
     * Seed courses with all related data (chapters, lessons, videos, etc.).
     * Uses junction tables for instructors and categories.
     * Depends on seedInstructors() and seedCategories() being called first.
     *
     * @return void
     */
    public function seedCourses(): void;

    /**
     * Seed orders, cart items, and reviews for testing.
     * Depends on seedStudents() and seedCourses() being called first.
     *
     * @return void
     */
    public function seedOrders(): void;
}
