<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInterestsRequest extends FormRequest {
    public function authorize(): bool {
        return true; // Access is already limited by the student route middleware
    }

    public function rules(): array {
        $max = Setting::maxInterests();

        return [
            'interests'   => ['required', 'array', 'min:1', "max:$max"],
            'interests.*' => ['integer', 'distinct', Rule::exists( 'categories', 'id' )->where( 'status', true )],
        ];
    }

    public function messages(): array {
        return [
            'interests.required' => 'অন্তত একটি বিষয় বেছে নিন।',
            'interests.min'      => 'অন্তত একটি বিষয় বেছে নিন।',
            'interests.max'      => 'সর্বোচ্চ :max টি বিষয় বেছে নেওয়া যাবে।',
        ];
    }

    /**
     * The chosen ids without sub-categories whose group is also chosen,
     * because choosing the group already covers them.
     */
    public function categoryIds(): array {
        $ids = array_map( 'intval', $this->input( 'interests', [] ) );

        $covered = Category::whereIn( 'id', $ids )->whereIn( 'parent_id', $ids )->pluck( 'id' )->all();

        return array_values( array_diff( $ids, $covered ) );
    }
}
