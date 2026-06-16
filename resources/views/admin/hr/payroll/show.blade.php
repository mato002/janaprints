<x-admin-layout
    :title="__('Payroll Run 360').' · '.$run->reference"
    :breadcrumbs="[['label' => __('Payroll'), 'url' => route('admin.hr.payroll.dashboard')], ['label' => __('Runs'), 'url' => route('admin.hr.payroll.index')], ['label' => $run->reference]]"
>
    <div
        class="payroll-run-360"
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
        @include('admin.hr.payroll.360.header')
        @include('admin.hr.payroll.360.kpi-strip')

        <nav class="mb-6 flex flex-wrap gap-2 border-b border-slate-200 pb-2" aria-label="{{ __('Payroll run workspace tabs') }}">
            @foreach ($tabs as $tabDef)
                <button
                    type="button"
                    class="rounded-md px-3 py-1.5 text-sm font-medium"
                    :class="tab === @js($tabDef['id']) ? 'bg-erp-primary text-white' : 'text-slate-600 hover:bg-slate-100'"
                    @click="setTab(@js($tabDef['id']))"
                >
                    {{ $tabDef['label'] }}
                    @if ($tabDef['id'] === 'review' && ! ($review['can_submit_for_approval'] ?? true))
                        <span class="ml-1 inline-flex h-2 w-2 rounded-full bg-amber-500" aria-hidden="true"></span>
                    @endif
                </button>
            @endforeach
        </nav>

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="payroll-run-360__panels">
            @foreach ($tabs as $tabDef)
                <div x-show="tab === @js($tabDef['id'])" @if ($tabDef['id'] !== 'overview') x-cloak @endif>
                    @include('admin.hr.payroll.360.tabs.'.$tabDef['id'])
                </div>
            @endforeach
        </div>
    </div>
</x-admin-layout>
