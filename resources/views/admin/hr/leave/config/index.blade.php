<x-admin-layout :title="__('Leave Configuration')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Leave'), 'url' => route('admin.hr.leave.dashboard')], ['label' => __('Configuration')]]">
    <div
        x-data="{
            tab: new URLSearchParams(window.location.search).get('tab') || 'leave-types',
            setTab(id) {
                this.tab = id;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', id);
                window.history.replaceState({}, '', url);
            },
        }"
    >
        <x-admin.page-header :title="__('Leave Configuration')" :description="__('Administer leave types, holidays, policies, accrual, and carry-forward rules.')">
            <x-slot name="actions">
                <a href="{{ route('admin.hr.leave.dashboard') }}" class="erp-btn-secondary">{{ __('Back to leave') }}</a>
            </x-slot>
        </x-admin.page-header>

<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
            @foreach ([
                ['label' => __('Leave Types'), 'value' => $stats['leave_types']],
                ['label' => __('Holidays'), 'value' => $stats['holidays']],
                ['label' => __('Policies'), 'value' => $stats['policies']],
                ['label' => __('Accrual Rules'), 'value' => $stats['accrual_rules']],
                ['label' => __('Carry Rules'), 'value' => $stats['carry_rules']],
            ] as $kpi)
                <x-admin.kpi-widget :label="$kpi['label']" :value="$kpi['value']" />
            @endforeach
        </div>

        <nav class="mb-6 flex flex-wrap gap-2 border-b border-slate-200 pb-2">
            @foreach ([
                'leave-types' => __('Leave Types'),
                'holidays' => __('Public Holidays'),
                'policies' => __('Leave Policies'),
                'accrual-rules' => __('Accrual Rules'),
                'carry-forward' => __('Carry Forward Rules'),
            ] as $id => $label)
                <button type="button" class="rounded-md px-3 py-1.5 text-sm font-medium"
                    :class="tab === @js($id) ? 'bg-erp-primary text-white' : 'text-slate-600 hover:bg-slate-100'"
                    @click="setTab(@js($id))">{{ $label }}</button>
            @endforeach
        </nav>

        <div x-show="tab === 'leave-types'" class="space-y-4">
            @include('admin.hr.leave.config.tabs.leave-types')
        </div>
        <div x-show="tab === 'holidays'" x-cloak class="space-y-4">
            @include('admin.hr.leave.config.tabs.holidays')
        </div>
        <div x-show="tab === 'policies'" x-cloak class="space-y-4">
            @include('admin.hr.leave.config.tabs.policies')
        </div>
        <div x-show="tab === 'accrual-rules'" x-cloak class="space-y-4">
            @include('admin.hr.leave.config.tabs.accrual-rules')
        </div>
        <div x-show="tab === 'carry-forward'" x-cloak class="space-y-4">
            @include('admin.hr.leave.config.tabs.carry-forward')
        </div>
    </div>
</x-admin-layout>
