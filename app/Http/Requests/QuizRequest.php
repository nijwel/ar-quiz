<?php

namespace App\Http\Requests;

use App\Enums\Difficulty;
use App\Models\Category;
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
            'category_id'         => ['required', 'exists:categories,id', function ( $attribute, $value, $fail ) {
                // A group such as Programming only holds sub-categories, not quizzes
                if ( Category::where( 'parent_id', $value )->exists() ) {
                    $fail( 'Choose a sub-category (e.g. PHP), not a group.' );
                }
            }],
            'difficulty'          => ['required', Rule::enum( Difficulty::class )],
            'duration_minutes'    => ['nullable', 'integer', 'min:1', 'max:1440'],
            'start_exam_at'       => ['nullable', 'date'],
            // Without a start the quiz is open right away, so the end only has to follow a start that was given
            'end_exam_at'         => ['nullable', 'date', Rule::when( $this->filled( 'start_exam_at' ), 'after:start_exam_at' )],
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

    /**
     * The submitted questions in the shape QuizTransfer::saveQuestions() expects.
     */
    public function questionsData(): array {
        return collect( $this->input( 'questions', [] ) )->values()->map( fn( $question ) => [
            'question' => $question['text'],
            'answers'  => collect( $question['answers'] )->values()->map( fn( $text, $index ) => [
                'answer'     => $text,
                'is_correct' => $index === (int) $question['correct'],
            ] )->all(),
        ] )->all();
    }
}
