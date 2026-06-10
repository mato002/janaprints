<?php

namespace App\Http\Requests\Admin\PrintingIntelligence;

use App\Enums\PrintInkType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePrintInkProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('printing.ink-profiles.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = (int) (tenant()->companyId() ?? $this->user()?->company_id);

        return [
            'name' => ['required', 'string', 'max:120'],
            'ink_type' => ['required', Rule::enum(PrintInkType::class)],
            'inventory_item_id' => [
                'nullable',
                'integer',
                Rule::exists('inventory_items', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'cartridge_cost' => ['required', 'numeric', 'min:0'],
            'estimated_ml' => ['nullable', 'numeric', 'gt:0'],
            'cost_per_ml' => ['nullable', 'numeric', 'min:0'],
            'estimated_yield_pages' => ['nullable', 'integer', 'min:0'],
            'estimated_yield_sq_m' => ['nullable', 'numeric', 'min:0'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            'name' => $this->input('name'),
            'ink_type' => $this->input('ink_type'),
            'inventory_item_id' => $this->input('inventory_item_id') ?: null,
            'cartridge_cost' => $this->input('cartridge_cost'),
            'estimated_ml' => $this->input('estimated_ml') ?: null,
            'cost_per_ml' => $this->input('cost_per_ml') ?: null,
            'estimated_yield_pages' => $this->input('estimated_yield_pages') ?: null,
            'estimated_yield_sq_m' => $this->input('estimated_yield_sq_m') ?: null,
            'active' => $this->boolean('active'),
        ];
    }
}
