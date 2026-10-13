<?php

namespace App\Http\Requests;

use App\Enums\ProductLogAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListProductLogsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'action' => ['nullable', Rule::enum(ProductLogAction::class)],
            'product_id' => ['nullable', 'integer', 'min:1'],
            'name' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
