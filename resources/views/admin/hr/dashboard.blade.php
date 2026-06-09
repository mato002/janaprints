<x-admin-layout :title="__('HR Dashboard')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Dashboard')]]">
    <x-admin.page-header :title="__('HR Dashboard')" :description="__('Workforce overview across attendance, leave, payroll, documents, performance, training, and exit management.')">
        <x-slot name="actions">
            @can('employees.manage')
                <a href="{{ route('admin.employees.index') }}" class="erp-btn-secondary" data-turbo-frame="erp-main">{{ __('Employees') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => __('Total Employees'), 'value' => $overview['headline']['total_employees'], 'icon' => 'users'],
            ['label' => __('Present Today'), 'value' => $overview['headline']['present_today'], 'icon' => 'check-circle'],
            ['label' => __('Attendance %'), 'value' => $overview['headline']['attendance_percent'].'%', 'icon' => 'chart-pie'],
            ['label' => __('On Leave'), 'value' => $overview['headline']['on_leave_today'], 'icon' => 'calendar'],
            ['label' => __('Pending Actions'), 'value' => $overview['headline']['pending_actions'], 'icon' => 'exclamation'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    @if (! empty($overview['alerts']))
        <x-admin.card class="mt-6" :title="__('Attention Required')">
            <ul class="space-y-2 text-sm">
                @foreach ($overview['alerts'] as $alert)
                    @can($alert['permission'])
                        <li>
                            <a href="{{ route($alert['route']) }}" class="text-indigo-600 hover:underline" data-turbo-frame="erp-main">{{ $alert['label'] }}</a>
                        </li>
                    @endcan
                @endforeach
            </ul>
        </x-admin.card>
    @endif

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($overview['modules'] as $module)
            @can($module['permission'])
                <x-admin.card>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-erp-primary">
                                <a href="{{ route($module['route']) }}" class="hover:underline" data-turbo-frame="erp-main">{{ $module['label'] }}</a>
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $module['description'] }}</p>
                        </div>
                    </div>
                    <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
                        @foreach ($module['metrics'] as $metric)
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-slate-400">{{ $metric['label'] }}</dt>
                                <dd class="mt-1 font-semibold">{{ $metric['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <a href="{{ route($module['route']) }}" class="erp-btn-secondary mt-4 inline-block text-xs" data-turbo-frame="erp-main">{{ __('Open module') }}</a>
                </x-admin.card>
            @endcan
        @endforeach
    </div>
</x-admin-layout>
