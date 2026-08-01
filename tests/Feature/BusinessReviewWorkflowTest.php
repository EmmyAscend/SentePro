<?php

namespace Tests\Feature;

use App\Mail\BusinessReviewedMail;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BusinessReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_mark_a_business_as_approved_through_review_flow(): void
    {
        Mail::fake();

        $business = Business::factory()->create([
            'business_name' => 'Review Flow Business',
            'status' => 'pending',
        ]);
        $admin = User::factory()->businessAdmin($business)->create();

        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post('/admin/businesses/'.$business->id.'/review', [
            'status' => 'approved',
            'review_notes' => 'Verified with corporate documents.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'approved',
            'review_notes' => 'Verified with corporate documents.',
            'reviewed_by' => $superAdmin->id,
        ]);

        Mail::assertQueued(BusinessReviewedMail::class, fn ($mail) => $mail->business->is($business)
            && $mail->status === 'approved'
            && $mail->hasTo($admin->email));
    }

    public function test_business_admin_cannot_review_a_business(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'Review Flow Business',
            'status' => 'pending',
        ]);

        $businessAdmin = User::factory()->businessAdmin()->create();

        $response = $this->actingAs($businessAdmin)->post('/admin/businesses/'.$business->id.'/review', [
            'status' => 'approved',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'pending',
        ]);
    }
}
