<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            ['name' => 'Technology', 'slug' => 'technology', 'parent_id' => null, 'sort_order' => 1],
            ['name' => 'Business', 'slug' => 'business', 'parent_id' => null, 'sort_order' => 2],
            ['name' => 'Design', 'slug' => 'design', 'parent_id' => null, 'sort_order' => 3],
            ['name' => 'Programming', 'slug' => 'programming', 'parent_id' => null, 'sort_order' => 4],
            ['name' => 'Data Science', 'slug' => 'data-science', 'parent_id' => null, 'sort_order' => 5],
            ['name' => 'Web Development', 'slug' => 'web-development', 'parent_id' => null, 'sort_order' => 6],
            ['name' => 'Mobile Development', 'slug' => 'mobile-development', 'parent_id' => null, 'sort_order' => 7],
            ['name' => 'Digital Marketing', 'slug' => 'digital-marketing', 'parent_id' => null, 'sort_order' => 8],

            // Sub-categories under Technology
            ['name' => 'Python', 'slug' => 'python', 'parent_id' => 1, 'sort_order' => 9],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'parent_id' => 1, 'sort_order' => 10],
            ['name' => 'Java', 'slug' => 'java', 'parent_id' => 1, 'sort_order' => 11],
            ['name' => 'C#', 'slug' => 'c-sharp', 'parent_id' => 1, 'sort_order' => 12],

            // Sub-categories under Programming
            ['name' => 'ReactJS', 'slug' => 'reactjs', 'parent_id' => 4, 'sort_order' => 13],
            ['name' => 'Node.js', 'slug' => 'nodejs', 'parent_id' => 4, 'sort_order' => 14],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'parent_id' => 4, 'sort_order' => 15],

            // Sub-categories under Web Development
            ['name' => 'iOS Development', 'slug' => 'ios-development', 'parent_id' => 7, 'sort_order' => 16],
            ['name' => 'Android Development', 'slug' => 'android-development', 'parent_id' => 7, 'sort_order' => 17],

            // Sub-categories under Data Science
            ['name' => 'Machine Learning', 'slug' => 'machine-learning', 'parent_id' => 5, 'sort_order' => 18],
            ['name' => 'Deep Learning', 'slug' => 'deep-learning', 'parent_id' => 5, 'sort_order' => 19],
            ['name' => 'NLP', 'slug' => 'nlp', 'parent_id' => 5, 'sort_order' => 20],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
