@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp

<div
    x-data="{
        section: new URLSearchParams(window.location.search).get('setup') || 'leave-types',
        setSection(id) {
            this.section = id;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', 'setup');
            url.searchParams.set('setup', id);
            window.history.replaceState({}, '', url);
        },
    }"
>
    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach ([
            ['label' => __('Leave Types'), 'value' => $setupStats['leave_types']],
            ['label' => __('Holidays'), 'value' => $setupStats['holidays']],
            ['label' => __('Policies'), 'value' => $setupStats['policies']],
            ['label' => __('Accrual Rules'), 'value' => $setupStats['accrual_rules']],
            ['label' => __('Carry Rules'), 'value' => $setupStats['carry_rules']],
        ] as $kpi)
            <x-admin.kpi-widget :label="$kpi['label']" :value="$kpi['value']" />
        @endforeach
    </div>

    <nav class="mb-4 flex flex-wrap gap-2 border-b border-slate-200 pb-2" aria-label="{{ __('Leave setup sections') }}">
        @foreach ([
            'leave-types' => __('Leave Types'),
            'holidays' => __('Public Holidays'),
            'policies' => __('Leave Policies'),
            'accrual-rules' => __('Accrual Rules'),
            'carry-forward' => __('Carry Forward'),
        ] as $id => $label)
            <button
                type="button"
                class="rounded-md px-3 py-1.5 text-sm font-medium"
                :class="section === @js($id) ? 'bg-erp-primary text-white' : 'text-slate-600 hover:bg-slate-100'"
                @click="setSection(@js($id))"
            >{{ $label }}</button>
        @endforeach
    </nav>

    <div x-show="section === 'leave-types'" class="space-y-4">
        @include('admin.hr.leave.config.tabs.leave-types')
    </div>
    <div x-show="section === 'holidays'" x-cloak class="space-y-4">
        @include('admin.hr.leave.config.tabs.holidays')
    </div>
    <div x-show="section === 'policies'" x-cloak class="space-y-4">
        @include('admin.hr.leave.config.tabs.policies')
    </div>
    <div x-show="section === 'accrual-rules'" x-cloak class="space-y-4">
        @include('admin.hr.leave.config.tabs.accrual-rules')
    </div>
    <div x-show="section === 'carry-forward'" x-cloak class="space-y-4">
        @include('admin.hr.leave.config.tabs.carry-forward')
    </div>
</div>
