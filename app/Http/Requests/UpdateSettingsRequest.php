<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest {
    public function authorize(): bool {
        return true; // Access is already limited by the admin route middleware
    }

    public function rules(): array {
        return [
            'seconds_per_question' => ['required', 'integer', 'min:5', 'max:3600'],
            'max_attempts'         => ['required', 'integer', 'min:1', 'max:100'],
            'show_answer_review'   => ['required', 'boolean'],
            'max_interests'        => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }
}
