<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_registration_page_is_available(): void
    {
        $response = $this->get('/business/register');

        $response->assertOk();
        $response->assertSee('Business Registration');
    }

    public function test_business_registration_form_accepts_valid_business_details(): void
    {
        $response = $this->post('/business/register', [
            'owner_name' => 'Jane Owner',
            'owner_email' => 'owner@sentepro.test',
            'owner_password' => 'password123',
            'owner_password_confirmation' => 'password123',
            'business_name' => 'SentePro Demo Business',
            'trading_name' => 'SentePro Demo',
            'registration_number' => 'REG-123456',
            'country' => 'Uganda',
            'phone' => '+256700000000',
            'email' => 'admin@sentepro.test',
            'industry' => 'Payments',
            'expected_monthly_volume' => '1000000',
            'business_description' => 'A test business collecting payments online.',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }
}
