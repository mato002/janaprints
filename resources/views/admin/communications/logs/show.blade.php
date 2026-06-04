@include('admin.communications.logs.360._data')

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
        <header class="comm-log-360__header">
            <div class="comm-log-360__header-top">
                <a
                    href="{{ route('admin.communications.logs.timeline') }}"
                    class="comm-log-360__back"
                    data-turbo-frame="erp-main"
                >
                    ← {{ __('Communication logs') }}
                </a>
                @can('export', App\Models\Communications\CommunicationLog::class)
                    <a
                        href="{{ route('admin.communications.logs.export') }}"
                        class="comm-log-360__export-link"
                        data-turbo-frame="erp-main"
                    >{{ __('Export') }}</a>
                @endcan
            </div>
            <div class="comm-log-360__header-main">
                <div class="min-w-0">
                    <p class="comm-log-360__ref">{{ $log->reference_number }}</p>
                    <h1 class="comm-log-360__title">
                        {{ $log->channel->label() }} {{ $log->communication_type->label() }}
                    </h1>
                    <p class="comm-log-360__datetime">
                        {{ $log->created_at?->format('d M Y') }}
                        <span aria-hidden="true"> • </span>
                        {{ $log->created_at?->format('H:i') }}
                    </p>
                </div>
                <span class="comm-log-360__status comm-log-360__status--{{ $statusTone }}">
                    {{ strtoupper($log->status->label()) }}
                </span>
            </div>
        </header>

        <section class="comm-log-360__kpi-strip" aria-label="{{ __('Communication KPIs') }}">
            <div class="comm-log-360__kpi">
                <span class="comm-log-360__kpi-label">{{ __('Message status') }}</span>
                <span class="comm-log-360__kpi-value">{{ $log->status->label() }}</span>
            </div>
            <div class="comm-log-360__kpi">
                <span class="comm-log-360__kpi-label">{{ __('Recipients') }}</span>
                <span class="comm-log-360__kpi-value">{{ $recipientCount }}</span>
            </div>
            <div class="comm-log-360__kpi">
                <span class="comm-log-360__kpi-label">{{ __('Delivery events') }}</span>
                <span class="comm-log-360__kpi-value">{{ $eventCount }}</span>
            </div>
            <div class="comm-log-360__kpi">
                <span class="comm-log-360__kpi-label">{{ __('Channel') }}</span>
                <span class="comm-log-360__kpi-value comm-log-360__kpi-value--sm">{{ $log->channel->label() }}</span>
            </div>
            <div class="comm-log-360__kpi">
                <span class="comm-log-360__kpi-label">{{ __('Created by') }}</span>
                <span class="comm-log-360__kpi-value comm-log-360__kpi-value--sm">{{ $log->creator?->name ?? '—' }}</span>
            </div>
            <div class="comm-log-360__kpi">
                <span class="comm-log-360__kpi-label">{{ __('Sent time') }}</span>
                <span class="comm-log-360__kpi-value comm-log-360__kpi-value--sm">{{ $log->sent_at?->format('d M H:i') ?? '—' }}</span>
            </div>
        </section>

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
