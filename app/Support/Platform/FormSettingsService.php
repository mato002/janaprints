<?php

namespace App\Support\Platform;

use App\Models\Platform\FormFieldSetting;
use App\Models\Platform\FormSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FormSettingsService
{
    /**
     * @return array<string, mixed>
     */
    protected function registryForm(string $formKey): array
    {
        return config('form_registry.forms')[$formKey] ?? [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function registryFields(string $formKey): array
    {
        return $this->registryForm($formKey)['fields'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function registryField(string $formKey, string $fieldKey): array
    {
        return $this->registryFields($formKey)[$fieldKey] ?? [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function resolvedFields(
        string $formKey,
        ?int $companyId = null,
        ?int $branchId = null,
        ?Model $entity = null,
    ): array {
        $registryFields = $this->registryFields($formKey);

        $fields = collect($registryFields)
            ->mapWithKeys(fn (array $meta, string $fieldKey) => [
                $fieldKey => $this->fieldConfig($formKey, $fieldKey, $companyId, $branchId),
            ])
            ->merge($this->customFieldConfigs($formKey, $companyId, $branchId))
            ->sortBy('sort_order')
            ->all();

        if ($entity === null) {
            return $fields;
        }

        $values = app(FormCustomFieldService::class)->valuesFor($entity, $formKey);

        foreach ($fields as $fieldKey => $config) {
            if ($config['is_custom'] && array_key_exists($fieldKey, $values)) {
                $fields[$fieldKey]['default'] = $values[$fieldKey];
            }
        }

        return $fields;
    }

    /**
     * @return Collection<string, array<string, mixed>>
     */
    protected function customFieldConfigs(string $formKey, ?int $companyId, ?int $branchId): Collection
    {
        $companyId ??= tenant()->companyId();
        $branchId ??= tenant()->branchId();
        $registryKeys = array_keys($this->registryFields($formKey));

        $formSetting = $this->resolveFormSetting($formKey, $companyId, $branchId);

        if (! $formSetting) {
            return collect();
        }

        return $formSetting->fields
            ->whereNotIn('field_key', $registryKeys)
            ->mapWithKeys(fn (FormFieldSetting $field) => [
                $field->field_key => $this->fieldConfig($formKey, $field->field_key, $companyId, $branchId),
            ]);
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

        $registry = $this->registryField($formKey, $fieldKey);
        $isCustom = $registry === [];

        $branchForm = $branchId !== null
            ? $this->resolveFormSettingAtScope($formKey, $companyId, $branchId, $forConfiguration)
            : null;
        $companyForm = $this->resolveFormSettingAtScope($formKey, $companyId, null, $forConfiguration);

        $branchField = $branchForm?->fields->firstWhere('field_key', $fieldKey);
        $companyField = $companyForm?->fields->firstWhere('field_key', $fieldKey);

        $inheritsCompany = false;

        if ($branchField) {
            if (
                $companyField
                && ! $isCustom
                && $branchForm
                && $this->branchFormIsRegistryPlaceholder($branchForm, $formKey)
                && $this->branchFieldMatchesRegistry($branchField, $registry)
                && ! $this->branchFieldMatchesRegistry($companyField, $registry)
            ) {
                $source = $companyField;
                $inheritsCompany = true;
            } else {
                $source = $branchField;
            }
        } elseif ($companyField) {
            $source = $companyField;
            $inheritsCompany = $branchId !== null && $branchForm === null;
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
                'registry_required' => false,
                'is_custom' => true,
            ];
        } else {
            $required = (bool) ($registry['required'] ?? false);
            $visible = $required || (bool) ($registry['visible'] ?? true);

            return [
                'field_key' => $fieldKey,
                'label' => $registry['label'] ?? $fieldKey,
                'type' => $registry['type'] ?? 'text',
                'required' => $required,
                'visible' => $visible,
                'hidden' => ! $visible,
                'read_only' => (bool) ($registry['read_only'] ?? false),
                'default' => $registry['default'] ?? null,
                'sort_order' => (int) ($registry['sort_order'] ?? 0),
                'inherits_company' => false,
                'registry_required' => $required,
                'is_custom' => false,
            ];
        }

        $meta = $source->default_value ?? [];
        $registryRequired = ! $isCustom && (bool) ($registry['required'] ?? false);
        $required = $registryRequired || (bool) $source->is_required;
        $visible = $required || ($source->is_visible && ! $source->is_hidden);

        return [
            'field_key' => $fieldKey,
            'label' => $registry['label'] ?? ($meta['label'] ?? $fieldKey),
            'type' => $registry['type'] ?? ($meta['type'] ?? 'text'),
            'required' => $required,
            'visible' => $visible,
            'hidden' => ! $visible,
            'read_only' => (bool) ($meta['read_only'] ?? false),
            'default' => $meta['data'] ?? ($registry['default'] ?? null),
            'sort_order' => (int) ($source->sort_order ?: ($registry['sort_order'] ?? 0)),
            'inherits_company' => $inheritsCompany,
            'registry_required' => $registryRequired,
            'is_custom' => $isCustom,
        ];
    }

    /**
     * @param  array<string, mixed>  $registry
     */
    protected function branchFieldMatchesRegistry(FormFieldSetting $branchField, array $registry): bool
    {
        if ($registry === []) {
            return false;
        }

        $registryRequired = (bool) ($registry['required'] ?? false);
        $registryVisible = (bool) ($registry['visible'] ?? true);

        return (bool) $branchField->is_required === $registryRequired
            && (bool) $branchField->is_visible === $registryVisible
            && (bool) $branchField->is_hidden === ! $registryVisible;
    }

    protected function branchFormIsRegistryPlaceholder(FormSetting $branchForm, string $formKey): bool
    {
        $registryFields = $this->registryFields($formKey);

        if ($registryFields === [] || $branchForm->fields->count() < count($registryFields)) {
            return false;
        }

        foreach ($registryFields as $fieldKey => $registry) {
            $field = $branchForm->fields->firstWhere('field_key', $fieldKey);

            if (! $field || ! $this->branchFieldMatchesRegistry($field, $registry)) {
                return false;
            }
        }

        return true;
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

        foreach ($this->resolvedFields($formKey, $companyId, $branchId) as $fieldKey => $config) {
            if ($config['is_custom'] && ! array_key_exists($fieldKey, $baseRules)) {
                $baseRules[$fieldKey] = $this->rulesForFieldType($config['type']);
            }
        }

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

    /**
     * @return list<string|object>
     */
    protected function rulesForFieldType(string $type): array
    {
        return match ($type) {
            'email' => ['string', 'email', 'max:255'],
            'number' => ['numeric'],
            'date' => ['date'],
            'checkbox' => ['boolean'],
            'textarea' => ['string'],
            default => ['string', 'max:500'],
        };
    }
}
