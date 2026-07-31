<?php

namespace App\Http\Requests;

use App\Models\SettlementMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettlementMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', SettlementMethod::class);
    }

    public function rules(): array
    {
        $method = $this->route('settlementMethod');

        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('settlement_methods', 'code')->ignore($method)],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:enabled,disabled'],
            'processing_time' => ['required', 'integer', 'min:0'],
            'time_unit' => ['required', 'string', 'in:hours,days,working_days'],
            'settlement_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'settlement_fee_flat' => ['required', 'numeric', 'min:0'],
            'platform_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'platform_fee_flat' => ['required', 'numeric', 'min:0'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0'],
            'daily_limit' => ['nullable', 'numeric', 'min:0'],
            'auto_approval' => ['required', 'boolean'],
            'weekend_processing' => ['required', 'boolean'],
            'public_description' => ['nullable', 'string', 'max:1000'],
            'internal_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
