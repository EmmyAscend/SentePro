<?php

namespace App\Http\Requests;

use App\Enums\BusinessType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isBusiness = $this->input('business_type') === BusinessType::Business->value;
        $isNgo = $this->input('business_type') === BusinessType::Ngo->value;
        $isIndividual = $this->input('business_type') === BusinessType::Individual->value;

        return [
            'business_type' => ['required', Rule::in(array_column(BusinessType::cases(), 'value'))],
            'business_name' => ['required', 'string', 'max:255'],
            'trading_name' => [Rule::requiredIf($isBusiness), 'nullable', 'string', 'max:255'],
            'registration_number' => [Rule::requiredIf($isBusiness || $isNgo), 'nullable', 'string', 'max:255'],
            'id_number' => [Rule::requiredIf($isIndividual), 'nullable', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email'],
            'industry' => [Rule::requiredIf($isBusiness), 'nullable', 'string', 'max:255'],
            'expected_monthly_volume' => [Rule::requiredIf($isBusiness), 'nullable', 'string', 'max:255'],
            'business_description' => ['nullable', 'string'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'unique:users,email'],
            'owner_password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
