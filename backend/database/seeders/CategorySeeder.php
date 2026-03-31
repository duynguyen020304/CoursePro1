<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Skip if categories already exist
        if (Category::count() > 0) {
            $this->command->info('Categories already seeded. Skipping...');
            return;
        }

        $categories = [
            ['name' => 'Technology', 'parent_id' => null, 'sort_order' => 1],
            ['name' => 'Business', 'parent_id' => null, 'sort_order' => 2],
            ['name' => 'Design', 'parent_id' => null, 'sort_order' => 3],
            ['name' => 'Programming', 'parent_id' => null, 'sort_order' => 4],
            ['name' => 'Data Science', 'parent_id' => null, 'sort_order' => 5],
            ['name' => 'Web Development', 'parent_id' => null, 'sort_order' => 6],
            ['name' => 'Mobile Development', 'parent_id' => null, 'sort_order' => 7],
            ['name' => 'Digital Marketing', 'parent_id' => null, 'sort_order' => 8],

            // Sub-categories under Technology
            ['name' => 'Python', 'parent_id' => 1, 'sort_order' => 9],
            ['name' => 'JavaScript', 'parent_id' => 1, 'sort_order' => 10],
            ['name' => 'Java', 'parent_id' => 1, 'sort_order' => 11],
            ['name' => 'C#', 'parent_id' => 1, 'sort_order' => 12],

            // Sub-categories under Programming
            ['name' => 'ReactJS', 'parent_id' => 4, 'sort_order' => 13],
            ['name' => 'Node.js', 'parent_id' => 4, 'sort_order' => 14],
            ['name' => 'Vue.js', 'parent_id' => 4, 'sort_order' => 15],

            // Sub-categories under Web Development
            ['name' => 'iOS Development', 'parent_id' => 7, 'sort_order' => 16],
            ['name' => 'Android Development', 'parent_id' => 7, 'sort_order' => 17],

            // Sub-categories under Data Science
            ['name' => 'Machine Learning', 'parent_id' => 5, 'sort_order' => 18],
            ['name' => 'Deep Learning', 'parent_id' => 5, 'sort_order' => 19],
            ['name' => 'NLP', 'parent_id' => 5, 'sort_order' => 20],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
