<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyCategoryRequest extends FormRequest {
    public function authorize(): bool {
        return true; // Access is already limited by the admin route middleware
    }

    public function rules(): array {
        return [
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:categories,id'],
        ];
    }

    public function messages(): array {
        return [
            'ids.required' => 'Select at least one category.',
        ];
    }
}
