<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class FacilityRequest extends FormRequest
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
    public function rules(Request $request, $id): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('facilities')->ignore($id), // Ignore unique check for unchanged name
            ],
            'facility_type' => 'required|string|in:individual,whole_place,both',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'requirements' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120',
            'sex_restriction' => 'nullable|in:male,female',
            'prices' => 'nullable|array',
            'prices.*.name' => 'nullable|string',
            'prices.*.value' => 'nullable|numeric',
            'prices.*.price_type' => 'nullable|string|in:individual,whole',
            'prices.*.is_based_on_days' => 'nullable|boolean',
            'facility_attributes' => 'nullable|array',
            'facility_attributes.*.room_name' => 'nullable|string|max:255',
            'facility_attributes.*.capacity' => 'nullable|integer|min:1',
            'facility_attributes.*.sex_restriction' => 'nullable|in:male,female',
        
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The facility name is required.',
            'description.required' => 'The description is required.',
            'image.required' => 'The main image is required.',
        ];
    }
}
