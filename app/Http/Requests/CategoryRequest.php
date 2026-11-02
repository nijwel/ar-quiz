<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Create / update a category. Groups are one level deep: a category can belong to a
 * top-level group, and a group cannot itself be put inside another group.
 */
class CategoryRequest extends FormRequest {
    public function authorize(): bool {
        return true; // Access is already limited by the admin route middleware
    }

    protected function prepareForValidation(): void {
        $this->merge( [
            'slug'      => Str::slug( $this->input( 'slug' ) ?: $this->input( 'name' ) ),
            'parent_id' => $this->input( 'parent_id' ) ?: null,
            'status'    => $this->boolean( 'status' ),
        ] );
    }

    public function rules(): array {
        $id = $this->route( 'id' );

        return [
            'name'      => ['required', 'string', 'max:255', Rule::unique( 'categories', 'name' )->ignore( $id )],
            'slug'      => ['required', 'string', 'max:255', Rule::unique( 'categories', 'slug' )->ignore( $id )],
            'status'    => ['boolean'],
            'parent_id' => [
                'nullable',
                Rule::exists( 'categories', 'id' )->whereNull( 'parent_id' ),
                Rule::notIn( array_filter( [$id] ) ),
            ],
        ];
    }

    public function messages(): array {
        return [
            'parent_id.exists' => 'Only a top-level category can be a group.',
            'parent_id.not_in' => 'A category cannot be its own group.',
        ];
    }

    public function after(): array {
        return [function ( $validator ) {
            $id = $this->route( 'id' );

            // A group with sub-categories cannot be moved inside another group
            if ( $id && $this->input( 'parent_id' ) && Category::where( 'parent_id', $id )->exists() ) {
                $validator->errors()->add( 'parent_id', 'This category has sub-categories, so it cannot be put inside another group.' );
            }
        }];
    }
}
