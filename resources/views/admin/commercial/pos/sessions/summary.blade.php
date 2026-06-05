<x-admin-layout :title="__('Session summary :number', ['number' => $session->session_number])">
    @include('admin.commercial.pos.sessions.partials.summary-body', [
        'session' => $session,
        'metrics' => $metrics,
        'varianceTolerance' => $varianceTolerance,
    ])

    <div class="mt-4 text-center print:hidden">
        <button type="button" onclick="window.print()" class="erp-btn-primary">{{ __('Print summary') }}</button>
        @can('export', $session)
            <a href="{{ route('admin.commercial.pos.sessions.export', $session) }}" class="erp-btn-secondary ml-2">{{ __('Export PDF') }}</a>
        @endcan
        <a href="{{ route('admin.commercial.pos.sessions.show', $session) }}" class="erp-btn-secondary ml-2">{{ __('Back to session') }}</a>
    </div>
</x-admin-layout>
