<?php

namespace Database\Seeders;

use App\Contracts\ISeedDataService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

/**
 * Main Database Seeder
 *
 * This seeder acts as the entry point for all database seeding operations.
 * It delegates to the SeedDataService which implements idempotent upsert logic.
 *
 * The service-based architecture allows for:
 * - Easy testing through dependency injection
 * - Consistent seeding behavior across contexts
 * - Idempotent operations (safe to run multiple times)
 *
 * Usage:
 *   php artisan db:seed
 *   php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * This method resolves the SeedDataService from the container and executes
     * the master seedAll() method, which chains all seed operations in the
     * correct dependency order.
     *
     * @return void
     */
    public function run(): void
    {
        // Resolve the seed data service from the container
        $seedService = App::make(ISeedDataService::class);

        // Pass command instance for output feedback
        $seedService->setCommand($this->command);

        // Execute all seed operations in dependency order
        $seedService->seedAll();
    }
}
