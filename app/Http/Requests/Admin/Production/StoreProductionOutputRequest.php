<?php

namespace App\Http\Requests\Admin\Production;

use App\Models\Production\ProductionJobCard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductionOutputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('production.outputs.post') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $manualCost = $this->user()?->can('production.outputs.manual-cost') ?? false;

        return [
            'finished_inventory_item_id' => [
                'nullable',
                Rule::exists('inventory_items', 'id')->where('company_id', tenant()->companyId() ?? $this->user()?->company_id),
            ],
            'quantity_completed' => ['required', 'numeric', 'gt:0'],
            'quantity_rejected' => ['nullable', 'numeric', 'gte:0'],
            'unit_cost' => [$manualCost ? 'nullable' : 'prohibited', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
