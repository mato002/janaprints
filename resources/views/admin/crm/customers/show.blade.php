<x-admin-layout
    :title="__('Customer 360 Workspace').' · '.$customer->company_name"
    :breadcrumbs="[['label' => __('Customers'), 'url' => route('admin.crm.customers.index')], ['label' => $customer->company_name]]"
>
    <div
        class="crm-360"
        x-data="{
            tab: (() => {
                const requested = new URLSearchParams(window.location.search).get('tab') || 'overview';
                return requested === 'artwork' ? 'print-specifications' : requested;
            })(),
            setTab(id) {
                this.tab = id;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', id);
                window.history.replaceState({}, '', url);
            },
        }"
    >
        @include('admin.crm.customers.360._data')
        @include('admin.crm.customers.360.header')
        @include('admin.crm.customers.360.kpi-strip')

        <nav class="crm-360__tabs" aria-label="{{ __('Customer workspace tabs') }}">
            @foreach ([
                'overview' => __('Overview'),
                'conversations' => __('Conversations'),
                'communications' => __('Communications'),
                'commercial' => __('Commercial'),
                'files' => __('Files'),
                'activities' => __('Activities'),
                'notes' => __('Notes'),
                'timeline' => __('Timeline'),
                'print-specifications' => __('Print Specifications'),
            ] as $id => $label)
                <button
                    type="button"
                    class="crm-360__tab"
                    :class="tab === @js($id) && 'crm-360__tab--active'"
                    @click="setTab(@js($id))"
                    :aria-selected="tab === @js($id)"
                >
                    {{ $label }}
                </button>
            @endforeach
        </nav>

        <div class="crm-360__panels">
            <div x-show="tab === 'overview'" class="crm-360__panel">
                @include('admin.crm.customers.360.tab-overview')
            </div>
            <div x-show="tab === 'conversations'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-conversations')
            </div>
            <div x-show="tab === 'communications'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-communications')
            </div>
            <div x-show="tab === 'commercial'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-commercial')
            </div>
            <div x-show="tab === 'files'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-files')
            </div>
            <div x-show="tab === 'activities'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-activities')
            </div>
            <div x-show="tab === 'notes'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-notes')
            </div>
            <div x-show="tab === 'timeline'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-timeline')
            </div>
            <div x-show="tab === 'print-specifications'" x-cloak class="crm-360__panel">
                @include('admin.crm.customers.360.tab-print-specifications')
            </div>
        </div>
    </div>
</x-admin-layout>
