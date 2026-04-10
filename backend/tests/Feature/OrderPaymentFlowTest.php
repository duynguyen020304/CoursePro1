<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Role as UserRole;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $user->createToken('test-token')->plainTextToken];
    }

    private function createCourse(float $price): Course
    {
        $this->ensureRoles();
        $instructorUser = User::factory()->instructor()->create();
        $instructor = Instructor::create([
            'instructor_id' => (string) Str::uuid(),
            'user_id' => $instructorUser->user_id,
            'biography' => 'Test instructor biography',
            'is_active' => true,
        ]);

        return Course::create([
            'course_id' => (string) Str::uuid(),
            'title' => 'Test Course',
            'description' => 'Test Course Description',
            'price' => $price,
            'difficulty' => 'beginner',
            'language' => 'English',
            'created_by' => $instructor->instructor_id,
            'is_active' => true,
        ]);
    }

    public function test_order_creation_uses_cart_items_and_returns_created_order(): void
    {
        $this->ensureRoles();
        $user = User::factory()->create();

        $course = $this->createCourse(149.99);

        $cart = Cart::create([
            'cart_id' => (string) Str::uuid(),
            'user_id' => $user->user_id,
        ]);

        CartItem::create([
            'cart_item_id' => (string) Str::uuid(),
            'cart_id' => $cart->cart_id,
            'course_id' => $course->course_id,
            'quantity' => 1,
        ]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/orders');

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.order_id', $response->json('data.order_id'))
            ->assertJsonPath('data.payment.payment_status', 'pending');

        $this->assertNotEmpty($response->json('data.order_id'));
        $this->assertDatabaseHas('orders', [
            'order_id' => $response->json('data.order_id'),
            'user_id' => $user->user_id,
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $response->json('data.order_id'),
            'payment_status' => 'pending',
        ]);
    }

    public function test_order_payment_uses_route_order_id_and_completes_payment(): void
    {
        $this->ensureRoles();
        $user = User::factory()->create();

        $course = $this->createCourse(199.00);

        $cart = Cart::create([
            'cart_id' => (string) Str::uuid(),
            'user_id' => $user->user_id,
        ]);

        CartItem::create([
            'cart_item_id' => (string) Str::uuid(),
            'cart_id' => $cart->cart_id,
            'course_id' => $course->course_id,
            'quantity' => 1,
        ]);

        $order = Order::create([
            'order_id' => (string) Str::uuid(),
            'user_id' => $user->user_id,
            'order_date' => now(),
            'total_amount' => $course->price,
        ]);

        Payment::create([
            'payment_id' => (string) Str::uuid(),
            'order_id' => $order->order_id,
            'payment_date' => now(),
            'payment_method' => 'pending',
            'payment_status' => 'pending',
            'amount' => $course->price,
        ]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson("/api/orders/{$order->order_id}/payment", [
                'payment_method' => 'paypal',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.payment_status', 'completed')
            ->assertJsonPath('data.payment_method', 'paypal');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->order_id,
            'payment_status' => 'completed',
            'payment_method' => 'paypal',
        ]);
    }

    private function ensureRoles(): void
    {
        if (!UserRole::where('role_code', 'admin')->exists()) {
            UserRole::create(['role_id' => (string) Str::uuid(), 'role_code' => 'admin', 'role_name' => 'Admin']);
        }

        if (!UserRole::where('role_code', 'student')->exists()) {
            UserRole::create(['role_id' => (string) Str::uuid(), 'role_code' => 'student', 'role_name' => 'Student']);
        }

        if (!UserRole::where('role_code', 'instructor')->exists()) {
            UserRole::create(['role_id' => (string) Str::uuid(), 'role_code' => 'instructor', 'role_name' => 'Instructor']);
        }
    }
}
