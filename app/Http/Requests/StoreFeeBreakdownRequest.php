<?php

namespace App\Http\Requests;

use App\Models\FeeBreakdown;
use Illuminate\Foundation\Http\FormRequest;

class StoreFeeBreakdownRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', FeeBreakdown::class);
    }

    public function rules(): array
    {
        $rules = [
            'transaction_reference' => ['required', 'string', 'max:255'],
            'gateway_fee' => ['required', 'numeric', 'min:0'],
            'platform_fee' => ['required', 'numeric', 'min:0'],
            'net_amount' => ['required', 'numeric', 'min:0'],
        ];

        if ($this->user()->isSuperAdmin()) {
            $rules['business_id'] = ['required', 'exists:businesses,id'];
        }

        return $rules;
    }
}
