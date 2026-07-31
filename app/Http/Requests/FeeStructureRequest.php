<?php

namespace App\Http\Requests;

use App\Models\FeeStructure;
use Illuminate\Foundation\Http\FormRequest;

class FeeStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', FeeStructure::class);
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'in:pesapal,yo_payments'],
            'business_id' => ['nullable', 'exists:businesses,id'],
            'status' => ['required', 'string', 'in:enabled,disabled'],
            'gateway_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'gateway_fee_flat' => ['required', 'numeric', 'min:0'],
            'platform_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'platform_fee_flat' => ['required', 'numeric', 'min:0'],
            'min_fee' => ['nullable', 'numeric', 'min:0'],
            'max_fee' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
