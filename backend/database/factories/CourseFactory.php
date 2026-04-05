<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        // First ensure we have an instructor
        $instructor = Instructor::factory()->create();

        return [
            'course_id' => 'course_' . Str::uuid(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 0, 200),
            'difficulty' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'language' => fake()->randomElement(['English', 'Vietnamese', 'Spanish']),
            'created_by' => $instructor->instructor_id,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
