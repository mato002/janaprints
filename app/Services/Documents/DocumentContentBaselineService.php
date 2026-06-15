<?php

namespace App\Services\Documents;

use Illuminate\Support\Facades\Schema;

class DocumentContentBaselineService
{
    public function __construct(
        protected DocumentSettingsService $settings,
    ) {}

    /**
     * @return array{settings_created: int}
     */
    public function seed(?int $companyId = null): array
    {
        $companyId ??= tenant()->companyId();

        if (! Schema::hasTable('document_settings') || $companyId === null) {
            return ['settings_created' => 0];
        }

        $before = \App\Models\DocumentSetting::query()
            ->where('company_id', $companyId)
            ->count();

        $this->settings->syncRegistryForCompany($companyId);

        $after = \App\Models\DocumentSetting::query()
            ->where('company_id', $companyId)
            ->count();

        return ['settings_created' => max(0, $after - $before)];
    }
}
