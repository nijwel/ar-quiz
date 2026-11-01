<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizRequest extends FormRequest {
    public function authorize(): bool {
        return true; // Access is already limited by the student route middleware
    }

    public function rules(): array {
        return [
            'quiz_id'   => ['required', 'integer', 'exists:quizzes,id'],
            // answers[question_id] = answer_id; may be empty when time runs out
            'answers'   => ['nullable', 'array'],
            'answers.*' => ['integer'],
        ];
    }
}
