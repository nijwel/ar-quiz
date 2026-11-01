<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ImportQuizRequest extends FormRequest {
    public function authorize(): bool {
        return true; // Access is already limited by the admin route middleware
    }

    public function rules(): array {
        return [
            'quiz_file' => ['nullable', 'required_without:quiz_json', 'file', 'extensions:json', 'max:20480'],
            'quiz_json' => ['nullable', 'required_without:quiz_file', 'string'],
        ];
    }

    public function messages(): array {
        return [
            'quiz_file.required_without' => 'Choose a JSON file or paste quiz JSON.',
            'quiz_json.required_without' => 'Choose a JSON file or paste quiz JSON.',
            'quiz_file.extensions'       => 'The file must be a .json file.',
            'quiz_file.max'              => 'The file must not be larger than 20 MB.',
        ];
    }

    /**
     * The decoded JSON from the uploaded file or the pasted text.
     *
     * @throws ValidationException
     */
    public function payload(): mixed {
        $json = $this->hasFile( 'quiz_file' )
            ? file_get_contents( $this->file( 'quiz_file' )->getRealPath() )
            : $this->input( 'quiz_json' );

        // Remove a UTF-8 byte order mark that some editors add
        $json = preg_replace( '/^\xEF\xBB\xBF/', '', (string) $json );

        try {
            return json_decode( $json, true, flags: JSON_THROW_ON_ERROR );
        } catch ( \JsonException $e ) {
            throw ValidationException::withMessages( ['quiz_file' => 'The JSON is not valid: ' . $e->getMessage()] );
        }
    }
}
