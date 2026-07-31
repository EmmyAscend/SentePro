<?php

namespace App\Http\Requests;

use App\Models\GatewayProvider;
use Illuminate\Foundation\Http\FormRequest;

class StoreGatewayProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', GatewayProvider::class);
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'string', 'in:pesapal,yo_payments'],
            'status' => ['required', 'string', 'max:255'],
            'environment' => ['required', 'string', 'in:sandbox,production'],
            // webhook_url is computed server-side from our own receiving routes,
            // not client-typed — a business admin shouldn't be entering our URL.
            'credentials' => ['required', 'json'],
            'supported_countries' => ['required', 'string', 'max:255'],
            'supported_currencies' => ['required', 'string', 'max:255'],
        ];

        if ($this->user()->isSuperAdmin()) {
            $rules['business_id'] = ['required', 'exists:businesses,id'];
        }

        return $rules;
    }
}
