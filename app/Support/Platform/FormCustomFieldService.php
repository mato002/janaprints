<?php

namespace App\Support\Platform;

use App\Models\Platform\FormCustomFieldValue;
use Illuminate\Database\Eloquent\Model;

class FormCustomFieldService
{
    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    /**
     * @return array<string, string|null>
     */
    public function valuesFor(Model $entity, string $formKey): array
    {
        return FormCustomFieldValue::query()
            ->where('entity_type', $entity->getMorphClass())
            ->where('entity_id', $entity->getKey())
            ->where('form_key', $formKey)
            ->pluck('value', 'field_key')
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    public function partition(
        string $formKey,
        array $data,
        ?int $companyId = null,
        ?int $branchId = null,
    ): array {
        $customKeys = $this->customFieldKeys($formKey, $companyId, $branchId);
        $custom = [];

        foreach ($customKeys as $fieldKey) {
            if (array_key_exists($fieldKey, $data)) {
                $custom[$fieldKey] = $data[$fieldKey];
            }
        }

        return [array_diff_key($data, $custom), $custom];
    }

    /**
     * @param  array<string, mixed>  $customData
     */
    public function sync(
        Model $entity,
        string $formKey,
        array $customData,
        ?int $companyId = null,
    ): void {
        $companyId ??= $entity->getAttribute('company_id') ?? tenant()->companyId();

        if ($companyId === null) {
            return;
        }

        $allowedKeys = $this->customFieldKeys($formKey, $companyId, $entity->getAttribute('branch_id'));

        foreach ($allowedKeys as $fieldKey) {
            if (! array_key_exists($fieldKey, $customData)) {
                continue;
            }

            $value = $customData[$fieldKey];

            if ($value === null || $value === '') {
                FormCustomFieldValue::query()
                    ->where('entity_type', $entity->getMorphClass())
                    ->where('entity_id', $entity->getKey())
                    ->where('field_key', $fieldKey)
                    ->delete();

                continue;
            }

            FormCustomFieldValue::query()->updateOrCreate(
                [
                    'entity_type' => $entity->getMorphClass(),
                    'entity_id' => $entity->getKey(),
                    'field_key' => $fieldKey,
                ],
                [
                    'company_id' => $companyId,
                    'form_key' => $formKey,
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                ],
            );
        }
    }

    /**
     * @return list<string>
     */
    public function customFieldKeys(string $formKey, ?int $companyId = null, ?int $branchId = null): array
    {
        return collect($this->formSettings->resolvedFields($formKey, $companyId, $branchId))
            ->filter(fn (array $config) => $config['is_custom'])
            ->keys()
            ->values()
            ->all();
    }
}
