@php
    $isEdit = $chain !== null;
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $sectionUrl = route('admin.workspaces.administration.section', ['section' => 'workflow-governance']);
    $steps = old('steps', $chain?->steps->map(fn ($step) => [
        'approver_role' => $step->approver_role,
        'approver_user_id' => $step->approver_user_id,
        'approval_limit' => $step->approval_limit,
        'is_required' => $step->is_required,
        'min_amount' => $step->condition_json['min_amount'] ?? null,
        'max_amount' => $step->condition_json['max_amount'] ?? null,
        'min_percent' => $step->condition_json['min_percent'] ?? null,
        'max_percent' => $step->condition_json['max_percent'] ?? null,
    ])->all() ?? [['approver_role' => '', 'approver_user_id' => '', 'approval_limit' => '', 'is_required' => true]]);
@endphp

<x-admin-layout
    :title="$isEdit ? __('Edit Approval Chain') : __('New Approval Chain')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Workflow & Governance'), 'url' => $sectionUrl],
        ['label' => __('Approval Chains'), 'url' => route('admin.governance.chains.index', $scopeQuery)],
        ['label' => $isEdit ? $chain->name : __('Create')],
    ]"
>
    <x-admin.page-header
        :title="$isEdit ? __('Edit Approval Chain') : __('New Approval Chain')"
        :description="__('Configure chain metadata and ordered approval steps. Activation links this chain to its approval rule type.')"
    />

    <x-admin.card>
        <form
            method="POST"
            action="{{ $isEdit ? route('admin.governance.chains.update', $chain) : route('admin.governance.chains.store') }}"
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

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-input-label for="name" :value="__('Chain Name')" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $chain?->name)" required />
                </div>
                <div>
                    <x-input-label for="module" :value="__('Module')" />
                    <select id="module" name="module" class="erp-select mt-1 w-full" required>
                        @foreach ($modules as $value => $label)
                            <option value="{{ $value }}" @selected(old('module', $chain?->module) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="document_type" :value="__('Document Type')" />
                    <select id="document_type" name="document_type" class="erp-select mt-1 w-full">
                        <option value="">{{ __('Any') }}</option>
                        @foreach ($documentTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('document_type', $chain?->document_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="approval_rule_type" :value="__('Linked Approval Rule')" />
                    <select id="approval_rule_type" name="approval_rule_type" class="erp-select mt-1 w-full" required>
                        @foreach ($ruleTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('approval_rule_type', $chain?->approval_rule_type?->value) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="approval_mode" :value="__('Approval Mode')" />
                    <select id="approval_mode" name="approval_mode" class="erp-select mt-1 w-full" required>
                        @foreach ($modes as $value => $label)
                            <option value="{{ $value }}" @selected(old('approval_mode', $chain?->approval_mode?->value ?? 'sequential') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" class="erp-input mt-1 w-full" rows="2">{{ old('description', $chain?->description) }}</textarea>
                </div>
            </div>

            @if (! empty($jobTitleAuthorities))
                <div class="rounded-lg border border-erp-border bg-erp-page/40 p-4">
                    <h3 class="text-sm font-semibold text-erp-primary">{{ __('Job Title Approval Authorities') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Reference mapping from organizational job titles to default approver roles.') }}</p>
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        @foreach ($jobTitleAuthorities as $label)
                            <li>{{ $label }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-lg border border-erp-border bg-erp-page/40 p-4">
                <h3 class="text-sm font-semibold text-erp-primary">{{ __('Conditional Thresholds (optional)') }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ __('Used when approval mode is Conditional, or to pick among multiple chains for the same rule type.') }}</p>
                <div class="mt-3 grid gap-3 md:grid-cols-4">
                    <div>
                        <x-input-label for="min_amount" :value="__('Min Amount')" />
                        <x-text-input id="min_amount" name="min_amount" type="number" step="0.01" class="mt-1 block w-full" :value="old('min_amount', $chain?->condition_json['min_amount'] ?? '')" />
                    </div>
                    <div>
                        <x-input-label for="max_amount" :value="__('Max Amount')" />
                        <x-text-input id="max_amount" name="max_amount" type="number" step="0.01" class="mt-1 block w-full" :value="old('max_amount', $chain?->condition_json['max_amount'] ?? '')" />
                    </div>
                    <div>
                        <x-input-label for="min_percent" :value="__('Min Percent')" />
                        <x-text-input id="min_percent" name="min_percent" type="number" step="0.01" class="mt-1 block w-full" :value="old('min_percent', $chain?->condition_json['min_percent'] ?? '')" />
                    </div>
                    <div>
                        <x-input-label for="max_percent" :value="__('Max Percent')" />
                        <x-text-input id="max_percent" name="max_percent" type="number" step="0.01" class="mt-1 block w-full" :value="old('max_percent', $chain?->condition_json['max_percent'] ?? '')" />
                    </div>
                </div>
            </div>

            <div>
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-erp-primary">{{ __('Approval Steps') }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ __('Define step number order, approver role or specific user, optional limits, and required/optional flags.') }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach ($steps as $index => $step)
                        <div class="rounded-lg border border-erp-border p-4">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Step :n', ['n' => $index + 1]) }}</p>
                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                <div>
                                    <x-input-label :value="__('Role')" />
                                    <select name="steps[{{ $index }}][approver_role]" class="erp-select mt-1 w-full">
                                        <option value="">{{ __('Select role') }}</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role }}" @selected(($step['approver_role'] ?? '') === $role)>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :value="__('Specific User')" />
                                    <select name="steps[{{ $index }}][approver_user_id]" class="erp-select mt-1 w-full">
                                        <option value="">{{ __('Any user with role') }}</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" @selected((string) ($step['approver_user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :value="__('Approval Limit')" />
                                    <x-text-input name="steps[{{ $index }}][approval_limit]" type="number" step="0.01" class="mt-1 block w-full" :value="$step['approval_limit'] ?? ''" />
                                </div>
                                <div class="flex items-end">
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="hidden" name="steps[{{ $index }}][is_required]" value="0">
                                        <input type="checkbox" name="steps[{{ $index }}][is_required]" value="1" class="rounded border-erp-border text-erp-accent" @checked($step['is_required'] ?? true)>
                                        <span>{{ __('Required step') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <x-primary-button>{{ $isEdit ? __('Save chain') : __('Create chain') }}</x-primary-button>
                <a href="{{ route('admin.governance.chains.index', $scopeQuery) }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
