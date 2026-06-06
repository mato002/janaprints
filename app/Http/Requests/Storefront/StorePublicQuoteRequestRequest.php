<?php

namespace App\Http\Requests\Storefront;

use App\Rules\StorefrontPersonName;
use App\Rules\StorefrontPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicQuoteRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('service') && ! $this->filled('service_needed')) {
            $this->merge(['service_needed' => $this->input('service')]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $extensions = config('leads.artwork.allowed_extensions', []);

        return [
            'name' => ['required', 'string', 'min:2', 'max:120', new StorefrontPersonName],
            'company' => ['nullable', 'string', 'max:160', 'regex:/^[\p{L}\p{N}\s\'.\-&]+$/u'],
            'phone' => ['required', 'string', 'max:20', new StorefrontPhoneNumber],
            'email' => ['required', 'email', 'max:160'],
            'service' => ['sometimes', 'string', 'max:120'],
            'service_needed' => ['required', 'string', 'max:120'],
            'quantity' => ['nullable', 'string', 'max:80'],
            'deadline' => ['nullable', 'string', 'max:80'],
            'message' => ['required', 'string', 'max:3000'],
            'artwork' => [
                'nullable',
                'file',
                'max:'.config('leads.artwork.max_size_kb', 25600),
                Rule::file()->extensions($extensions),
            ],
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
