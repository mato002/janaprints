<?php

namespace App\Support\Platform;

use App\Models\Platform\FormStatusOption;
use BackedEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use UnitEnum;

class FormStatusOptionService
{
    /**
     * @return list<string>
     */
    public function formsWithConfigurableStatus(): array
    {
        return array_keys(config('form_status_registry.defaults', []));
    }

    public function formHasConfigurableStatus(string $formKey): bool
    {
        return array_key_exists($formKey, config('form_status_registry.defaults', []));
    }

    public function isStatusField(string $fieldKey, array $fieldConfig): bool
    {
        if (($fieldConfig['type'] ?? 'text') !== 'select') {
            return false;
        }

        if ($fieldKey === 'status') {
            return true;
        }

        $label = Str::lower(trim((string) ($fieldConfig['label'] ?? '')));

        return $label === 'status';
    }

    /**
     * @return Collection<int, FormStatusOption>
     */
    public function optionsFor(string $formKey, ?int $companyId = null, ?int $branchId = null, bool $activeOnly = true): Collection
    {
        $companyId ??= tenant()->companyId() ?? auth()->user()?->company_id;

        if ($companyId === null) {
            return collect();
        }

        $this->ensureDefaults($formKey, $companyId, null);

        if ($branchId !== null) {
            $branchOptions = $this->scopedQuery($companyId, $branchId, $formKey, $activeOnly)->get();

            if ($branchOptions->isNotEmpty()) {
                return $branchOptions;
            }
        }

        return $this->scopedQuery($companyId, null, $formKey, $activeOnly)->get();
    }

    /**
     * @return array<int, string|Rule>
     */
    public function validationRules(
        string $formKey,
        ?int $companyId = null,
        ?int $branchId = null,
        bool $required = true,
    ): array {
        $rules = $required ? ['required'] : ['nullable'];

        $allowed = $this->optionsFor($formKey, $companyId, $branchId)
            ->pluck('value')
            ->all();

        if ($allowed !== []) {
            $rules[] = Rule::in($allowed);
        } else {
            $rules[] = 'string';
            $rules[] = 'max:60';
        }

        return $rules;
    }

    public static function valueOf(mixed $status): ?string
    {
        if ($status instanceof BackedEnum) {
            return $status->value;
        }

        if (is_string($status) && $status !== '') {
            return $status;
        }

        return null;
    }

    public function labelFor(mixed $status, string $formKey, ?int $companyId = null, ?int $branchId = null): string
    {
        $value = self::valueOf($status);

        if ($value === null) {
            return '—';
        }

        $option = $this->optionsFor($formKey, $companyId, $branchId, false)
            ->firstWhere('value', $value);

        if ($option) {
            return $option->label;
        }

        if ($status instanceof UnitEnum) {
            if (method_exists($status, 'label')) {
                return (string) $status->label();
            }

            return $status->name;
        }

        return Str::headline($value);
    }

    public function ensureDefaults(string $formKey, int $companyId, ?int $branchId = null): void
    {
        if (! $this->formHasConfigurableStatus($formKey) || $branchId !== null) {
            return;
        }

        if ($this->scopedQuery($companyId, null, $formKey, false)->exists()) {
            return;
        }

        $enumClass = config("form_status_registry.defaults.{$formKey}");

        if (! is_string($enumClass) || ! enum_exists($enumClass)) {
            return;
        }

        foreach ($enumClass::cases() as $index => $case) {
            FormStatusOption::query()->create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'form_key' => $formKey,
                'value' => $case->value,
                'label' => $this->defaultLabelForEnumCase($case),
                'sort_order' => $index,
                'is_active' => true,
                'is_system' => true,
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $submitted
     */
    public function syncOptions(string $formKey, int $companyId, ?int $branchId, array $submitted): void
    {
        if (! $this->formHasConfigurableStatus($formKey)) {
            return;
        }

        if ($branchId === null) {
            $this->ensureDefaults($formKey, $companyId, null);
        }

        $existing = $this->scopedQuery($companyId, $branchId, $formKey, false)
            ->get()
            ->keyBy('value');

        foreach ($submitted as $index => $row) {
            $value = $this->normalizeValue((string) ($row['value'] ?? ''));
            $label = trim((string) ($row['label'] ?? ''));

            if ($value === '' || $label === '') {
                continue;
            }

            $remove = filter_var($row['remove'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isActive = filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $current = $existing->get($value);

            if ($current?->is_system) {
                FormStatusOption::query()
                    ->whereKey($current->id)
                    ->update([
                        'label' => $label,
                        'sort_order' => (int) $index,
                        'is_active' => $isActive,
                    ]);

                continue;
            }

            if ($remove) {
                if ($current) {
                    $current->delete();
                }

                continue;
            }

            FormStatusOption::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'form_key' => $formKey,
                    'value' => $value,
                ],
                [
                    'label' => $label,
                    'sort_order' => (int) $index,
                    'is_active' => $isActive,
                    'is_system' => false,
                ],
            );
        }
    }

    protected function scopedQuery(int $companyId, ?int $branchId, string $formKey, bool $activeOnly)
    {
        return FormStatusOption::query()
            ->where('company_id', $companyId)
            ->where('form_key', $formKey)
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('label');
    }

    public function createOption(
        string $formKey,
        int $companyId,
        ?int $branchId,
        string $value,
        string $label,
    ): FormStatusOption {
        if (! $this->formHasConfigurableStatus($formKey)) {
            throw new \InvalidArgumentException("Form [{$formKey}] does not support configurable statuses.");
        }

        $this->ensureDefaults($formKey, $companyId, null);

        $value = $this->normalizeValue($value);
        $label = trim($label);

        $nextSort = (int) ($this->scopedQuery($companyId, $branchId, $formKey, false)->max('sort_order') ?? 0) + 1;

        return FormStatusOption::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'form_key' => $formKey,
            'value' => $value,
            'label' => $label,
            'sort_order' => $nextSort,
            'is_active' => true,
            'is_system' => false,
        ]);
    }

    public function normalizeValue(string $value): string
    {
        $value = Str::lower(trim($value));
        $value = preg_replace('/[^a-z0-9_]+/', '_', $value) ?? '';

        return trim($value, '_');
    }

    protected function defaultLabelForEnumCase(UnitEnum $case): string
    {
        if (method_exists($case, 'label')) {
            return (string) $case->label();
        }

        if ($case instanceof BackedEnum) {
            return Str::headline($case->value);
        }

        return $case->name;
    }
}
