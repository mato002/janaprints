<?php

namespace App\Http\Requests\Admin\PrintingIntelligence;

use App\Services\PrintingIntelligence\PrintingIntelligenceConfigurationService;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePrintingIntelligenceConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('printing.intelligence.configure') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (app(PrintingIntelligenceConfigurationService::class)->editableDefinitions() as $key => $definition) {
            $rules[$key] = match ($definition['type']) {
                'boolean' => ['nullable', 'boolean'],
                'integer' => ['nullable', 'integer'],
                'float' => ['nullable', 'numeric'],
                default => ['nullable', 'string', 'max:255'],
            };
        }

        return $rules;
    }
}
