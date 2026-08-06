<?php

namespace App\Http\Requests;

use App\Enums\PaymentProvider;
use App\Models\GatewayProvider;
use Illuminate\Foundation\Http\FormRequest;

class GatewayProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', GatewayProvider::class);
    }

    public function rules(): array
    {
        $rules = [
            'status' => ['required', 'string', 'max:255'],
            'environment' => ['required', 'string', 'in:sandbox,production'],
            // webhook_url is computed server-side from our own receiving
            // routes, not admin-typed.
            'supported_currencies' => ['required', 'string', 'max:255'],
        ];

        /** @var GatewayProvider $gatewayProvider */
        $gatewayProvider = $this->route('gatewayProvider');

        if ($gatewayProvider->provider === PaymentProvider::YoPayments) {
            $rules['api_username'] = ['required', 'string', 'max:255'];
            $rules['api_password'] = ['required', 'string', 'max:255'];
        } else {
            $rules['consumer_key'] = ['required', 'string', 'max:255'];
            $rules['consumer_secret'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }
}
