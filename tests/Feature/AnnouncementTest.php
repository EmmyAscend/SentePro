<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_and_edit_an_announcement(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post('/announcements', [
            'title' => 'Scheduled maintenance',
            'body' => 'We will be undergoing maintenance this weekend.',
            'status' => 'active',
        ])->assertRedirect(route('announcements.index'));

        $announcement = Announcement::first();
        $this->assertSame('Scheduled maintenance', $announcement->title);
        $this->assertSame('active', $announcement->status);
        $this->assertSame($superAdmin->id, $announcement->created_by);

        $this->actingAs($superAdmin)->put("/announcements/{$announcement->id}", [
            'title' => 'Maintenance complete',
            'body' => 'Maintenance has finished.',
            'status' => 'inactive',
        ])->assertRedirect(route('announcements.index'));

        $this->assertSame('Maintenance complete', $announcement->fresh()->title);
        $this->assertSame('inactive', $announcement->fresh()->status);
    }

    public function test_a_business_admin_cannot_create_an_announcement(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($admin)->post('/announcements', [
            'title' => 'Should fail',
            'body' => 'Not allowed.',
            'status' => 'active',
        ])->assertForbidden();
    }

    public function test_regular_users_only_see_active_announcements(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        Announcement::create(['title' => 'Active Notice', 'body' => 'Visible.', 'status' => 'active']);
        Announcement::create(['title' => 'Inactive Notice', 'body' => 'Hidden.', 'status' => 'inactive']);

        $response = $this->actingAs($admin)->get('/announcements');

        $response->assertOk();
        $response->assertSee('Active Notice');
        $response->assertDontSee('Inactive Notice');
    }

    public function test_super_admin_sees_inactive_announcements_too(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        Announcement::create(['title' => 'Inactive Notice', 'body' => 'Hidden.', 'status' => 'inactive']);

        $response = $this->actingAs($superAdmin)->get('/announcements');

        $response->assertOk();
        $response->assertSee('Inactive Notice');
    }
}
