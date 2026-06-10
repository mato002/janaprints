<x-admin-layout :title="__('Exit Management')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Exit Management')]]">
    <x-admin.page-header :title="__('Exit Management')" :description="__('Employee offboarding, clearance, and final dues settlement.')">
        <x-slot name="actions">
            @can('create', App\Models\Hr\EmployeeExit::class)
                <a href="{{ route('admin.hr.exit.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Initiate exit') }}</a>
            @endcan
            <a href="{{ route('admin.hr.exit.index') }}" class="erp-btn-secondary">{{ __('All exits') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Active Exits'), 'value' => $stats['active_exits'], 'icon' => 'switch-horizontal'],
            ['label' => __('Pending Clearance'), 'value' => $stats['pending_clearance'], 'icon' => 'clipboard-check'],
            ['label' => __('Settled This Year'), 'value' => $stats['settled_this_year'], 'icon' => 'cash'],
            ['label' => __('Closed This Year'), 'value' => $stats['closed_this_year'], 'icon' => 'check-circle'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card class="mt-6" :title="__('Recent Exit Processes')">
        @if ($recentExits->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No exit processes yet.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500">
                            <th class="py-2 pr-3">{{ __('Reference') }}</th>
                            <th class="py-2 pr-3">{{ __('Employee') }}</th>
                            <th class="py-2 pr-3">{{ __('Type') }}</th>
                            <th class="py-2 pr-3">{{ __('Net Dues') }}</th>
                            <th class="py-2">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentExits as $exit)
                            <tr class="border-b border-slate-100">
                                <td class="py-2 pr-3">
                                    <a href="{{ route('admin.hr.exit.show', $exit) }}" class="text-indigo-600 hover:underline">{{ $exit->reference }}</a>
                                </td>
                                <td class="py-2 pr-3">{{ $exit->employee->full_name }}</td>
                                <td class="py-2 pr-3">{{ $exit->exit_type->label() }}</td>
                                <td class="py-2 pr-3">{{ number_format($exit->net_final_dues, 2) }}</td>
                                <td class="py-2">{{ $exit->status->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.card>
</x-admin-layout>
