<x-admin-layout :title="__('Compensation Audit Log')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')], ['label' => __('Audit')]]">
    <x-admin.page-header :title="__('Compensation Audit Log')" :description="__('Salary revisions and compensation change history.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.compensation.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
        </x-slot>
    </x-admin.page-header>

    <x-admin.card class="mb-6">
        <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Salary Changes') }}</h3>
        <x-admin.data-table>
            <x-slot name="head">
                <tr>
                    <th>{{ __('Employee') }}</th>
                    <th>{{ __('Old Salary') }}</th>
                    <th>{{ __('New Salary') }}</th>
                    <th>{{ __('Changed By') }}</th>
                    <th>{{ __('Effective') }}</th>
                    <th>{{ __('Reason') }}</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse ($salary_changes as $change)
                    <tr>
                        <td>{{ $change->employee?->full_name }}</td>
                        <td>{{ number_format($change->old_salary, 2) }}</td>
                        <td>{{ number_format($change->new_salary, 2) }}</td>
                        <td>{{ $change->changedBy?->name ?? '—' }}</td>
                        <td>{{ $change->effective_from?->format('M j, Y') }}</td>
                        <td>{{ $change->reason ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-admin.empty-state :title="__('No salary changes recorded')" /></td></tr>
                @endforelse
            </x-slot>
            <x-slot name="footer"><x-admin.table-pagination :paginator="$salary_changes" /></x-slot>
        </x-admin.data-table>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Activity Log') }}</h3>
        <x-admin.data-table>
            <x-slot name="head">
                <tr>
                    <th>{{ __('When') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Action') }}</th>
                    <th>{{ __('Subject') }}</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse ($activity as $entry)
                    <tr>
                        <td>{{ $entry->created_at?->format('M j, Y H:i') }}</td>
                        <td>{{ $entry->user?->name ?? '—' }}</td>
                        <td>{{ $entry->action }}</td>
                        <td class="text-xs text-slate-500">{{ class_basename($entry->model_type) }} #{{ $entry->model_id }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-admin.empty-state :title="__('No activity logged')" /></td></tr>
                @endforelse
            </x-slot>
            <x-slot name="footer"><x-admin.table-pagination :paginator="$activity" /></x-slot>
        </x-admin.data-table>
    </x-admin.card>
</x-admin-layout>
