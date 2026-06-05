@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.workspaces.administration.section', ['section' => 'workflow-governance']);
    $isEdit = $isEdit ?? false;
@endphp

<x-admin-layout
    :title="$isEdit ? __('Edit Escalation Rule') : __('Create Escalation Rule')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Workflow & Governance'), 'url' => $hubBackUrl],
        ['label' => __('Escalations'), 'url' => route('admin.governance.escalations.index', $scopeQuery)],
        ['label' => $isEdit ? __('Edit') : __('Create')],
    ]"
>
    @include('admin.settings.partials.hub-toolbar', [
        'title' => $isEdit ? __('Edit Escalation Rule') : __('Create Escalation Rule'),
        'description' => __('Configure waiting periods and escalation routing for approval workflows.'),
        'backUrl' => route('admin.governance.escalations.index', $scopeQuery),
    ])

    <x-admin.card>
        <form
            method="POST"
            action="{{ $isEdit ? route('admin.governance.escalations.update', ['escalation' => $rule->id]) : route('admin.governance.escalations.store') }}"
            class="space-y-6"
        >
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <input type="hidden" name="company_id" value="{{ $companyId }}">
            @if ($branchId)
                <input type="hidden" name="branch_id" value="{{ $branchId }}">
            @endif

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <label class="erp-label" for="name">{{ __('Rule Name') }}</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $rule->name ?? '') }}" class="erp-input w-full" required>
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="erp-label" for="workflow_key">{{ __('Workflow') }}</label>
                        <select id="workflow_key" name="workflow_key" class="erp-input w-full" required>
                            <option value="">{{ __('Select workflow') }}</option>
                            @foreach ($workflows as $value => $label)
                                <option value="{{ $value }}" @selected(old('workflow_key', $rule->workflow_key ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('workflow_key')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="erp-label" for="waiting_hours">{{ __('Waiting Period') }}</label>
                        <select id="waiting_hours" name="waiting_hours" class="erp-input w-full" required>
                            <option value="">{{ __('Select waiting period') }}</option>
                            @foreach ($waitingPeriods as $hours => $label)
                                <option value="{{ $hours }}" @selected((int) old('waiting_hours', $rule->waiting_hours ?? 0) === (int) $hours)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('waiting_hours')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="erp-label" for="escalation_target_role">{{ __('Escalation Target') }}</label>
                        <select id="escalation_target_role" name="escalation_target_role" class="erp-input w-full" required>
                            <option value="">{{ __('Select escalation target role') }}</option>
                            @foreach ($roles as $roleName)
                                <option value="{{ $roleName }}" @selected(old('escalation_target_role', $rule->escalation_target_role ?? '') === $roleName)>{{ $roleName }}</option>
                            @endforeach
                        </select>
                        @error('escalation_target_role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="erp-label">{{ __('Escalation Method') }}</label>
                        <div class="mt-2 space-y-2">
                            @foreach ($escalationMethods as $value => $label)
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        type="radio"
                                        name="escalation_method"
                                        value="{{ $value }}"
                                        class="border-erp-border text-erp-accent"
                                        @checked(old('escalation_method', $rule?->escalation_method?->value ?? 'reminder') === $value)
                                        required
                                    >
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-slate-500">
                            {{ __('Reminder sends a notification when the waiting period expires. Auto Escalate reassigns approval to the escalation target.') }}
                        </p>
                        @error('escalation_method')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="erp-label" for="description">{{ __('Description') }}</label>
                        <textarea id="description" name="description" rows="3" class="erp-input w-full">{{ old('description', $rule->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-erp-border pt-4">
                <button type="submit" class="erp-btn-primary">{{ $isEdit ? __('Save Changes') : __('Create Rule') }}</button>
                <a href="{{ route('admin.governance.escalations.index', $scopeQuery) }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
