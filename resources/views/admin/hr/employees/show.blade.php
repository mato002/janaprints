<x-admin-layout
    :title="__('Employee 360').' · '.$employee->full_name"
    :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Employees'), 'url' => route('admin.employees.index')], ['label' => $employee->full_name]]"
>
    <div
        class="employee-360"
        x-data="{
            tab: new URLSearchParams(window.location.search).get('tab') || 'overview',
            setTab(id) {
                this.tab = id;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', id);
                window.history.replaceState({}, '', url);
            },
        }"
    >
        @include('admin.hr.employees.360.header')
        @include('admin.hr.employees.360.kpi-strip')

        <nav class="mb-6 flex flex-wrap gap-2 border-b border-slate-200 pb-2" aria-label="{{ __('Employee workspace tabs') }}">
            @foreach ($tabs as $tabDef)
                <button
                    type="button"
                    class="rounded-md px-3 py-1.5 text-sm font-medium"
                    :class="tab === @js($tabDef['id']) ? 'bg-erp-primary text-white' : 'text-slate-600 hover:bg-slate-100'"
                    @click="setTab(@js($tabDef['id']))"
                >
                    {{ $tabDef['label'] }}
                </button>
            @endforeach
        </nav>

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        <div class="employee-360__panels">
            @foreach ($tabs as $tabDef)
                <div x-show="tab === @js($tabDef['id'])" @if ($tabDef['id'] !== 'overview') x-cloak @endif>
                    @include('admin.hr.employees.360.tabs.'.$tabDef['id'])
                </div>
            @endforeach
        </div>
    </div>
</x-admin-layout>
