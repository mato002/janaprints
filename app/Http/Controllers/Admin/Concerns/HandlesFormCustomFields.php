<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\Platform\FormCustomFieldService;
use Illuminate\Database\Eloquent\Model;

trait HandlesFormCustomFields
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    protected function partitionCustomFields(
        string $formKey,
        array $data,
        ?int $companyId = null,
        ?int $branchId = null,
    ): array {
        return app(FormCustomFieldService::class)->partition($formKey, $data, $companyId, $branchId);
    }

    /**
     * @param  array<string, mixed>  $customData
     */
    protected function syncCustomFields(
        Model $entity,
        string $formKey,
        array $customData,
        ?int $companyId = null,
    ): void {
        app(FormCustomFieldService::class)->sync($entity, $formKey, $customData, $companyId);
    }
}
