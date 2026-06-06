<?php

namespace App\Http\Requests\Storefront;

use App\Rules\StorefrontPersonName;
use App\Rules\StorefrontPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class StorePublicContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120', new StorefrontPersonName],
            'company' => ['nullable', 'string', 'max:160', 'regex:/^[\p{L}\p{N}\s\'.\-&]+$/u'],
            'phone' => ['nullable', 'string', 'max:20', new StorefrontPhoneNumber],
            'email' => ['required', 'email', 'max:160'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:3000'],
            'website' => ['nullable', 'max:0'],
            '_gotcha' => ['nullable', 'max:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'website.max' => __('Submission rejected.'),
            '_gotcha.max' => __('Submission rejected.'),
        ];
    }
}
