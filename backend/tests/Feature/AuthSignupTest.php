<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthSignupTest extends TestCase
{
    use DatabaseTransactions;

    public function test_signup_creates_missing_student_role_before_creating_user(): void
    {
        Role::where('role_id', 'student')->delete();

        $this->assertDatabaseMissing('roles', ['role_id' => 'student']);

        $response = $this->postJson('/api/signup', [
            'first_name' => 'Duy',
            'last_name' => 'Nguyen',
            'email' => 'duy@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Account created successfully',
            ]);

        $this->assertDatabaseHas('roles', [
            'role_id' => 'student',
            'role_name' => 'Student',
        ]);

        $user = User::where('first_name', 'Duy')->firstOrFail();
        $account = UserAccount::where('email', 'duy@example.com')->firstOrFail();
        $student = Student::where('user_id', $user->user_id)->first();

        $this->assertSame('student', $user->role_id);
        $this->assertNotNull($student);
        $this->assertSame($user->user_id, $account->user_id);
        $this->assertTrue(Role::where('role_id', 'student')->exists());
    }
}
