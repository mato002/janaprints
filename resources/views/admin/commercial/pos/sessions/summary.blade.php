<x-admin-layout :title="__('Session summary :number', ['number' => $session->session_number])">
    @include('admin.commercial.pos.sessions.partials.summary-body', [
        'session' => $session,
        'metrics' => $metrics,
        'varianceTolerance' => $varianceTolerance,
    ])

    <div class="mt-4 flex flex-wrap items-center justify-center gap-2 print:hidden">
        <button type="button" onclick="window.print()" class="erp-btn-primary">{{ __('Print summary') }}</button>
        @can('export', $session)
            <x-admin.export-dropdown :pdf-url="route('admin.commercial.pos.sessions.export', $session)" />
        @endcan
        <a href="{{ route('admin.commercial.pos.sessions.show', $session) }}" class="erp-btn-secondary">{{ __('Back to session') }}</a>
    </div>
</x-admin-layout>
