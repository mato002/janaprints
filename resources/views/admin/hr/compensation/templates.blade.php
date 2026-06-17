<x-admin-layout :title="__('Payroll Classes')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')], ['label' => __('Payroll classes')]]">
    <x-admin.page-header :title="__('Payroll Classes')" :description="__('Reusable salary bands for roles and grades. Assign a class when onboarding; fine-tune allowances per employee later.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.compensation.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\EmployeeCompensation::class)
                <a href="{{ route('admin.hr.compensation.templates.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Add payroll class') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Class') }}</th>
                <th>{{ __('Basic') }}</th>
                <th>{{ __('Gross') }}</th>
                <th>{{ __('Payroll group') }}</th>
                <th>{{ __('Frequency') }}</th>
                <th class="hidden md:table-cell">{{ __('Status') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($templates as $template)
                <tr>
                    <td>
                        <div class="font-medium">{{ $template->name }}</div>
                        <div class="text-xs text-slate-500">{{ $template->code }}</div>
                        @if (($template->usage_count ?? 0) > 0)
                            <div class="mt-0.5 text-[11px] text-slate-400">
                                {{ trans_choice(':count employee|:count employees', $template->usage_count, ['count' => $template->usage_count]) }}
                            </div>
                        @endif
                    </td>
                    <td>{{ number_format($template->basic_salary, 2) }} {{ $template->currency }}</td>
                    <td>{{ number_format($template->grossComponents(), 2) }} {{ $template->currency }}</td>
                    <td>{{ $template->payroll_group_label ?? '—' }}</td>
                    <td>{{ $template->payment_frequency?->label() }}</td>
                    <td class="hidden md:table-cell">
                        <x-admin.status-badge :variant="$template->is_active ? 'success' : 'neutral'">
                            {{ $template->is_active ? __('Active') : __('Inactive') }}
                        </x-admin.status-badge>
                    </td>
                    <td class="erp-table-actions-col">
                        @can('create', App\Models\Hr\EmployeeCompensation::class)
                            <x-admin.table-row-actions>
                                <x-admin.table-row-action :href="route('admin.hr.compensation.templates.edit', $template)">{{ __('Edit') }}</x-admin.table-row-action>
                                @if ($template->is_active)
                                    <x-admin.table-row-action
                                        method="PATCH"
                                        :action="route('admin.hr.compensation.templates.deactivate', $template)"
                                        :confirm="__('Deactivate this payroll class? It will no longer appear when onboarding new employees.')"
                                    >{{ __('Deactivate') }}</x-admin.table-row-action>
                                @else
                                    <x-admin.table-row-action
                                        method="PATCH"
                                        :action="route('admin.hr.compensation.templates.reactivate', $template)"
                                    >{{ __('Reactivate') }}</x-admin.table-row-action>
                                @endif
                                @if (($template->usage_count ?? 0) === 0)
                                    <x-admin.table-row-action
                                        method="DELETE"
                                        :action="route('admin.hr.compensation.templates.destroy', $template)"
                                        variant="danger"
                                        :confirm="__('Delete this payroll class permanently?')"
                                    >{{ __('Delete') }}</x-admin.table-row-action>
                                @endif
                            </x-admin.table-row-actions>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-admin.empty-state :title="__('No payroll classes yet')" :description="__('Create classes such as Grade A, Supervisor, or Casual staff to speed up employee onboarding.')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$templates" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
