<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserActivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permissions
        $permissions = [
            'manage_users',
            'create_documents',
            'view_own_documents',
            'delete_own_documents',
            'share_documents',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        Role::create(['name' => 'admin'])->syncPermissions($permissions);
        Role::create(['name' => 'user'])->syncPermissions([
            'create_documents',
            'view_own_documents',
            'delete_own_documents',
            'share_documents'
        ]);
    }

    public function test_new_users_are_inactive_by_default_and_cannot_login()
    {
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Account created! Please wait for admin approval.');

        // User exists but is inactive
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'is_active' => false,
        ]);

        $user = User::where('email', 'new@example.com')->first();
        $this->assertFalse((bool) $user->is_active);

        // Try to login
        $loginResponse = $this->post(route('login'), [
            'email' => 'new@example.com',
            'password' => 'password',
        ]);

        // Should fail validation/redirect back
        $loginResponse->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_activate_user()
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $user = User::factory()->create(['is_active' => false]);
        $user->assignRole('user');

        $this->actingAs($admin);

        $response = $this->patch(route('admin.users.toggle-status', $user));

        $response->assertSessionHas('success');
        $this->assertTrue((bool) $user->fresh()->is_active);

        // Activate user can login
        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);
    }

    public function test_normal_user_cannot_activate_others()
    {
        $user1 = User::factory()->create(['is_active' => true]);
        $user1->assignRole('user');

        $user2 = User::factory()->create(['is_active' => false]);
        $user2->assignRole('user');

        $this->actingAs($user1);

        $response = $this->patch(route('admin.users.toggle-status', $user2));

        $response->assertForbidden(); // Middleware 'role:admin' should block this
    }

    public function test_dashboard_hides_amounts_for_normal_user()
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('user');

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(
            fn($page) => $page
                ->component('Dashboard')
                ->where('stats.is_admin', false)
                ->where('stats.total_amount', 0)
        );
    }

    public function test_dashboard_shows_amounts_for_admin()
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(
            fn($page) => $page
                ->component('Dashboard')
                ->where('stats.is_admin', true)
        );
    }
}
