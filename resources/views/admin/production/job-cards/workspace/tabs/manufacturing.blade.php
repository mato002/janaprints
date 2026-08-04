@php
    $hasSpec = $tabData['has_specification'] ?? false;
    $pipeline = $tabData['timeline_pipeline'] ?? [];
@endphp

<div class="manufacturing-tab space-y-4">
    @if ($hasSpec && ! empty($tabData['edit_url']))
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ $tabData['edit_url'] }}" class="erp-btn-secondary text-sm">{{ __('Edit specification') }}</a>
            <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']) }}" class="erp-btn-secondary text-sm">{{ __('Materials') }}</a>
            <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality']) }}" class="erp-btn-secondary text-sm">{{ __('QC') }}</a>
        </div>
    @endif

    @if (! $hasSpec)
        <x-admin.card>
            <p class="text-sm text-slate-600">{{ $tabData['empty_message'] ?? __('No structured Production Specification available.') }}</p>
            @if (! empty($tabData['legacy']))
                <dl class="mt-4 divide-y divide-erp-border rounded-lg border border-erp-border text-sm">
                    @foreach ($tabData['legacy'] as $label => $value)
                        @if ($value)
                            <div class="flex justify-between gap-3 px-3 py-2">
                                <dt class="text-slate-500">{{ ucfirst(str_replace('_', ' ', $label)) }}</dt>
                                <dd class="font-medium">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            @endif
        </x-admin.card>
    @else
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Manufacturing timeline') }}</h3>
            <ol class="flex flex-wrap gap-2">
                @foreach ($pipeline as $stage)
                    @php
                        $tone = match ($stage['state']) {
                            'complete' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'current' => 'bg-erp-primary/10 text-erp-primary border-erp-primary/30 ring-2 ring-erp-primary/20',
                            default => 'bg-slate-50 text-slate-500 border-slate-200',
                        };
                    @endphp
                    <li class="rounded-full border px-3 py-1 text-xs font-medium {{ $tone }}">{{ $stage['label'] }}</li>
                @endforeach
            </ol>
        </x-admin.card>

        @include('admin.production.job-cards.workspace.partials.manufacturing-dashboard', [
            'jobCard' => $jobCard,
            'tabData' => $tabData,
        ])
    @endif
</div>
