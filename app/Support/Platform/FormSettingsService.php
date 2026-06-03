<?php

namespace App\Support\Platform;

use App\Models\Platform\FormFieldSetting;
use App\Models\Platform\FormSetting;
use Illuminate\Support\Collection;

class FormSettingsService
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function resolvedFields(string $formKey, ?int $companyId = null, ?int $branchId = null): array
    {
        $fields = config("form_registry.forms.{$formKey}.fields", []);

        return collect($fields)
            ->mapWithKeys(fn (array $meta, string $fieldKey) => [
                $fieldKey => $this->fieldConfig($formKey, $fieldKey, $companyId, $branchId),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function fieldConfig(
        string $formKey,
        string $fieldKey,
        ?int $companyId = null,
        ?int $branchId = null,
        bool $forConfiguration = false,
    ): array {
        $companyId ??= tenant()->companyId();
        $branchId ??= tenant()->branchId();

        $registry = config("form_registry.forms.{$formKey}.fields.{$fieldKey}", []);
        $isCustom = $registry === [];

        $branchForm = $branchId !== null
            ? $this->resolveFormSettingAtScope($formKey, $companyId, $branchId, $forConfiguration)
            : null;
        $companyForm = $this->resolveFormSettingAtScope($formKey, $companyId, null, $forConfiguration);

        $branchField = $branchForm?->fields->firstWhere('field_key', $fieldKey);
        $companyField = $companyForm?->fields->firstWhere('field_key', $fieldKey);

        $inheritsCompany = $branchId !== null && $branchForm === null && $companyField !== null;

        if ($branchField) {
            $source = $branchField;
        } elseif ($companyField) {
            $source = $companyField;
        } elseif ($isCustom) {
            return [
                'field_key' => $fieldKey,
                'label' => $fieldKey,
                'type' => 'text',
                'required' => false,
                'visible' => true,
                'hidden' => false,
                'read_only' => false,
                'default' => null,
                'sort_order' => 999,
                'inherits_company' => false,
                'is_custom' => true,
            ];
        } else {
            return [
                'field_key' => $fieldKey,
                'label' => $registry['label'] ?? $fieldKey,
                'type' => $registry['type'] ?? 'text',
                'required' => (bool) ($registry['required'] ?? false),
                'visible' => (bool) ($registry['visible'] ?? true),
                'hidden' => ! ($registry['visible'] ?? true),
                'read_only' => (bool) ($registry['read_only'] ?? false),
                'default' => $registry['default'] ?? null,
                'sort_order' => (int) ($registry['sort_order'] ?? 0),
                'inherits_company' => false,
                'is_custom' => false,
            ];
        }

        $meta = $source->default_value ?? [];

        return [
            'field_key' => $fieldKey,
            'label' => $registry['label'] ?? ($meta['label'] ?? $fieldKey),
            'type' => $registry['type'] ?? ($meta['type'] ?? 'text'),
            'required' => (bool) $source->is_required,
            'visible' => $source->is_visible && ! $source->is_hidden,
            'hidden' => (bool) $source->is_hidden,
            'read_only' => (bool) ($meta['read_only'] ?? false),
            'default' => $meta['data'] ?? ($registry['default'] ?? null),
            'sort_order' => (int) ($source->sort_order ?: ($registry['sort_order'] ?? 0)),
            'inherits_company' => $inheritsCompany,
            'is_custom' => $isCustom || (bool) ($meta['custom'] ?? false),
        ];
    }

    /**
     * @return Collection<int, FormFieldSetting>
     */
    public function fields(string $formKey, ?int $companyId = null, ?int $branchId = null): Collection
    {
        $companyId ??= tenant()->companyId();
        $branchId ??= tenant()->branchId();

        $formSetting = $this->resolveFormSetting($formKey, $companyId, $branchId);

        if (! $formSetting) {
            return collect();
        }

        return $formSetting->fields;
    }

    public function isRequired(string $formKey, string $fieldKey, ?int $companyId = null, ?int $branchId = null): bool
    {
        return $this->fieldConfig($formKey, $fieldKey, $companyId, $branchId)['required'];
    }

    public function isVisible(string $formKey, string $fieldKey, ?int $companyId = null, ?int $branchId = null): bool
    {
        return $this->fieldConfig($formKey, $fieldKey, $companyId, $branchId)['visible'];
    }

    public function isReadOnly(string $formKey, string $fieldKey, ?int $companyId = null, ?int $branchId = null): bool
    {
        return $this->fieldConfig($formKey, $fieldKey, $companyId, $branchId)['read_only'];
    }

    public function defaultValue(string $formKey, string $fieldKey, mixed $fallback = null, ?int $companyId = null, ?int $branchId = null): mixed
    {
        $default = $this->fieldConfig($formKey, $fieldKey, $companyId, $branchId)['default'];

        return $default ?? $fallback;
    }

    /**
     * @param  array<string, array<int, mixed>>  $baseRules
     * @return array<string, array<int, mixed>>
     */
    public function mergeValidationRules(
        string $formKey,
        array $baseRules,
        ?int $companyId = null,
        ?int $branchId = null,
    ): array {
        $companyId ??= tenant()->companyId();
        $branchId ??= tenant()->branchId();

        foreach ($baseRules as $fieldKey => $rules) {
            $config = $this->fieldConfig($formKey, $fieldKey, $companyId, $branchId);

            if ($config['hidden'] || ! $config['visible']) {
                $baseRules[$fieldKey] = ['prohibited'];

                continue;
            }

            $rules = array_values(array_filter(
                (array) $rules,
                fn ($rule) => ! in_array($rule, ['required', 'nullable'], true)
                    && ! (is_string($rule) && str_starts_with($rule, 'required:')),
            ));

            if ($config['required']) {
                array_unshift($rules, 'required');
            } else {
                array_unshift($rules, 'nullable');
            }

            $baseRules[$fieldKey] = $rules;
        }

        return $baseRules;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function applyDefaults(
        string $formKey,
        array $data,
        ?int $companyId = null,
        ?int $branchId = null,
    ): array {
        foreach ($this->resolvedFields($formKey, $companyId, $branchId) as $fieldKey => $config) {
            if (! $config['visible']) {
                continue;
            }

            if (! array_key_exists($fieldKey, $data) || $data[$fieldKey] === null || $data[$fieldKey] === '') {
                if ($config['default'] !== null && $config['default'] !== '') {
                    $data[$fieldKey] = $config['default'];
                }
            }
        }

        return $data;
    }

    protected function resolveDbField(
        string $formKey,
        string $fieldKey,
        ?int $companyId,
        ?int $branchId,
    ): ?FormFieldSetting {
        $formSetting = $this->resolveFormSetting($formKey, $companyId, $branchId);

        if (! $formSetting) {
            return null;
        }

        return $formSetting->fields->firstWhere('field_key', $fieldKey);
    }

    protected function field(string $formKey, string $fieldKey, ?int $companyId, ?int $branchId): ?FormFieldSetting
    {
        return $this->resolveDbField($formKey, $fieldKey, $companyId, $branchId);
    }

    protected function resolveFormSetting(string $formKey, ?int $companyId, ?int $branchId): ?FormSetting
    {
        if ($branchId !== null) {
            $branchForm = $this->resolveFormSettingAtScope($formKey, $companyId, $branchId);

            if ($branchForm) {
                return $branchForm;
            }
        }

        return $this->resolveFormSettingAtScope($formKey, $companyId, null);
    }

    protected function resolveFormSettingAtScope(
        string $formKey,
        ?int $companyId,
        ?int $branchId,
        bool $forConfiguration = false,
    ): ?FormSetting {
        return FormSetting::query()
            ->where('form_key', $formKey)
            ->where('company_id', $companyId)
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->when(! $forConfiguration, fn ($query) => $query->where('is_active', true))
            ->with('fields')
            ->first();
    }
}
