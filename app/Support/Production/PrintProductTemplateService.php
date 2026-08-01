<?php

namespace App\Support\Production;

use App\Enums\PrintProductTemplateCategory;
use App\Enums\PrintInkType;
use App\Enums\ProductionType;
use App\Models\Production\PrintProductTemplate;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PrintProductTemplateService
{
    /**
     * @return array<string, mixed>
     */
    public function validationRules(?PrintProductTemplate $existing = null): array
    {
        $companyId = tenant()->companyId();

        return [
            'code' => [
                'nullable',
                'string',
                'max:40',
                Rule::unique('print_product_templates', 'code')
                    ->where(fn ($q) => $q->where('company_id', $companyId))
                    ->ignore($existing?->id),
            ],
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::enum(PrintProductTemplateCategory::class)],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'production_type' => ['nullable', Rule::enum(ProductionType::class)],
            'default_paper_inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'default_material_inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'gsm' => ['nullable', 'string', 'max:40'],
            'default_size' => ['nullable', 'string', 'max:80'],
            'default_finished_size' => ['nullable', 'string', 'max:80'],
            'default_sheet_size' => ['nullable', 'string', 'max:80'],
            'default_orientation' => ['nullable', 'string', 'max:20'],
            'default_colour_mode' => ['nullable', 'string', 'max:40'],
            'number_of_colours' => ['nullable', 'integer', 'min:1', 'max:16'],
            'default_sides' => ['nullable', 'string', 'max:20'],
            'default_binding_type' => ['nullable', 'string', 'max:60'],
            'default_finishing_type' => ['nullable', 'string', 'max:60'],
            'default_lamination' => ['nullable', 'boolean'],
            'default_foiling' => ['nullable', 'boolean'],
            'default_spot_uv' => ['nullable', 'boolean'],
            'default_embossing' => ['nullable', 'boolean'],
            'default_debossing' => ['nullable', 'boolean'],
            'default_die_cutting' => ['nullable', 'boolean'],
            'default_creasing' => ['nullable', 'boolean'],
            'default_perforation' => ['nullable', 'boolean'],
            'default_numbering_required' => ['nullable', 'boolean'],
            'default_eyelets' => ['nullable', 'boolean'],
            'default_waste_allowance_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_ups' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'default_notes' => ['nullable', 'string', 'max:10000'],
            'artwork_required' => ['nullable', 'boolean'],
            'bleed_required' => ['nullable', 'boolean'],
            'safe_margin' => ['nullable', 'string', 'max:40'],
            'resolution_recommendation' => ['nullable', 'string', 'max:80'],
            'preferred_work_center_id' => ['nullable', 'integer', 'exists:work_centers,id'],
            'preferred_machine_asset_id' => ['nullable', 'integer', 'exists:fixed_assets,id'],
            'preferred_operator_skill' => ['nullable', 'string', 'max:80'],
            'optional_outsource' => ['nullable', 'boolean'],
            'recommended_qc_checklist_id' => ['nullable', 'integer', 'exists:product_qc_checklists,id'],
            'recommended_packaging' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function paginate(Request $request): LengthAwarePaginator
    {
        return $this->filteredQuery($request)
            ->with(['preferredWorkCenter:id,name,code', 'defaultPaperInventoryItem:id,item_name'])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * @return \Illuminate\Support\Collection<int, PrintProductTemplate>
     */
    public function activeForSelection(?string $category = null)
    {
        return PrintProductTemplate::query()
            ->forTenant()
            ->active()
            ->when($category, fn (Builder $q) => $q->where('category', $category))
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'category', 'production_type']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, int $branchId, User $user, array $data): PrintProductTemplate
    {
        $rawCode = Arr::get($data, 'code');
        $code = filled($rawCode)
            ? Str::upper(Str::slug($rawCode, ''))
            : $this->uniqueCode((string) Arr::get($data, 'name', 'TEMPLATE'), $companyId);

        return PrintProductTemplate::query()->create([
            ...$this->normalizePayload($data),
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'code' => $code,
            'is_active' => Arr::get($data, 'is_active', true),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PrintProductTemplate $template, array $data, User $user): PrintProductTemplate
    {
        $payload = $this->normalizePayload($data);

        if (isset($payload['code'])) {
            $payload['code'] = Str::upper(Str::slug($payload['code'], ''));
        }

        $template->update([
            ...$payload,
            'updated_by' => $user->id,
        ]);

        return $template->fresh();
    }

    public function duplicate(PrintProductTemplate $template, User $user): PrintProductTemplate
    {
        $copy = $template->replicate(['code', 'revision_number']);
        $copy->code = $this->uniqueCode($template->code.'-COPY', $template->company_id);
        $copy->name = $template->name.' ('.__('Copy').')';
        $copy->is_active = false;
        $copy->revision_number = 1;
        $copy->created_by = $user->id;
        $copy->updated_by = $user->id;
        $copy->save();

        return $copy;
    }

    public function toggleActive(PrintProductTemplate $template, User $user): PrintProductTemplate
    {
        $template->update([
            'is_active' => ! $template->is_active,
            'updated_by' => $user->id,
        ]);

        return $template->fresh();
    }

    /**
     * Map template defaults to ProductionSpecification field keys.
     *
     * @return array<string, mixed>
     */
    public function applyToSpecificationDefaults(PrintProductTemplate $template): array
    {
        $notes = $template->default_notes;

        if ($template->gsm) {
            $gsmNote = __('GSM').': '.$template->gsm;
            $notes = $notes ? $gsmNote."\n".$notes : $gsmNote;
        }

        return array_filter([
            'print_product_template_id' => $template->id,
            'production_type' => $template->production_type?->value,
            'product_description' => $template->name,
            'size' => $template->default_size,
            'finished_size' => $template->default_finished_size,
            'sheet_size' => $template->default_sheet_size,
            'orientation' => $template->default_orientation,
            'paper_inventory_item_id' => $template->default_paper_inventory_item_id,
            'material_inventory_item_id' => $template->default_material_inventory_item_id,
            'colour_mode' => $template->default_colour_mode ?? ($template->number_of_colours ? $template->number_of_colours.'/0' : null),
            'sides' => $template->default_sides,
            'binding_type' => $template->default_binding_type,
            'finishing_type' => $template->default_finishing_type,
            'lamination' => $template->default_lamination,
            'foiling' => $template->default_foiling,
            'spot_uv' => $template->default_spot_uv,
            'embossing' => $template->default_embossing,
            'debossing' => $template->default_debossing,
            'die_cutting' => $template->default_die_cutting,
            'creasing' => $template->default_creasing,
            'perforation' => $template->default_perforation,
            'numbering_required' => $template->default_numbering_required,
            'eyelets' => $template->default_eyelets,
            'ups' => $template->default_ups,
            'waste_allowance_percent' => $template->default_waste_allowance_percent,
            'production_notes' => $notes,
        ], fn ($value) => $value !== null && $value !== '' && $value !== false);
    }

    /**
     * Template defaults fill empty user fields; explicit user values win.
     *
     * @param  array<string, mixed>  $templateDefaults
     * @param  array<string, mixed>  $userInput
     * @return array<string, mixed>
     */
    public function mergeWithUserInput(array $templateDefaults, array $userInput): array
    {
        $merged = $templateDefaults;

        foreach ($userInput as $key => $value) {
            if ($this->isFilledValue($value)) {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function exportRows(Request $request): array
    {
        return $this->filteredQuery($request)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn (PrintProductTemplate $template) => [
                'code' => $template->code,
                'name' => $template->name,
                'category' => $template->category?->label(),
                'production_type' => $template->production_type?->value,
                'gsm' => $template->gsm,
                'finished_size' => $template->default_finished_size,
                'active' => $template->is_active ? __('Yes') : __('No'),
            ])
            ->all();
    }

    protected function filteredQuery(Request $request): Builder
    {
        $query = PrintProductTemplate::query()->forTenant();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        if ($request->query('active') === '1') {
            $query->where('is_active', true);
        } elseif ($request->query('active') === '0') {
            $query->where('is_active', false);
        }

        return $query;
    }

    protected function uniqueCode(string $base, int $companyId): string
    {
        $code = Str::upper(Str::slug($base, ''));
        $suffix = 1;

        while (PrintProductTemplate::query()->where('company_id', $companyId)->where('code', $code)->exists()) {
            $code = Str::upper(Str::slug($base, '')).'-'.$suffix;
            $suffix++;
        }

        return $code;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizePayload(array $data): array
    {
        $keys = array_keys($this->validationRules());
        $payload = Arr::only($data, $keys);

        foreach ([
            'default_lamination', 'default_foiling', 'default_spot_uv', 'default_embossing',
            'default_debossing', 'default_die_cutting', 'default_creasing', 'default_perforation',
            'default_numbering_required', 'default_eyelets', 'artwork_required', 'bleed_required',
            'optional_outsource', 'is_active',
        ] as $boolKey) {
            if (array_key_exists($boolKey, $payload)) {
                $payload[$boolKey] = filter_var($payload[$boolKey], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $payload;
    }

    protected function isFilledValue(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        if (is_bool($value)) {
            return true;
        }

        return true;
    }
}
