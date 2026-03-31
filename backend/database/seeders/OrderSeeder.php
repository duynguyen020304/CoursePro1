<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Review;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing students and courses
        $students = Student::with('user')->get();
        $courses = \App\Models\Course::all();

        if ($students->isEmpty() || $courses->isEmpty()) {
            $this->command->warn('No students or courses found. Skipping order seeding.');
            return;
        }

        $this->command->info('Seeding orders, cart items, and reviews...');

        // Create completed orders for certificates testing
        $completedOrdersCount = 0;
        foreach ($students as $student) {
            // Create 1-3 completed orders per student
            $orderCount = rand(1, 3);

            for ($i = 0; $i < $orderCount; $i++) {
                $course = $courses->random();
                $totalAmount = $course->price ?? 0;
                $orderDate = Carbon::now()->subDays(rand(1, 60));

                // Create order with status 'completed' for certificates (using actual schema)
                $order = Order::create([
                    'order_id' => \Str::uuid(),
                    'user_id' => $student->user_id,
                    'course_id' => $course->course_id,
                    'order_date' => $orderDate,
                    'total_amount' => $totalAmount,
                    'status' => 'completed', // Set status to completed for certificate testing
                    'created_at' => $orderDate,
                ]);

                $completedOrdersCount++;

                // Create order detail
                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'course_id' => $course->course_id,
                    'price' => $totalAmount,
                ]);

                // Update order with course_id for foreign key reference
                $order->update(['course_id' => $course->course_id]);

                // Create payment (using actual schema: order_id, no student_id)
                Payment::firstOrCreate(
                    ['order_id' => $order->order_id],
                    [
                        'payment_id' => \Str::uuid(),
                        'amount' => $totalAmount,
                        'payment_method' => 'credit_card',
                        'payment_status' => 'completed',
                        'transaction_id' => 'TXN-' . strtoupper(\Str::random(10)),
                    ]
                );

                // Create review (using user_id, not student_id)
                Review::firstOrCreate(
                    ['user_id' => $student->user_id, 'course_id' => $course->course_id],
                    [
                        'review_id' => \Str::uuid(),
                        'rating' => rand(4, 5),
                        'review_text' => $this->getRandomReviewText(),
                        'created_at' => Carbon::now()->subDays(rand(1, 30)),
                    ]
                );
            }
        }

        $this->command->info("Created {$completedOrdersCount} completed orders.");

        // Seed cart items for checkout testing
        $this->seedCartItems($students, $courses);
    }

    private function seedCartItems($students, $courses)
    {
        $cartsCreated = 0;

        foreach ($students->take(3) as $student) {
            // Create cart (using user_id, generate cart_id)
            $cart = Cart::firstOrCreate(
                ['user_id' => $student->user_id],
                ['cart_id' => \Str::uuid()]
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
                    ['cart_item_id' => \Str::uuid(), 'quantity' => 1]
                );
            }
        }

        $this->command->info("Created {$cartsCreated} carts with items for checkout testing.");
    }

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
