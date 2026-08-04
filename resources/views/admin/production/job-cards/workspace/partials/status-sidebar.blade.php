@php
    use App\Support\Navigation\WorkspaceEmbed;

    $header = $header ?? [];
    $executionState = $executionState ?? [];
    $tabData = $tabData ?? [];
    $kpis = $kpis ?? [];
    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();

    $summary = $tabData['summary'] ?? [];
    $queue = $tabData['queue'] ?? [];

    $qcKpi = collect($kpis)->firstWhere('key', 'quality');
    $dispatchKpi = collect($kpis)->firstWhere('key', 'dispatch_score');
    $completionKpi = collect($kpis)->firstWhere('key', 'operation_completion');

    $qcValue = $qcKpi['metrics'][0]['value'] ?? __('No QC recorded');
    $dispatchValue = ! empty($dispatchKpi['warning'])
        ? __('Blocked')
        : ($dispatchKpi['metrics'][0]['value'] ?? '—');
    $completionValue = $completionKpi['metrics'][0]['value'] ?? '0%';
@endphp

<aside class="space-y-3" aria-label="{{ __('Job status') }}">
    <x-admin.job-module-card theme="production" :title="__('Live status')" icon="view-grid" compact>
        <div class="grid grid-cols-2 gap-2">
            <x-admin.job-kpi-tile theme="slate" :label="__('Stage')" :value="$header['current_stage_label'] ?? $executionState['stage_name'] ?? '—'" />
            <x-admin.job-kpi-tile theme="production" :label="__('Progress')" :value="((int) ($header['progress_percent'] ?? 0)).'%'" />
            <x-admin.job-kpi-tile theme="dispatch" :label="__('Priority')" :value="ucfirst(str_replace('_', ' ', $summary['priority'] ?? $header['priority']->value ?? '—'))" />
            <x-admin.job-kpi-tile theme="materials" :label="__('Queue')" :value="($queue['position'] ?? null) ? '#'.$queue['position'] : __('Not queued')" />
        </div>
    </x-admin.job-module-card>

    <x-admin.job-module-card theme="qc" :title="__('QC & dispatch')" icon="shield-check" compact>
        <div class="grid grid-cols-1 gap-2">
            <x-admin.job-kpi-tile theme="qc" :label="__('QC status')" :value="$qcValue" :emphasis="str_contains((string) $qcValue, 'No QC')" />
            <x-admin.job-kpi-tile theme="dispatch" :label="__('Dispatch')" :value="$dispatchValue" :emphasis="$dispatchValue === __('Blocked')" />
            <x-admin.job-kpi-tile theme="production" :label="__('Completion')" :value="$completionValue" />
        </div>
        <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality']) }}" class="mt-2 inline-flex text-xs font-medium text-violet-700 hover:underline" @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>{{ __('Open QC tab') }} →</a>
    </x-admin.job-module-card>

    @include('admin.production.job-cards.workspace.partials.commercial-zone', [
        'jobCard' => $jobCard,
        'tabData' => $tabData,
        'dispatchSummary' => $dispatchSummary ?? null,
    ])
</aside>
