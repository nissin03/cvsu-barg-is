<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePhoneRequest extends FormRequest
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
        return [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.regex' => 'Phone number must start with 9 and be exactly 10 digits.',
        ];
    }

    public function normalizePhoneNumber(): string
    {
        return (string) Str::of($this->phone_number)
            ->replaceStart('+63', '')
            ->replaceStart('0', '')
            ->prepend('+63');
    }
}
