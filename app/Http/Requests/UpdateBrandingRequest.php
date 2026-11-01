<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandingRequest extends FormRequest {
    public function authorize(): bool {
        return true; // Access is already limited by the admin route middleware
    }

    public function rules(): array {
        // SVG is left out on purpose: it can carry scripts
        return [
            'logo'           => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon'        => ['nullable', 'file', 'mimes:png,ico', 'max:512'],
            'remove_logo'    => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array {
        return [
            'logo.max'    => 'Logo must be 2 MB or smaller.',
            'favicon.max' => 'Favicon must be 512 KB or smaller.',
        ];
    }
}
