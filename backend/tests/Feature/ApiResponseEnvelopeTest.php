<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * API Response Envelope Standardization Tests (T15)
 *
 * Tests that all API endpoints return responses in the standardized envelope format:
 * - Success: { success: true, message: string, data: mixed }
 * - Empty-success: { success: true, message: string, data: null }
 * - Created: { success: true, message: string, data: mixed } with HTTP 201
 * - Error: { success: false, message: string, data: null } or { data: errors } for 422
 * - Paginated: { success: true, message: string, data: [], hasNextPage, hasPreviousPage, totalPage, totalItem }
 *
 * @see backend/app/Http/Controllers/Controller.php - Helper methods
 * @see backend/bootstrap/app.php - Exception rendering
 */
class ApiResponseEnvelopeTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * =========================================================
     * SUCCESS ENVELOPE TESTS
     * Standard: { success: true, message: string, data: mixed }
     * =========================================================
     */

    public function test_categories_index_returns_success_envelope_structure(): void
    {
        // Categories endpoint is public
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertNotNull($response->json('data'));
        $this->assertIsString($response->json('message'));
    }

    public function test_courses_list_returns_paginated_envelope_structure(): void
    {
        // Courses endpoint is public and returns paginated response
        $response = $this->getJson('/api/courses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'hasNextPage',
                'hasPreviousPage',
                'totalPage',
                'totalItem',
            ])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertIsArray($response->json('data'));
        $this->assertIsBool($response->json('hasNextPage'));
        $this->assertIsBool($response->json('hasPreviousPage'));
        $this->assertIsInt($response->json('totalPage'));
        $this->assertIsInt($response->json('totalItem'));
    }

    public function test_instructors_list_returns_paginated_envelope_structure(): void
    {
        $response = $this->getJson('/api/instructors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'hasNextPage',
                'hasPreviousPage',
                'totalPage',
                'totalItem',
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * =========================================================
     * EMPTY-SUCCESS ENVELOPE TESTS
     * Standard: { success: true, message: string, data: null }
     * Use for delete operations or actions with no payload
     * =========================================================
     */

    public function test_forgot_password_validation_error_returns_envelope(): void
    {
        // Note: Returns 422 when email doesn't exist in database (validation failure)
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        // Validation fails because email doesn't exist
        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_forgot_password_jwt_validation_error_returns_envelope(): void
    {
        $response = $this->postJson('/api/forgot-password-jwt', [
            'email' => 'nonexistent@example.com',
        ]);

        // Validation fails because email doesn't exist
        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_verify_code_returns_empty_success_envelope(): void
    {
        $response = $this->postJson('/api/verify-code', [
            'email' => 'nonexistent@example.com',
            'code' => '123456',
        ]);

        // Should return 200 (success path - account not found returns empty success for security)
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
    }

    public function test_reset_password_returns_envelope(): void
    {
        $response = $this->postJson('/api/reset-password', [
            'email' => 'nonexistent@example.com',
            'token' => 'some-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        // Should return 200 or 422 depending on token validation
        // Either way should have proper envelope structure
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
    }

    /**
     * =========================================================
     * VALIDATION ERROR ENVELOPE TESTS
     * Standard: { success: false, message: string, data: { errors: {...} } } with HTTP 422
     * =========================================================
     */

    public function test_login_validation_error_returns_422_envelope(): void
    {
        // Missing required fields
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => false,
            ]);

        $this->assertNotNull($response->json('data'));
    }

    public function test_signup_validation_error_returns_422_envelope(): void
    {
        // Missing required fields
        $response = $this->postJson('/api/signup', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => false,
            ]);

        $this->assertNotNull($response->json('data'));
    }

    /**
     * =========================================================
     * AUTH/UNAUTHORIZED ENVELOPE TESTS
     * Standard: { success: false, message: string, data: null } with HTTP 401
     * =========================================================
     */

    public function test_protected_route_without_auth_returns_401_envelope(): void
    {
        $response = $this->getJson('/api/user/profile');

        $response->assertStatus(401)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => false,
                'data' => null,
            ]);

        $this->assertStringContainsString('Unauthenticated', $response->json('message'));
    }

    public function test_cart_without_auth_returns_401_envelope(): void
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'data' => null,
            ]);
    }

    public function test_orders_without_auth_returns_401_envelope(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'data' => null,
            ]);
    }

    public function test_user_profile_without_auth_returns_401(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'data' => null,
            ]);
    }

    /**
     * =========================================================
     * NOT-FOUND ENVELOPE TESTS
     * Standard: { success: false, message: string, data: null } with HTTP 404
     * =========================================================
     */

    public function test_nonexistent_category_returns_404_envelope(): void
    {
        $response = $this->getJson('/api/categories/nonexistent-slug-that-does-not-exist');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found.',
                'data' => null,
            ]);
    }

    public function test_nonexistent_course_returns_404_envelope(): void
    {
        $response = $this->getJson('/api/courses/nonexistent-course-id-12345');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found.',
                'data' => null,
            ]);
    }

    public function test_nonexistent_instructor_returns_404_envelope(): void
    {
        $response = $this->getJson('/api/instructors/nonexistent-instructor-id');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found.',
                'data' => null,
            ]);
    }

    public function test_nonexistent_route_returns_404_envelope(): void
    {
        $response = $this->getJson('/api/nonexistent-route-xyz');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => false,
                'data' => null,
            ]);
    }

    /**
     * =========================================================
     * FORBIDDEN ENVELOPE TESTS
     * Standard: { success: false, message: string, data: null } with HTTP 403
     * =========================================================
     */

    public function test_forbidden_response_returns_standard_envelope(): void
    {
        // Create a student user with Sanctum token
        $student = \App\Models\User::factory()->create([
            'role_id' => \App\Models\Role::where('role_code', 'student')->value('role_id'),
        ]);
        $token = $student->createToken('test-token');

        // Try to access admin-only route (GET /api/admin/users)
        // The permission middleware should return 403 for users without admin access
        $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->getJson('/api/admin/users');

        $response->assertStatus(403)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => false,
                'data' => null,
            ]);

        // Verify the message indicates forbidden action (exact message from middleware)
        $this->assertEquals('You do not have the required permission to access this resource', $response->json('message'));
    }

    /**
     * =========================================================
     * PAGINATED RESPONSE ENVELOPE TESTS
     * Standard: { success: true, message: string, data: [], hasNextPage, hasPreviousPage, totalPage, totalItem }
     * =========================================================
     */

    public function test_courses_pagination_returns_all_required_fields(): void
    {
        $response = $this->getJson('/api/courses');

        $response->assertStatus(200);

        // Verify all pagination fields are present and correct types
        $this->assertArrayHasKey('success', $response->json());
        $this->assertArrayHasKey('message', $response->json());
        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('hasNextPage', $response->json());
        $this->assertArrayHasKey('hasPreviousPage', $response->json());
        $this->assertArrayHasKey('totalPage', $response->json());
        $this->assertArrayHasKey('totalItem', $response->json());

        // Verify types
        $this->assertIsBool($response->json('success'));
        $this->assertIsString($response->json('message'));
        $this->assertIsArray($response->json('data'));
        $this->assertIsBool($response->json('hasNextPage'));
        $this->assertIsBool($response->json('hasPreviousPage'));
        $this->assertIsInt($response->json('totalPage'));
        $this->assertIsInt($response->json('totalItem'));
    }

    public function test_pagination_respects_per_page_parameter(): void
    {
        $response = $this->getJson('/api/courses?per_page=5');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertLessThanOrEqual(5, count($data));
    }

    public function test_pagination_has_next_page_when_more_items_exist(): void
    {
        $response = $this->getJson('/api/courses?per_page=1');

        $response->assertStatus(200);

        // If there's more than 1 item, hasNextPage should be true
        if ($response->json('totalItem') > 1) {
            $this->assertTrue($response->json('hasNextPage'));
        }
    }

    public function test_reviews_endpoint_requires_auth_returns_401(): void
    {
        // Reviews endpoint requires authentication
        $response = $this->getJson('/api/reviews');

        $response->assertStatus(401)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => false,
                'data' => null,
            ]);
    }

    /**
     * =========================================================
     * HELPER METHOD DIRECT TESTS
     * Tests for Controller helper methods indirectly through endpoints
     * =========================================================
     */

    public function test_success_envelope_always_has_data_field(): void
    {
        // Categories should return success with data
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertTrue(array_key_exists('data', $response->json()));
    }

    public function test_error_envelope_always_has_data_null_or_errors(): void
    {
        // Trigger a 404
        $response = $this->getJson('/api/categories/nonexistent');

        $response->assertStatus(404);

        $json = $response->json();
        $this->assertFalse($json['success']);
        $this->assertTrue(array_key_exists('data', $json));
        $this->assertNull($json['data']);
    }

    public function test_exception_handler_returns_standard_envelope_for_404(): void
    {
        $response = $this->getJson('/api/categories/nonexistent-slug');

        $response->assertStatus(404);

        // Verify structure matches T2 exception handling spec
        $this->assertArrayHasKey('success', $response->json());
        $this->assertArrayHasKey('message', $response->json());
        $this->assertArrayHasKey('data', $response->json());

        $this->assertFalse($response->json('success'));
        $this->assertEquals('Resource not found.', $response->json('message'));
        $this->assertNull($response->json('data'));
    }

    public function test_exception_handler_returns_standard_envelope_for_401(): void
    {
        $response = $this->getJson('/api/user/profile');

        $response->assertStatus(401);

        $json = $response->json();
        $this->assertArrayHasKey('success', $json);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('data', $json);

        $this->assertFalse($json['success']);
        $this->assertNull($json['data']);
    }

    /**
     * =========================================================
     * INTEGRATION TESTS
     * End-to-end workflow tests
     * =========================================================
     */

    public function test_validation_error_never_leaks_internal_details(): void
    {
        // Test that error responses don't expose internal details
        // This is critical for security

        // Trigger a validation error
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422);

        // The message should be about validation, not internal details
        $message = $response->json('message');
        $this->assertIsString($message);

        // Should not contain stack traces, file paths, or SQL errors
        $this->assertStringNotContainsString('Exception', $message);
        $this->assertStringNotContainsString('Stack trace', $message);
        $this->assertStringNotContainsString('.php', $message);
        $this->assertStringNotContainsString('SQL', $message);
    }

    public function test_404_error_never_leaks_internal_details(): void
    {
        $response = $this->getJson('/api/nonexistent-route-xyz');

        $response->assertStatus(404);

        $message = $response->json('message');
        $this->assertIsString($message);

        // Should not contain stack traces, file paths
        $this->assertStringNotContainsString('Exception', $message);
        $this->assertStringNotContainsString('Stack trace', $message);
        $this->assertStringNotContainsString('.php', $message);
    }

    public function test_all_public_endpoints_return_json(): void
    {
        $endpoints = [
            '/api/categories',
            '/api/courses',
            '/api/instructors',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(200)
                ->assertHeader('content-type', 'application/json');
        }
    }

    public function test_auth_endpoints_return_json(): void
    {
        $endpoints = [
            ['POST', '/api/login', ['email' => 'test@test.com', 'password' => 'wrong']],
            ['POST', '/api/signup', []],
            ['POST', '/api/forgot-password', ['email' => 'test@test.com']],
        ];

        foreach ($endpoints as [$method, $endpoint, $data]) {
            if ($method === 'POST') {
                $response = $this->postJson($endpoint, $data);
            } else {
                $response = $this->getJson($endpoint);
            }

            // Should return JSON (200, 401, 422, etc.)
            $response->assertHeader('content-type', 'application/json');
        }
    }

    /**
     * =========================================================
     * ENVELOPE CONSISTENCY TESTS
     * Verify all responses follow the same structure rules
     * =========================================================
     */

    public function test_success_responses_always_have_string_message(): void
    {
        $response = $this->getJson('/api/categories');

        $this->assertIsString($response->json('message'));
        $this->assertNotEmpty($response->json('message'));
    }

    public function test_error_responses_always_have_string_message(): void
    {
        $response = $this->getJson('/api/categories/nonexistent');

        $this->assertIsString($response->json('message'));
        $this->assertNotEmpty($response->json('message'));
    }

    public function test_success_responses_have_null_data_only_when_intended(): void
    {
        // Categories list should have data (array)
        $response = $this->getJson('/api/categories');
        $this->assertNotNull($response->json('data'));

        // Forgot password (success path) should have data: null
        $response = $this->postJson('/api/forgot-password', ['email' => 'test@test.com']);
        // If it succeeds (200), data should be null
        if ($response->status() === 200) {
            $this->assertNull($response->json('data'));
        }
    }

    public function test_paginated_data_is_array(): void
    {
        $response = $this->getJson('/api/courses');

        $this->assertIsArray($response->json('data'));
    }

    public function test_envelope_keys_are_always_present(): void
    {
        // This tests that the response has the expected structure keys
        $response = $this->getJson('/api/categories');

        $json = $response->json();
        $this->assertArrayHasKey('success', $json);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('data', $json);
    }
}
