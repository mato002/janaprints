@php
    $isEdit = $rule !== null;
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $sectionUrl = route('admin.workspaces.administration.section', ['section' => 'workflow-governance']);
    $conditions = old('conditions', $rule?->conditions_json ?? [['field' => '', 'operator' => 'equals', 'value' => '']]);
    $actions = old('actions', $rule?->actions->map(fn ($action) => [
        'action_type' => $action->action_type->value,
        'config' => $action->config_json ?? [],
    ])->all() ?? [['action_type' => '', 'config' => []]]);
@endphp

<x-admin-layout
    :title="$isEdit ? __('Edit Workflow Rule') : __('New Workflow Rule')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Workflow & Governance'), 'url' => $sectionUrl],
        ['label' => __('Workflow Rules'), 'url' => route('admin.governance.workflow-rules.index', $scopeQuery)],
        ['label' => $isEdit ? $rule->name : __('Create')],
    ]"
>
    <x-admin.page-header
        :title="$isEdit ? __('Edit Workflow Rule') : __('New Workflow Rule')"
        :description="__('Define IF conditions and THEN actions. Example: Quotation Approved → Create Sales Order.')"
    />

    <x-admin.card>
        <form
            method="POST"
            action="{{ $isEdit ? route('admin.governance.workflow-rules.update', $rule) : route('admin.governance.workflow-rules.store') }}"
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
                    <x-input-label for="name" :value="__('Rule Name')" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $rule?->name)" required />
                </div>
                <div>
                    <x-input-label for="entity_type" :value="__('Entity')" />
                    <select id="entity_type" name="entity_type" class="erp-select mt-1 w-full" required>
                        @foreach ($entities as $value => $label)
                            <option value="{{ $value }}" @selected(old('entity_type', $rule?->entity_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="trigger" :value="__('Trigger')" />
                    <select id="trigger" name="trigger" class="erp-select mt-1 w-full" required>
                        @foreach ($triggers as $value => $label)
                            <option value="{{ $value }}" @selected(old('trigger', $rule?->trigger?->value) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" class="erp-input mt-1 w-full" rows="2">{{ old('description', $rule?->description) }}</textarea>
                </div>
            </div>

            <div class="rounded-lg border border-erp-border bg-erp-page/40 p-4">
                <h3 class="text-sm font-semibold text-erp-primary">{{ __('IF — Conditions') }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ __('All conditions must match. Leave field empty to skip a row.') }}</p>
                <div class="mt-3 space-y-3">
                    @foreach ($conditions as $index => $condition)
                        <div class="grid gap-3 md:grid-cols-3">
                            <div>
                                <x-input-label :value="__('Field')" />
                                <input type="text" name="conditions[{{ $index }}][field]" value="{{ $condition['field'] ?? '' }}" class="erp-input mt-1 w-full" placeholder="total_amount, status">
                            </div>
                            <div>
                                <x-input-label :value="__('Operator')" />
                                <select name="conditions[{{ $index }}][operator]" class="erp-select mt-1 w-full">
                                    @foreach ($operators as $value => $label)
                                        <option value="{{ $value }}" @selected(($condition['operator'] ?? 'equals') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label :value="__('Value')" />
                                <input type="text" name="conditions[{{ $index }}][value]" value="{{ $condition['value'] ?? '' }}" class="erp-input mt-1 w-full">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-erp-border bg-erp-page/40 p-4">
                <h3 class="text-sm font-semibold text-erp-primary">{{ __('THEN — Actions') }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ __('Actions execute in order when the rule fires.') }}</p>
                <div class="mt-3 space-y-4">
                    @foreach ($actions as $index => $action)
                        @php $config = $action['config'] ?? []; @endphp
                        <div class="rounded border border-slate-200 bg-white p-4">
                            <div class="mb-3">
                                <x-input-label :value="__('Action Type')" />
                                <select name="actions[{{ $index }}][action_type]" class="erp-select mt-1 w-full" required>
                                    <option value="">{{ __('Select action') }}</option>
                                    @foreach ($actionTypes as $value => $label)
                                        <option value="{{ $value }}" @selected(($action['action_type'] ?? '') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <x-input-label :value="__('Target Entity (create document)')" />
                                    <input type="text" name="actions[{{ $index }}][config][target_entity]" value="{{ $config['target_entity'] ?? '' }}" class="erp-input mt-1 w-full" placeholder="sales_order">
                                </div>
                                <div>
                                    <x-input-label :value="__('Notification Type')" />
                                    <select name="actions[{{ $index }}][config][notification_type]" class="erp-select mt-1 w-full">
                                        <option value="">{{ __('Select type') }}</option>
                                        @foreach ($notificationTypes as $value => $label)
                                            <option value="{{ $value }}" @selected(($config['notification_type'] ?? '') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :value="__('Recipient Role')" />
                                    <select name="actions[{{ $index }}][config][recipient_role]" class="erp-select mt-1 w-full">
                                        <option value="">{{ __('Any') }}</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role }}" @selected(($config['recipient_role'] ?? '') === $role)>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :value="__('Recipient User')" />
                                    <select name="actions[{{ $index }}][config][recipient_user_id]" class="erp-select mt-1 w-full">
                                        <option value="">{{ __('Any') }}</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" @selected((string) ($config['recipient_user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :value="__('Title / Subject')" />
                                    <input type="text" name="actions[{{ $index }}][config][title]" value="{{ $config['title'] ?? $config['subject'] ?? '' }}" class="erp-input mt-1 w-full">
                                </div>
                                <div>
                                    <x-input-label :value="__('Body / Message')" />
                                    <input type="text" name="actions[{{ $index }}][config][body]" value="{{ $config['body'] ?? $config['message'] ?? '' }}" class="erp-input mt-1 w-full">
                                </div>
                                <div>
                                    <x-input-label :value="__('Assign User ID')" />
                                    <input type="number" name="actions[{{ $index }}][config][user_id]" value="{{ $config['user_id'] ?? '' }}" class="erp-input mt-1 w-full">
                                </div>
                                <div>
                                    <x-input-label :value="__('Target Status')" />
                                    <input type="text" name="actions[{{ $index }}][config][target_status]" value="{{ $config['target_status'] ?? '' }}" class="erp-input mt-1 w-full">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.governance.workflow-rules.index', $scopeQuery) }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="erp-btn-primary">{{ $isEdit ? __('Update Rule') : __('Create Rule') }}</button>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
