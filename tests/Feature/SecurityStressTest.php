<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;

class SecurityStressTest extends TestCase
{
    use RefreshDatabase;

    protected User $testUser;
    protected User $employeeUser;
    protected Role $employeeRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create employee role
        $this->employeeRole = Role::create([
            'name' => 'Employee',
            'slug' => 'employee',
            'role_level' => 3,
            'permissions' => json_encode(['attendance.create', 'attendance.view']),
            'is_active' => true,
        ]);

        // Create test users
        $this->testUser = User::create([
            'full_name' => 'Test User',
            'username' => 'testuser',
            'emp_code' => 'EMP999',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->employeeRole->id,
            'is_active' => true,
            'is_super_admin' => false,
        ]);

        $this->employeeUser = User::create([
            'full_name' => 'Employee Test',
            'username' => 'employee',
            'emp_code' => 'EMP888',
            'email' => 'employee@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->employeeRole->id,
            'is_active' => true,
            'is_super_admin' => false,
        ]);
    }

    /** @test */
    public function test_login_with_non_existent_employee_id()
    {
        $response = $this->post('/admin/login', [
            'login' => 'FAKE999',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['data.login']);
        $this->assertGuest();
    }

    /** @test */
    public function test_login_with_non_existent_email()
    {
        $response = $this->post('/admin/login', [
            'login' => 'fake@nonexistent.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['data.login']);
        $this->assertGuest();
    }

    /** @test */
    public function test_rate_limiter_blocks_after_5_attempts()
    {
        RateLimiter::clear('emp999|127.0.0.1');

        // Attempt 6 times with wrong password
        for ($i = 1; $i <= 6; $i++) {
            $response = $this->post('/admin/login', [
                'login' => 'EMP999',
                'password' => 'wrongpassword',
            ]);

            if ($i < 6) {
                // First 5 attempts should show "failed" message
                $response->assertSessionHasErrors(['data.login']);
            } else {
                // 6th attempt should show "throttle" message
                $response->assertSessionHasErrors(['data.login']);
                $errors = session('errors')->get('data.login');
                $this->assertStringContainsString('throttle', strtolower(implode(' ', $errors)));
            }
        }

        $this->assertGuest();
    }

    /** @test */
    public function test_successful_login_clears_rate_limiter()
    {
        RateLimiter::clear('emp999|127.0.0.1');

        // Make 2 failed attempts
        for ($i = 1; $i <= 2; $i++) {
            $this->post('/admin/login', [
                'login' => 'EMP999',
                'password' => 'wrongpassword',
            ]);
        }

        // Now login successfully
        $response = $this->post('/admin/login', [
            'login' => 'EMP999',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticated();

        // Check that rate limiter was cleared
        $throttleKey = 'emp999|127.0.0.1';
        $this->assertEquals(0, RateLimiter::attempts($throttleKey));
    }

    /** @test */
    public function test_login_with_emp_code_instead_of_employee_id()
    {
        $response = $this->post('/admin/login', [
            'login' => 'EMP999',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    /** @test */
    public function test_login_with_email()
    {
        $response = $this->post('/admin/login', [
            'login' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    /** @test */
    public function test_employee_cannot_access_roles_resource()
    {
        $this->actingAs($this->employeeUser);

        // Try to access /admin/roles
        $response = $this->get('/admin/roles');

        // Should be forbidden (403) or redirected
        $this->assertTrue(
            $response->status() === 403 || $response->status() === 302,
            "Employee should not be able to access roles resource. Got status: {$response->status()}"
        );
    }

    /** @test */
    public function test_employee_cannot_access_users_resource()
    {
        $this->actingAs($this->employeeUser);

        $response = $this->get('/admin/users');

        $this->assertTrue(
            $response->status() === 403 || $response->status() === 302,
            "Employee should not be able to access users resource. Got status: {$response->status()}"
        );
    }

    /** @test */
    public function test_super_admin_can_access_all_resources()
    {
        $superAdmin = User::create([
            'full_name' => 'Super Admin',
            'username' => 'superadmin',
            'emp_code' => 'ADM001',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->employeeRole->id,
            'is_active' => true,
            'is_super_admin' => true,
        ]);

        $this->actingAs($superAdmin);

        $response = $this->get('/admin/roles');
        $this->assertTrue($response->isOk() || $response->isRedirect('/admin'));

        $response = $this->get('/admin/users');
        $this->assertTrue($response->isOk() || $response->isRedirect('/admin'));
    }
}
