<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_a_new_role(): void
    {
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => null,
            'is_super_admin' => true,
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->withoutMiddleware()
            ->post(route('access-control.store'), [
                'name' => 'Support Manager',
                'slug' => 'support-manager',
                'description' => 'Handles support tasks',
            ]);

        $response->assertRedirect(route('access-control.index'));
        $this->assertDatabaseHas('roles', ['slug' => 'support-manager']);
    }
}
