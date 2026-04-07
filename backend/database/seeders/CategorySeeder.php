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
            ['name' => 'Technology', 'slug' => 'technology', 'parent_slug' => null, 'sort_order' => 1],
            ['name' => 'Business', 'slug' => 'business', 'parent_slug' => null, 'sort_order' => 2],
            ['name' => 'Design', 'slug' => 'design', 'parent_slug' => null, 'sort_order' => 3],
            ['name' => 'Programming', 'slug' => 'programming', 'parent_slug' => null, 'sort_order' => 4],
            ['name' => 'Data Science', 'slug' => 'data-science', 'parent_slug' => null, 'sort_order' => 5],
            ['name' => 'Web Development', 'slug' => 'web-development', 'parent_slug' => null, 'sort_order' => 6],
            ['name' => 'Mobile Development', 'slug' => 'mobile-development', 'parent_slug' => null, 'sort_order' => 7],
            ['name' => 'Digital Marketing', 'slug' => 'digital-marketing', 'parent_slug' => null, 'sort_order' => 8],

            // Sub-categories under Technology
            ['name' => 'Python', 'slug' => 'python', 'parent_slug' => 'technology', 'sort_order' => 9],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'parent_slug' => 'technology', 'sort_order' => 10],
            ['name' => 'Java', 'slug' => 'java', 'parent_slug' => 'technology', 'sort_order' => 11],
            ['name' => 'C#', 'slug' => 'c-sharp', 'parent_slug' => 'technology', 'sort_order' => 12],

            // Sub-categories under Programming
            ['name' => 'ReactJS', 'slug' => 'reactjs', 'parent_slug' => 'programming', 'sort_order' => 13],
            ['name' => 'Node.js', 'slug' => 'nodejs', 'parent_slug' => 'programming', 'sort_order' => 14],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'parent_slug' => 'programming', 'sort_order' => 15],

            // Sub-categories under Mobile Development
            ['name' => 'iOS Development', 'slug' => 'ios-development', 'parent_slug' => 'mobile-development', 'sort_order' => 16],
            ['name' => 'Android Development', 'slug' => 'android-development', 'parent_slug' => 'mobile-development', 'sort_order' => 17],

            // Sub-categories under Data Science
            ['name' => 'Machine Learning', 'slug' => 'machine-learning', 'parent_slug' => 'data-science', 'sort_order' => 18],
            ['name' => 'Deep Learning', 'slug' => 'deep-learning', 'parent_slug' => 'data-science', 'sort_order' => 19],
            ['name' => 'NLP', 'slug' => 'nlp', 'parent_slug' => 'data-science', 'sort_order' => 20],
        ];

        foreach ($categories as $category) {
            $parentId = null;

            if (! empty($category['parent_slug'])) {
                $parentId = Category::where('slug', $category['parent_slug'])->value('id');
            }

            Category::create([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'parent_id' => $parentId,
                'sort_order' => $category['sort_order'],
            ]);
        }
    }
}
