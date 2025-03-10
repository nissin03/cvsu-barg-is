<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class FacilityUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $rules = [
            // 'name' => [
            //     'nullable',
            //     'string',
            //     'max:255',
            //     Rule::unique('facilities')->ignore($this->route('facility')),
            // ],
            'facility_type' => 'required|string|in:individual,whole_place,both',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'requirements' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120',
            'prices' => 'nullable|array',
            'prices.*.name' => 'nullable|string',
            'prices.*.value' => 'nullable|numeric',
            'prices.*.price_type' => 'nullable|string|in:individual,whole',
            'prices.*.is_based_on_days' => 'nullable|boolean',
            'prices.*.is_there_a_quantity' => 'nullable|boolean',
            'prices.*.date_from' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return collect($request->input('prices', []))
                        ->filter(function($price) {
                            return 
                                isset($price['is_based_on_days']) && 
                                filter_var($price['is_based_on_days'], FILTER_VALIDATE_BOOLEAN) === true &&
                                (!isset($price['date_from']) || $price['date_from'] === '');
                        })
                        ->isNotEmpty();
                }),
            ],
            'prices.*.date_to' => [
                'nullable',
                'after_or_equal:prices.*.date_from',
                Rule::requiredIf(function () use ($request) {
                    return collect($request->input('prices', []))
                        ->filter(function($price) {
                            return 
                                isset($price['is_based_on_days']) && 
                                filter_var($price['is_based_on_days'], FILTER_VALIDATE_BOOLEAN) === true &&
                                (!isset($price['date_to']) || $price['date_to'] === '');
                        })
                        ->isNotEmpty();
                }),
            ],
            'whole_capacity' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if (($request->input('facility_type') === 'whole_place' ||
                        ($request->input('facility_type') === 'both' && empty($request->input('facility_attributes', [])))) &&
                        (!is_numeric($value) || $value < 1)) {
                        $fail('The whole capacity must be a number greater than 0.');
                    }
                },
            ],
            'facility_attributes' => 'nullable|array',
            'facility_attributes.*.room_name' => 'required_with:facility_attributes.*.capacity|string|max:255',
            'facility_attributes.*.capacity' => [
                'required_with:facility_attributes.*.room_name',
                'integer',
                'min:1',
                'nullable' => false,
            ],

            'facility_attributes.*.sex_restriction' => 'nullable|string|in:male,female',
        ];
        return $rules;
    }
}
