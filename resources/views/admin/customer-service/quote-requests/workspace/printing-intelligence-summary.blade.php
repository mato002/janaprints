@php
    $summary = $summary ?? null;
    $warnings = $warnings ?? ($summary['warnings'] ?? []);
    $statusMessage = $statusMessage ?? null;
@endphp

@if ($statusMessage)
    <x-admin.alert variant="success" class="mb-4">{{ $statusMessage }}</x-admin.alert>
@endif

@if (! empty($warnings))
    <x-admin.alert variant="warning" class="mb-4">
        <ul class="list-disc space-y-1 pl-4 text-sm">
            @foreach ($warnings as $warning)
                <li>{{ $warning }}</li>
            @endforeach
        </ul>
    </x-admin.alert>
@endif

@if ($summary)
    <dl class="qr-360__pi-grid">
        <div>
            <dt>{{ __('Analysis status') }}</dt>
            <dd>{{ $summary['analysis_status_label'] ?? '—' }}</dd>
        </div>
        <div>
            <dt>{{ __('Colour status') }}</dt>
            <dd>{{ $summary['colour_analysis_status_label'] ?? '—' }}</dd>
        </div>
        <div>
            <dt>{{ __('File type') }}</dt>
            <dd>{{ strtoupper((string) ($summary['file_extension'] ?? '—')) }}</dd>
        </div>
        <div>
            <dt>{{ __('Page count') }}</dt>
            <dd>{{ $summary['page_count'] ?? '—' }}</dd>
        </div>
        <div>
            <dt>{{ __('Dimensions') }}</dt>
            <dd>{{ $summary['dimensions'] ?? '—' }}</dd>
        </div>
        <div>
            <dt>{{ __('Colour coverage') }}</dt>
            <dd>
                @if (($summary['cmyk_coverage_percent'] ?? null) !== null)
                    {{ number_format((float) $summary['cmyk_coverage_percent'], 2) }}%
                @else
                    —
                @endif
            </dd>
        </div>
        <div>
            <dt>{{ __('Coverage class') }}</dt>
            <dd>{{ $summary['coverage_class_label'] ?? '—' }}</dd>
        </div>
        <div>
            <dt>{{ __('Estimated ink') }}</dt>
            <dd>
                @if (($summary['estimated_ink_ml'] ?? null) !== null)
                    {{ number_format((float) $summary['estimated_ink_ml'], 2) }} ml
                    @if (($summary['estimated_ink_cost'] ?? null) !== null)
                        · {{ number_format((float) $summary['estimated_ink_cost'], 2) }}
                    @endif
                @else
                    —
                @endif
            </dd>
        </div>
        <div>
            <dt>{{ __('Recommended machine') }}</dt>
            <dd>{{ $summary['recommended_machine'] ?? '—' }}</dd>
        </div>
    </dl>

    @if (! empty($summary['warnings']))
        <div class="qr-360__pi-warnings mt-4">
            <p class="qr-360__pi-warnings-title">{{ __('Warnings') }}</p>
            <ul class="list-disc space-y-1 pl-4 text-sm text-amber-800">
                @foreach ($summary['warnings'] as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@else
    <p class="text-sm text-slate-500">{{ __('No analysis results are available yet.') }}</p>
@endif
