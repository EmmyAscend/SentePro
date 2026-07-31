<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffRoleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_admin_can_open_staff_management_screen(): void
    {
        $user = User::factory()->businessAdmin()->create();

        $response = $this->actingAs($user)->get('/admin/staff');

        $response->assertOk();
        $response->assertSee('Staff Management');
    }

    public function test_business_admin_can_assign_a_role_to_a_staff_user_within_their_own_business(): void
    {
        $admin = User::factory()->businessAdmin()->create();

        $response = $this->actingAs($admin)->post('/admin/staff', [
            'name' => 'Jane Staff',
            'email' => 'jane.staff@example.com',
            'password' => 'password123',
            'role' => 'staff',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'jane.staff@example.com',
            'role' => 'staff',
            'business_id' => $admin->business_id,
        ]);
    }

    public function test_business_admin_cannot_assign_staff_to_a_different_business(): void
    {
        $admin = User::factory()->businessAdmin()->create();
        $otherBusiness = Business::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/staff', [
            'name' => 'Jane Staff',
            'email' => 'jane.staff@example.com',
            'password' => 'password123',
            'role' => 'staff',
            'business_id' => $otherBusiness->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'jane.staff@example.com',
            'business_id' => $admin->business_id,
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'jane.staff@example.com',
            'business_id' => $otherBusiness->id,
        ]);
    }

    public function test_staff_member_cannot_manage_staff(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->get('/admin/staff');

        $response->assertForbidden();
    }

    public function test_super_admin_can_assign_staff_to_any_business(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $business = Business::factory()->create();

        $response = $this->actingAs($superAdmin)->post('/admin/staff', [
            'name' => 'Jane Staff',
            'email' => 'jane.staff@example.com',
            'password' => 'password123',
            'role' => 'staff',
            'business_id' => $business->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'jane.staff@example.com',
            'business_id' => $business->id,
        ]);
    }
}
