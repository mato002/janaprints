<?php

namespace App\Support\Crm;

use App\Support\Platform\SystemSettingsService;

class CrmSettings
{
    public function autoConvertLeadOnQuote(?int $companyId = null, ?int $branchId = null): bool
    {
        $definition = config('settings_registry.sections.crm.settings.auto_convert_lead_on_quote', []);

        return (bool) app(SystemSettingsService::class)->get(
            'auto_convert_lead_on_quote',
            $definition['default'] ?? true,
            $companyId ?? tenant()->companyId(),
            $branchId,
        );
    }

    public function publicQuoteAutoCreateLead(?int $companyId = null): bool
    {
        $definition = config('settings_registry.sections.crm.settings.public_quote_auto_create_lead', []);

        return (bool) app(SystemSettingsService::class)->get(
            'public_quote_auto_create_lead',
            $definition['default'] ?? config('leads.crm.auto_create_lead', true),
            $companyId ?? tenant()->companyId(),
        );
    }

    public function publicQuoteAutoDraftQuotation(?int $companyId = null): bool
    {
        $definition = config('settings_registry.sections.crm.settings.public_quote_auto_draft_quotation', []);

        return (bool) app(SystemSettingsService::class)->get(
            'public_quote_auto_draft_quotation',
            $definition['default'] ?? config('leads.crm.auto_draft_quotation', false),
            $companyId ?? tenant()->companyId(),
        );
    }

    public function publicQuoteDefaultAssigneeId(?int $companyId = null): ?int
    {
        $definition = config('settings_registry.sections.crm.settings.public_quote_default_assignee_id', []);
        $value = app(SystemSettingsService::class)->get(
            'public_quote_default_assignee_id',
            $definition['default'] ?? null,
            $companyId ?? tenant()->companyId(),
        );

        return $value ? (int) $value : null;
    }
}
