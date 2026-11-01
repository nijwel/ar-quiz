<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkQuizStatusRequest extends FormRequest {
    public function authorize(): bool {
        return true; // Access is already limited by the admin route middleware
    }

    public function rules(): array {
        return [
            'ids'    => ['required', 'array', 'max:500'],
            'ids.*'  => ['integer'],
            'status' => ['required', 'boolean'],
        ];
    }

    public function messages(): array {
        return [
            'ids.required' => 'অন্তত একটি কুইজ সিলেক্ট করুন।',
        ];
    }
}
