<?php

namespace App\Support\Platform;

use App\Models\Platform\FormFieldSetting;
use App\Models\Platform\FormSetting;
use Illuminate\Support\Collection;

class FormSettingsManager
{
    public function __construct(
        protected FormSettingsService $forms,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function rows(int $companyId, ?int $branchId): Collection
    {
        $this->ensureForms($companyId, $branchId);

        return collect(config('form_registry.forms', []))
            ->map(function (array $meta, string $formKey) use ($companyId, $branchId) {
                $formSetting = $this->findFormSetting($companyId, $branchId, $formKey);
                $companyForm = $branchId ? $this->findFormSetting($companyId, null, $formKey) : null;

                $registryKeys = array_keys($meta['fields']);
                $registryFields = collect($meta['fields'])
                    ->map(fn (array $fieldMeta, string $fieldKey) => $this->forms->fieldConfig(
                        $formKey,
                        $fieldKey,
                        $companyId,
                        $branchId,
                        true,
                    ));

                $customFields = collect();
                if ($formSetting) {
                    $customFields = $formSetting->fields
                        ->whereNotIn('field_key', $registryKeys)
                        ->map(fn (FormFieldSetting $field) => $this->forms->fieldConfig(
                            $formKey,
                            $field->field_key,
                            $companyId,
                            $branchId,
                            true,
                        ));
                }

                return [
                    'form_key' => $formKey,
                    'label' => $meta['label'],
                    'description' => $meta['description'],
                    'is_active' => (bool) ($formSetting?->is_active ?? true),
                    'inherits_company' => $branchId && ! $formSetting && $companyForm,
                    'fields' => $registryFields
                        ->concat($customFields)
                        ->sortBy('sort_order')
                        ->values()
                        ->all(),
                ];
            })
            ->values();
    }

    /**
     * @param  array<string, array<string, mixed>>  $payload
     */
    public function save(int $companyId, ?int $branchId, array $payload): void
    {
        foreach (config('form_registry.forms', []) as $formKey => $meta) {
            if (! isset($payload[$formKey])) {
                continue;
            }

            $input = $payload[$formKey];

            $formSetting = FormSetting::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'form_key' => $formKey,
                ],
                [
                    'is_active' => filter_var($input['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ],
            );

            $registryKeys = array_keys($meta['fields']);
            $submittedFields = $input['fields'] ?? [];

            foreach ($submittedFields as $fieldKey => $fieldInput) {
                $fieldMeta = $meta['fields'][$fieldKey] ?? null;
                $isCustom = ! in_array($fieldKey, $registryKeys, true);

                if (! $fieldMeta && ! $isCustom) {
                    continue;
                }

                $this->persistFieldSetting(
                    $formSetting,
                    $fieldKey,
                    $fieldInput,
                    $fieldMeta,
                    $isCustom,
                );
            }

            if (! empty($input['remove_fields']) && is_array($input['remove_fields'])) {
                FormFieldSetting::query()
                    ->where('form_setting_id', $formSetting->id)
                    ->whereIn('field_key', $input['remove_fields'])
                    ->whereNotIn('field_key', $registryKeys)
                    ->delete();
            }

            if (! empty($input['add_field']['field_key'] ?? null)) {
                $newKey = $this->normalizeCustomFieldKey((string) $input['add_field']['field_key']);

                if ($newKey !== '' && ! in_array($newKey, $registryKeys, true)) {
                    $this->persistFieldSetting(
                        $formSetting,
                        $newKey,
                        array_merge(
                            ['visibility' => 'visible', 'requirement' => 'optional'],
                            $input['add_field'],
                        ),
                        null,
                        true,
                    );
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>|null  $fieldMeta
     * @param  array<string, mixed>  $fieldInput
     */
    protected function persistFieldSetting(
        FormSetting $formSetting,
        string $fieldKey,
        array $fieldInput,
        ?array $fieldMeta,
        bool $isCustom,
    ): void {
        $visibility = $fieldInput['visibility'] ?? 'visible';
        $requirement = $fieldInput['requirement'] ?? 'optional';
        $readOnly = filter_var($fieldInput['read_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $defaultData = filled($fieldInput['default_value'] ?? null)
            ? $fieldInput['default_value']
            : null;

        $defaultValue = [
            'data' => $defaultData,
            'read_only' => $readOnly,
        ];

        if ($isCustom) {
            $defaultValue['custom'] = true;
            $defaultValue['label'] = $fieldInput['label'] ?? $fieldKey;
            $defaultValue['type'] = $fieldInput['type'] ?? 'text';
        }

        FormFieldSetting::query()->updateOrCreate(
            [
                'form_setting_id' => $formSetting->id,
                'field_key' => $fieldKey,
            ],
            [
                'is_required' => $requirement === 'required',
                'is_visible' => $visibility !== 'hidden',
                'is_hidden' => $visibility === 'hidden',
                'default_value' => $defaultValue,
                'sort_order' => (int) ($fieldMeta['sort_order'] ?? ($fieldInput['sort_order'] ?? 500)),
            ],
        );
    }

    protected function normalizeCustomFieldKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = preg_replace('/[^a-z0-9_]+/', '_', $key) ?? '';

        return trim($key, '_');
    }

    public function ensureForms(int $companyId, ?int $branchId): void
    {
        foreach (config('form_registry.forms', []) as $formKey => $meta) {
            $formSetting = FormSetting::query()->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'form_key' => $formKey,
                ],
                ['is_active' => true],
            );

            foreach ($meta['fields'] as $fieldKey => $fieldMeta) {
                FormFieldSetting::query()->firstOrCreate(
                    [
                        'form_setting_id' => $formSetting->id,
                        'field_key' => $fieldKey,
                    ],
                    [
                        'is_required' => (bool) ($fieldMeta['required'] ?? false),
                        'is_visible' => (bool) ($fieldMeta['visible'] ?? true),
                        'is_hidden' => ! ($fieldMeta['visible'] ?? true),
                        'default_value' => isset($fieldMeta['default'])
                            ? ['data' => $fieldMeta['default'], 'read_only' => false]
                            : null,
                        'sort_order' => (int) ($fieldMeta['sort_order'] ?? 0),
                    ],
                );
            }
        }
    }

    protected function findFormSetting(int $companyId, ?int $branchId, string $formKey): ?FormSetting
    {
        return FormSetting::query()
            ->where('company_id', $companyId)
            ->where('form_key', $formKey)
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->with('fields')
            ->first();
    }
}
