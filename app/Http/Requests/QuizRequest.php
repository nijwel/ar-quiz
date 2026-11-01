<?php

namespace App\Http\Requests;

use App\Enums\Difficulty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating and updating a quiz with its questions.
 */
class QuizRequest extends FormRequest {
    public function authorize(): bool {
        return true; // Access is already limited by the admin route middleware
    }

    public function rules(): array {
        // On update the route carries the quiz id, so its own title is allowed
        $quizId = $this->route( 'id' );

        return [
            'title'               => ['required', 'string', 'max:255', Rule::unique( 'quizzes', 'title' )->ignore( $quizId )],
            'description'         => ['nullable', 'string'],
            'category_id'         => ['required', 'exists:categories,id'],
            'difficulty'          => ['required', Rule::enum( Difficulty::class )],
            'duration_minutes'    => ['nullable', 'integer', 'min:1', 'max:1440'],
            'start_exam_at'       => ['nullable', 'date'],
            'end_exam_at'         => ['nullable', 'date', 'after:start_exam_at'],
            'questions'           => ['required', 'array', 'min:1'],
            'questions.*.text'    => ['required', 'string'],
            'questions.*.answers' => ['required', 'array', 'min:2'],
            'questions.*.correct' => ['required', 'integer'],
        ];
    }

    /**
     * Quiz columns ready to save.
     */
    public function quizData(): array {
        return [
            'category_id'      => $this->integer( 'category_id' ),
            'title'            => $this->input( 'title' ),
            'description'      => $this->input( 'description' ),
            'difficulty'       => $this->enum( 'difficulty', Difficulty::class ),
            'duration_minutes' => $this->integer( 'duration_minutes' ) ?: null,
            'start_exam_at'    => $this->input( 'start_exam_at' ),
            'end_exam_at'      => $this->input( 'end_exam_at' ),
        ];
    }
}
