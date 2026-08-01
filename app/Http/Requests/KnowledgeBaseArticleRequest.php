<?php

namespace App\Http\Requests;

use App\Models\KnowledgeBaseArticle;
use Illuminate\Foundation\Http\FormRequest;

class KnowledgeBaseArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', KnowledgeBaseArticle::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
            'status' => ['required', 'string', 'in:draft,published'],
        ];
    }
}
