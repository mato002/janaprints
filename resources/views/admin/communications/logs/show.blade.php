<x-admin-layout
    :title="$log->reference_number"
    :breadcrumbs="[
        ['label' => __('Communication Logs'), 'url' => route('admin.communications.logs.dashboard')],
        ['label' => $log->reference_number],
    ]"
>
    <div
        class="comm-log-360 mx-auto max-w-[1200px]"
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
        @include('admin.communications.logs.360._data')
        @include('admin.communications.logs.360.header')
        @include('admin.communications.logs.360.kpi-strip')

        <nav class="comm-log-360__tabs" aria-label="{{ __('Communication workspace tabs') }}">
            @foreach ([
                'overview' => __('Overview'),
                'timeline' => __('Timeline'),
                'recipients' => __('Recipients'),
                'audit' => __('Audit'),
                'analytics' => __('Analytics'),
            ] as $id => $label)
                <button
                    type="button"
                    class="comm-log-360__tab"
                    :class="tab === @js($id) && 'comm-log-360__tab--active'"
                    @click="setTab(@js($id))"
                    :aria-selected="tab === @js($id)"
                >
                    {{ $label }}
                </button>
            @endforeach
        </nav>

        <div class="comm-log-360__panels">
            <div x-show="tab === 'overview'" x-cloak class="comm-log-360__panel">
                @include('admin.communications.logs.360.tab-overview')
            </div>
            <div x-show="tab === 'timeline'" x-cloak class="comm-log-360__panel">
                @include('admin.communications.logs.360.tab-timeline')
            </div>
            <div x-show="tab === 'recipients'" x-cloak class="comm-log-360__panel">
                @include('admin.communications.logs.360.tab-recipients')
            </div>
            <div x-show="tab === 'audit'" x-cloak class="comm-log-360__panel">
                @include('admin.communications.logs.360.tab-audit')
            </div>
            <div x-show="tab === 'analytics'" x-cloak class="comm-log-360__panel">
                @include('admin.communications.logs.360.tab-analytics')
            </div>
        </div>
    </div>
</x-admin-layout>
