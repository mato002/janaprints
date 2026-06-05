@php
    $workflow = old('approval_workflow', $documentType->workflow_json['approval_workflow'] ?? '') ?? '';
    $notificationWorkflow = old('notification_workflow', $documentType->workflow_json['notification_workflow'] ?? '') ?? '';
    $auditTracking = old('audit_tracking', $documentType->workflow_json['audit_tracking'] ?? true);
    $archivalRules = old('archival_rules', $documentType->workflow_json['archival_rules'] ?? '') ?? '';
    $printTemplate = old('print_template', $documentType->workflow_json['print_template'] ?? '') ?? '';
    $isSystem = $documentType->is_system ?? false;
@endphp

<div class="grid gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Document Configuration') }}</h3>

        <div>
            <label class="erp-label" for="code">{{ __('Document Code') }}</label>
            <input
                type="text"
                id="code"
                name="code"
                value="{{ old('code', $documentType->code ?? '') }}"
                class="erp-input w-full"
                @disabled($isSystem)
                required
            >
            @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="erp-label" for="name">{{ __('Document Name') }}</label>
            <input type="text" id="name" name="name" value="{{ old('name', $documentType->name ?? '') }}" class="erp-input w-full" required>
        </div>

        <div>
            <label class="erp-label" for="module">{{ __('Module') }}</label>
            <select id="module" name="module" class="erp-input w-full" required>
                @foreach ($modules as $value => $label)
                    <option value="{{ $value }}" @selected(old('module', $documentType->module?->value ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="erp-label" for="prefix">{{ __('Prefix') }}</label>
            <input type="text" id="prefix" name="prefix" value="{{ old('prefix', $documentType->prefix ?? '') }}" class="erp-input w-full" maxlength="20">
        </div>

        <div>
            <label class="erp-label" for="number_series_key">{{ __('Number Series') }}</label>
            <select id="number_series_key" name="number_series_key" class="erp-input w-full">
                <option value="">{{ __('Not linked') }}</option>
                @foreach ($numberSeriesOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('number_series_key', $documentType->number_series_key ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="auto_numbering" value="0">
            <input type="checkbox" id="auto_numbering" name="auto_numbering" value="1" class="rounded border-erp-border text-erp-accent" @checked(old('auto_numbering', $documentType->auto_numbering ?? true))>
            <label for="auto_numbering" class="text-sm text-slate-700">{{ __('Auto Numbering') }}</label>
        </div>
    </div>

    <div class="space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Governance Settings') }}</h3>

        <div class="flex items-center gap-2">
            <input type="hidden" name="approval_required" value="0">
            <input type="checkbox" id="approval_required" name="approval_required" value="1" class="rounded border-erp-border text-erp-accent" @checked(old('approval_required', $documentType->approval_required ?? false))>
            <label for="approval_required" class="text-sm text-slate-700">{{ __('Approval Required') }}</label>
        </div>

        <div>
            <label class="erp-label" for="approval_levels">{{ __('Approval Levels') }}</label>
            <input type="number" id="approval_levels" name="approval_levels" value="{{ old('approval_levels', $documentType->approval_levels ?? 0) }}" min="0" max="10" class="erp-input w-24">
        </div>

        <div>
            <label class="erp-label" for="approval_rule_type">{{ __('Approval Rule Link') }}</label>
            <select id="approval_rule_type" name="approval_rule_type" class="erp-input w-full">
                <option value="">{{ __('None') }}</option>
                @foreach ($approvalRuleOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('approval_rule_type', $documentType->approval_rule_type ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="erp-label" for="retention_period_days">{{ __('Retention Period (days)') }}</label>
            <input type="number" id="retention_period_days" name="retention_period_days" value="{{ old('retention_period_days', $documentType->retention_period_days ?? '') }}" min="1" class="erp-input w-full">
        </div>

        <div>
            <label class="erp-label" for="form_key">{{ __('Form Controls Link') }}</label>
            <select id="form_key" name="form_key" class="erp-input w-full">
                <option value="">{{ __('None') }}</option>
                @foreach ($formOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('form_key', $documentType->form_key ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="mt-8 space-y-4">
    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Workflow Settings') }}</h3>

    <div class="grid gap-4 lg:grid-cols-2">
        <div>
            <label class="erp-label" for="approval_workflow">{{ __('Approval Workflow') }}</label>
            <input type="text" id="approval_workflow" name="approval_workflow" value="{{ $workflow }}" class="erp-input w-full">
        </div>
        <div>
            <label class="erp-label" for="notification_workflow">{{ __('Notification Workflow') }}</label>
            <input type="text" id="notification_workflow" name="notification_workflow" value="{{ $notificationWorkflow }}" class="erp-input w-full">
        </div>
        <div>
            <label class="erp-label" for="archival_rules">{{ __('Archival Rules') }}</label>
            <input type="text" id="archival_rules" name="archival_rules" value="{{ $archivalRules }}" class="erp-input w-full">
        </div>
        <div>
            <label class="erp-label" for="print_template">{{ __('Print Template') }}</label>
            <input type="text" id="print_template" name="print_template" value="{{ $printTemplate }}" class="erp-input w-full">
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="audit_tracking" value="0">
        <input type="checkbox" id="audit_tracking" name="audit_tracking" value="1" class="rounded border-erp-border text-erp-accent" @checked($auditTracking)>
        <label for="audit_tracking" class="text-sm text-slate-700">{{ __('Audit Tracking') }}</label>
    </div>
</div>
