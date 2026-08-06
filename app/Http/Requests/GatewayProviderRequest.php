<?php

namespace App\Http\Requests;

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
        return [
            'status' => ['required', 'string', 'max:255'],
            'environment' => ['required', 'string', 'in:sandbox,production'],
            // webhook_url is computed server-side from our own receiving
            // routes, not admin-typed.
            'credentials' => ['required', 'json'],
            'supported_currencies' => ['required', 'string', 'max:255'],
        ];
    }
}
