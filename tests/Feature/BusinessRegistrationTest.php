<?php

namespace Tests\Feature;

use App\Models\Business;
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

    public function test_registration_page_shows_the_type_prompt_and_a_checkbox_affordance_per_card(): void
    {
        $response = $this->get('/business/register');

        $response->assertOk();
        $response->assertSee('What are you registering? Choose one below');
        $response->assertSee('Individual');
        $response->assertSee('Business');
        $response->assertSee('Non-Profit Organisation');
        $response->assertSee('h-5 w-5 rounded-full border-2 border-white/20', false);
    }

    public function test_business_registration_form_accepts_valid_business_details(): void
    {
        $response = $this->post('/business/register', [
            'owner_name' => 'Jane Owner',
            'owner_email' => 'owner@sentepro.test',
            'owner_password' => 'password123',
            'owner_password_confirmation' => 'password123',
            'business_type' => 'business',
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

    public function test_business_type_is_required(): void
    {
        $response = $this->post('/business/register', $this->baseOwnerPayload());

        $response->assertSessionHasErrors('business_type');
    }

    public function test_registering_as_an_individual_only_requires_individual_fields(): void
    {
        $response = $this->post('/business/register', array_merge($this->baseOwnerPayload(), [
            'business_type' => 'individual',
            'business_name' => 'Jane Freelancer',
            'id_number' => 'ID-987654',
            'country' => 'Uganda',
            'phone' => '+256700000001',
            'email' => 'jane@sentepro.test',
        ]));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertSame('individual', Business::first()->business_type->value);
    }

    public function test_individual_registration_requires_an_id_number(): void
    {
        $response = $this->post('/business/register', array_merge($this->baseOwnerPayload(), [
            'business_type' => 'individual',
            'business_name' => 'Jane Freelancer',
            'country' => 'Uganda',
            'phone' => '+256700000001',
            'email' => 'jane@sentepro.test',
        ]));

        $response->assertSessionHasErrors('id_number');
    }

    public function test_registering_as_a_non_profit_requires_a_registration_number_but_not_trading_name(): void
    {
        $response = $this->post('/business/register', array_merge($this->baseOwnerPayload(), [
            'business_type' => 'ngo',
            'business_name' => 'Helping Hands NGO',
            'registration_number' => 'NGO-123456',
            'country' => 'Uganda',
            'phone' => '+256700000002',
            'email' => 'ngo@sentepro.test',
        ]));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $business = Business::first();
        $this->assertSame('ngo', $business->business_type->value);
        $this->assertNull($business->trading_name);
    }

    public function test_business_registration_requires_trading_name_and_industry(): void
    {
        $response = $this->post('/business/register', array_merge($this->baseOwnerPayload(), [
            'business_type' => 'business',
            'business_name' => 'SentePro Demo Business',
            'registration_number' => 'REG-123456',
            'country' => 'Uganda',
            'phone' => '+256700000000',
            'email' => 'admin@sentepro.test',
        ]));

        $response->assertSessionHasErrors(['trading_name', 'industry', 'expected_monthly_volume']);
    }

    public function test_registration_page_preselects_a_valid_type_from_the_query_string(): void
    {
        $response = $this->get('/business/register?type=ngo');

        $response->assertOk();
        $response->assertSee("type: 'ngo'", false);
    }

    public function test_registration_page_ignores_an_invalid_type_query_value(): void
    {
        $response = $this->get("/business/register?type='};alert(1);//");

        $response->assertOk();
        $response->assertSee("type: ''", false);
        $response->assertDontSee('alert(1)', false);
    }

    public function test_registration_page_title_reflects_the_preselected_type_before_any_click(): void
    {
        $response = $this->get('/business/register?type=ngo');

        $response->assertOk();
        $response->assertSee('Non-Profit Organisation Registration');
        $response->assertSee('Non-Profit Organisation onboarding');
    }

    public function test_registration_page_title_defaults_to_business_when_no_type_is_chosen(): void
    {
        $response = $this->get('/business/register');

        $response->assertOk();
        $response->assertSee('Business Registration');
        $response->assertSee('Business onboarding');
    }

    private function baseOwnerPayload(): array
    {
        return [
            'owner_name' => 'Jane Owner',
            'owner_email' => 'owner-'.uniqid().'@sentepro.test',
            'owner_password' => 'password123',
            'owner_password_confirmation' => 'password123',
        ];
    }
}
