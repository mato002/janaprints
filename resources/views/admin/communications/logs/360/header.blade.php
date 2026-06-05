<header class="comm-log-360__header">
    <div class="comm-log-360__header-top">
        <x-admin.crm-btn
            variant="ghost"
            size="sm"
            :href="route('admin.communications.logs.timeline')"
            class="!px-2.5"
            data-turbo-frame="erp-main"
        >← {{ __('Communication logs') }}</x-admin.crm-btn>
        @can('export', App\Models\Communications\CommunicationLog::class)
            <x-admin.crm-btn variant="outline" size="sm" :href="route('admin.communications.logs.export')" data-turbo-frame="erp-main">{{ __('Export') }}</x-admin.crm-btn>
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
