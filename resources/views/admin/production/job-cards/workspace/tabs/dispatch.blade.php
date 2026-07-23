@php
    $checklist = $tabData['checklist'] ?? [];
    $eligibility = $tabData['dispatch_eligibility'] ?? ['eligible' => false, 'blockers' => [], 'warnings' => []];
    $dnEligibility = $tabData['delivery_note_eligibility'] ?? ['eligible' => false, 'blockers' => []];
    $presentation = $tabData['dispatch_presentation'] ?? [];
    $hasDeliveryNote = (bool) ($presentation['has_delivery_note'] ?? false);
    $jobCard = $jobCard ?? null;
@endphp

@if ($hasDeliveryNote)
    @include('admin.production.job-cards.workspace.partials.dispatch-summary-dashboard', [
        'tabData' => $tabData,
        'dispatchPresentation' => $presentation,
    ])
@else
    @if (! empty($eligibility['blockers']))
        @include('admin.production.job-cards.workspace.partials.control-alerts', [
            'alerts' => collect($eligibility['blockers'])->map(fn ($m) => ['type' => 'error', 'message' => $m])->all(),
        ])
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        <x-admin.card class="lg:col-span-1">
            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Readiness score') }}</h3>
            <p class="text-3xl font-bold tabular-nums text-erp-primary">{{ $tabData['readiness_score'] ?? 0 }}%</p>
            <p class="mt-2 text-sm text-slate-600">
                @if ($eligibility['eligible'] ?? false)
                    {{ __('Eligible to mark ready for dispatch') }}
                @else
                    {{ __('Dispatch blocked until checklist items pass') }}
                @endif
            </p>
        </x-admin.card>

        <x-admin.card class="lg:col-span-2">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Dispatch readiness checklist') }}</h3>
            <ul class="divide-y divide-erp-border">
                @foreach ($checklist as $item)
                    @php
                        $stateBadge = match ($item['state']) {
                            'passed' => 'bg-emerald-100 text-emerald-800',
                            'failed' => 'bg-red-100 text-red-800',
                            'warning' => 'bg-amber-100 text-amber-800',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <li class="flex items-center justify-between gap-4 py-2.5 text-sm">
                        <span class="font-medium text-erp-primary">{{ $item['label'] }}</span>
                        <span class="text-slate-500">{{ $item['detail'] }}</span>
                        <span class="erp-badge shrink-0 {{ $stateBadge }}">{{ ucfirst($item['state']) }}</span>
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    </div>

    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Create delivery note') }}</h3>
        <p class="mb-4 text-sm text-slate-600">{{ __('Dispatch is managed through delivery notes. Create a delivery note to begin dispatch for this job.') }}</p>

        @if ($dnEligibility['eligible'] ?? false)
            @can('create', App\Models\Dispatch\DeliveryNote::class)
                <form method="POST" action="{{ route('admin.dispatch.delivery-notes.store-from-job', $jobCard) }}" class="mt-2">
                    @csrf
                    <x-primary-button type="submit">{{ __('Create delivery note') }}</x-primary-button>
                </form>
            @else
                <p class="text-sm text-slate-500">{{ __('You do not have permission to create delivery notes.') }}</p>
            @endcan
        @else
            <ul class="list-disc ps-5 text-sm text-red-700">
                @foreach ($dnEligibility['blockers'] ?? [] as $blocker)
                    <li>{{ $blocker }}</li>
                @endforeach
            </ul>
        @endif
    </x-admin.card>
@endif
