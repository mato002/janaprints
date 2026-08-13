@php
    $operations = $tabData['operations'] ?? null;
    $queues = $tabData['queues'] ?? collect();
    $controls = $tabData['controls'] ?? null;
    $state = $executionState ?? [];
    $operators = $state['operators'] ?? collect();
@endphp

@if (($state['needs_operator'] ?? false) && (auth()->user()?->can('schedule', $jobCard) || auth()->user()?->can('update', $jobCard)) && ($state['queue_id'] ?? null))
    <x-admin.card class="mb-6 border-amber-200 bg-amber-50" id="assign-operator">
        <form method="POST" action="{{ route('admin.production.job-cards.assign-operator', $jobCard) }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <input type="hidden" name="production_queue_id" value="{{ $state['queue_id'] }}">
            <div class="min-w-[16rem] flex-1">
                <p class="mb-1 text-sm font-medium text-amber-950">{{ __('Assign an operator to this queue stage') }}</p>
                <p class="mb-2 text-xs text-amber-900/80">{{ __('This unblocks the job so production can start.') }}</p>
                <x-admin.lookup-select
                    name="assigned_operator_id"
                    :options="$operators->map(fn ($operator) => ['value' => $operator->id, 'label' => $operator->name])->values()->all()"
                    :required="true"
                    create-route="admin.operators.quick-create"
                    refresh-route="admin.lookups.operators"
                    permission="employees.manage"
                    :modal-title="__('Create operator')"
                    select-class="erp-select w-full text-sm"
                    :placeholder="__('Select operator')"
                />
            </div>
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Assign operator') }}</button>
        </form>
    </x-admin.card>
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Production queue') }}</h3>
        @forelse ($queues as $entry)
            <div class="border-b border-erp-border py-2 text-sm last:border-0">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <span class="font-medium">{{ $entry->workCenter?->name }}</span>
                        <span class="text-slate-500"> — #{{ $entry->queue_position }} ({{ str_replace('_', ' ', $entry->status->value) }})</span>
                    </div>
                    @if ($tabData['can_manage_queue'] ?? false)
                        <div class="flex flex-wrap items-center gap-2">
                            <form method="POST" action="{{ route('admin.production.queues.update', [$jobCard, $entry]) }}" class="inline-flex flex-wrap items-center gap-1">
                                @csrf
                                @method('PUT')
                                <input type="number" name="queue_position" class="erp-input w-16 text-xs py-1" value="{{ $entry->queue_position }}" min="1" required>
                                <select name="status" class="erp-input text-xs py-1">
                                    @foreach ($tabData['queue_statuses'] ?? [] as $status)
                                        <option value="{{ $status->value }}" @selected($entry->status === $status)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="erp-btn-secondary text-xs">{{ __('Update') }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.production.queues.destroy', [$jobCard, $entry]) }}" class="inline" onsubmit="return confirm(@js(__('Remove this queue entry?')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:underline">{{ __('Remove') }}</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">{{ __('No queue entries.') }}</p>
        @endforelse

        @if ($tabData['can_queue'] ?? false)
            <form method="POST" action="{{ route('admin.production.queues.store', $jobCard) }}" class="mt-4 space-y-2" id="queue-form">
                @csrf
                <select name="work_center_id" class="erp-input w-full text-sm" required>
                    @foreach ($tabData['work_centers'] ?? [] as $wc)
                        <option value="{{ $wc->id }}">{{ $wc->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="queue_position" class="erp-input w-full text-sm" value="1" min="1" required>
                <button type="submit" class="erp-btn-secondary text-sm">{{ __('Add to queue') }}</button>
            </form>
        @endif
    </x-admin.card>

    @if ($tabData['can_log'] ?? false)
        <x-admin.card id="log-operation">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Log operation') }}</h3>
            <form method="POST" action="{{ route('admin.production.operations.store', $jobCard) }}" class="grid grid-cols-1 gap-2">
                @csrf
                <select name="work_center_id" class="erp-input text-sm" required>
                    @foreach ($tabData['work_centers'] ?? [] as $wc)
                        <option value="{{ $wc->id }}">{{ $wc->name }}</option>
                    @endforeach
                </select>
                <select name="production_stage_id" class="erp-input text-sm" required>
                    @foreach ($tabData['stages'] ?? [] as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @endforeach
                </select>
                @if ($tabData['operator_assignment_available'] ?? false)
                    <select name="assigned_employee_id" class="erp-input text-sm">
                        <option value="">{{ __('Assign operator (optional)') }}</option>
                        @foreach ($tabData['operators'] ?? [] as $employee)
                            <option value="{{ $employee->id }}">
                                {{ trim($employee->first_name.' '.$employee->last_name) }}
                                @if ($employee->employee_number) ({{ $employee->employee_number }}) @endif
                            </option>
                        @endforeach
                    </select>
                @endif
                <textarea name="remarks" class="erp-input text-sm" rows="2" placeholder="{{ __('Notes (optional)') }}"></textarea>
                <button type="submit" class="erp-btn-primary text-sm">{{ __('Log operation') }}</button>
            </form>
        </x-admin.card>
    @endif
</div>

<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Operations log') }}</h3>
    @if ($operations && $operations->count() > 0)
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Stage') }}</th>
                        <th>{{ __('Work center') }}</th>
                        <th>{{ __('Operator') }}</th>
                        <th>{{ __('Started') }}</th>
                        <th>{{ __('Completed') }}</th>
                        <th>{{ __('Duration') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Notes') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($operations as $op)
                        @php
                            $execStatus = $controls ? $controls->operationExecutionStatus($op, $jobCard) : 'pending';
                            $duration = ($op->started_at && $op->ended_at)
                                ? $op->started_at->diffForHumans($op->ended_at, true)
                                : ($op->started_at ? __('In progress') : '—');
                            $operator = $op->assignedEmployee?->full_name
                                ?? trim(($op->assignedEmployee?->first_name ?? '').' '.($op->assignedEmployee?->last_name ?? ''));
                        @endphp
                        <tr>
                            <td>{{ $op->stage?->name ?? '—' }}</td>
                            <td>{{ $op->workCenter?->name ?? '—' }}</td>
                            <td>{{ $operator !== '' ? $operator : '—' }}</td>
                            <td class="tabular-nums">{{ $op->started_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="tabular-nums">{{ $op->ended_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $duration }}</td>
                            <td>
                                @php
                                    $badgeClass = match ($execStatus) {
                                        'completed' => 'bg-emerald-100 text-emerald-800',
                                        'in_progress' => 'bg-blue-100 text-blue-800',
                                        'blocked' => 'bg-red-100 text-red-800',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="erp-badge {{ $badgeClass }}">{{ str_replace('_', ' ', $execStatus) }}</span>
                            </td>
                            <td class="max-w-[12rem] truncate" title="{{ $op->remarks }}">{{ $op->remarks ?? '—' }}</td>
                            <td class="text-end whitespace-nowrap">
                                @if (($tabData['can_assign'] ?? false) && ! $op->ended_at)
                                    <form method="POST" action="{{ route('admin.production.operations.update', [$jobCard, $op]) }}" class="inline-flex items-center gap-1">
                                        @csrf
                                        @method('PUT')
                                        <select name="assigned_employee_id" class="erp-input text-xs py-1 max-w-[8rem]">
                                            <option value="">{{ __('Unassigned') }}</option>
                                            @foreach ($tabData['operators'] ?? [] as $employee)
                                                <option value="{{ $employee->id }}" @selected($op->assigned_employee_id === $employee->id)>
                                                    {{ $employee->first_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="erp-btn-secondary text-xs">{{ __('Assign') }}</button>
                                    </form>
                                @endif
                                @if (($tabData['can_complete_op'] ?? false) && $op->started_at && ! $op->ended_at)
                                    <form method="POST" action="{{ route('admin.production.operations.complete', [$jobCard, $op]) }}" class="inline mt-1">
                                        @csrf
                                        <button type="submit" class="erp-btn-primary text-xs">{{ __('Complete') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($operations->hasPages())
            <div class="mt-4">{{ $operations->links() }}</div>
        @endif
    @else
        <x-admin.empty-state :title="__('No operations logged')" :description="__('Start production and log operations to track progress.')" />
    @endif
</x-admin.card>
