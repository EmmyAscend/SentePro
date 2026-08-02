<?php

namespace App\Http\Requests;

use App\Models\LegalPage;
use Illuminate\Foundation\Http\FormRequest;

class LegalPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', LegalPage::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }
}
