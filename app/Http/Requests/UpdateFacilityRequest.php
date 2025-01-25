<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFacilityRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $id = $this->route('facility'); // Get the facility ID from the route

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('facilities', 'name')->ignore($id),
            ],
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
            'prices.*.date_from' => 'nullable|date|required_if:prices.*.is_based_on_days,true',
            'prices.*.date_to' => 'nullable|date|required_if:prices.*.is_based_on_days,true|after_or_equal:prices.*.date_from',
            'whole_capacity' => $this->facilityTypeRequiresWholeCapacity() ? 'required|numeric|min:1' : 'nullable',
            'facility_attributes' => 'nullable|array',
            'facility_attributes.*.room_name' => 'nullable|string|max:255',
            'facility_attributes.*.capacity' => 'nullable|integer|min:1',
            'facility_attributes.*.sex_restriction' => 'nullable|in:male,female',
        ];
    }


    private function facilityTypeRequiresWholeCapacity(): bool
       {
           $facilityType = $this->input('facility_type');
           return $facilityType === 'whole_place' || ($facilityType === 'both' && empty($this->input('facility_attributes', [])));
       }
    /**
     * Modify the request data before validation.
     */
    protected function prepareForValidation()
    {
        $facility = $this->route('facility'); // Retrieve the facility model from the route

        // Merge existing name if not provided in the request
        if (!$this->has('name') || empty($this->input('name'))) {
            $this->merge(['name' => $facility->name]);
        }

        // Handle `sex_restriction` if it's null
        if ($this->has('sex_restriction') && is_null($this->input('sex_restriction'))) {
            $this->merge(['sex_restriction' => '']);
        }
    }
}
