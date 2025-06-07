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
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'facility_type' => 'required|string|in:individual,whole_place,both',
            'slug' => 'unique:facilities,slug,' . ($this->facility->id ?? 'NULL'),
            'description' => 'required|string',
            'rules_and_regulations' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'images' => 'nullable|array|max:3',
            'status' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'requirements' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'sex_restriction' => 'nullable|in:male,female',
            'prices' => 'required|array',
            'prices.*.name' => 'required|string',
            'prices.*.value' => 'required|numeric|min:0',
            'prices.*.price_type' => 'required|string|in:individual,whole',
            'prices.*.is_based_on_days' => 'required|boolean',
            'prices.*.is_there_a_quantity' => 'required|boolean',
            'prices.*.date_from' => 'required_if:prices.*.is_based_on_days,true|nullable|date',
            'prices.*.date_to' => 'required_if:prices.*.is_based_on_days,true|nullable|date|after_or_equal:prices.*.date_from',
            'whole_capacity' => $this->facilityTypeRequiresWholeCapacity() ? 'required|numeric|min:1' : 'nullable',
            'facility_attributes' => 'nullable|array',
            'facility_attributes.*.room_name' => 'required_with:facility_attributes|string',
            'facility_attributes.*.capacity' => 'required_with:facility_attributes|integer|min:1',
            'facility_attributes.*.sex_restriction' => 'nullable|in:male,female',
        ];

        return $rules;
    }

    private function facilityTypeRequiresWholeCapacity(): bool
    {
        $facilityType = $this->input('facility_type');
        return $facilityType === 'whole_place' || ($facilityType === 'both' && empty($this->input('facility_attributes', [])));
    }

    public function messages()
    {
        return [
            'name.required' => 'The facility name is required.',
            'description.required' => 'The description is required.',
            'image.required' => 'The main image is required.',
            'image.max' => 'The main image must not exceed 2MB.',
            'images.max' => 'You can only upload up to 3 gallery images.',
            'images.*.max' => 'Each gallery image must not exceed 2MB.',
            'requirements.max' => 'The requirements file must not exceed 2MB.',
        ];
    }
}
