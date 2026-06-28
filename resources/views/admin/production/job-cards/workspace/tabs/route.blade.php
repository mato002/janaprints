@php
    $progress = $tabData['progress'] ?? [];
    $steps = $progress['all'] ?? collect();
    $summary = $tabData['summary'] ?? [];
    $outsourceCtx = $tabData['outsource'] ?? [];
    $isAtVendor = $jobCard->status === App\Enums\ProductionJobCardStatus::Outsourced;
@endphp

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Production route') }}</h2>
        @if (($summary['total'] ?? 0) > 0)
            <p class="mt-1 text-sm text-slate-600">
                {{ __(':done of :total steps complete', ['done' => $summary['completed'] ?? 0, 'total' => $summary['total'] ?? 0]) }}
                @if (! empty($summary['current']))
                    <span class="text-slate-400">·</span>
                    {{ __('Current') }}: <span class="font-medium">{{ $summary['current'] }}</span>
                @endif
            </p>
        @endif
    </div>
    @if (($summary['total'] ?? 0) > 0)
        <div class="flex items-center gap-3 text-sm">
            <div class="w-32 overflow-hidden rounded-full bg-slate-200">
                <div class="h-2 rounded-full bg-erp-accent" style="width: {{ $summary['percent'] ?? 0 }}%"></div>
            </div>
            <span class="tabular-nums font-medium text-slate-700">{{ $summary['percent'] ?? 0 }}%</span>
        </div>
    @endif
</div>

@if ($steps->isNotEmpty())
    <div class="erp-card overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th class="w-12">#</th>
                    <th>{{ __('Step') }}</th>
                    <th>{{ __('Work center') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Started') }}</th>
                    <th>{{ __('Completed') }}</th>
                    <th>{{ __('Outsource') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($steps as $step)
                    <tr class="{{ ($progress['current'] ?? null)?->id === $step->id ? 'bg-erp-accent/5' : '' }}">
                        <td class="tabular-nums text-slate-500">{{ $step->sequence }}</td>
                        <td class="font-medium text-slate-900">{{ $step->step_name }}</td>
                        <td class="text-slate-600">{{ $step->workCenter?->name ?? '—' }}</td>
                        <td>
                            @if (($tabData['can_update'] ?? false) && ! in_array($step->status->value, ['completed', 'skipped'], true))
                                <form method="POST" action="{{ route('admin.production.job-cards.route-steps.update', [$jobCard, $step]) }}" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="erp-select w-full min-w-[8rem] text-xs py-1" onchange="this.form.submit()">
                                        @foreach ($tabData['statuses'] ?? [] as $status)
                                            <option value="{{ $status->value }}" @selected($step->status === $status)>{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <span class="erp-badge {{ $step->status->badgeClass() }}">{{ $step->status->label() }}</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-xs text-slate-600">
                            {{ $step->started_at?->format('Y-m-d H:i') ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap text-xs text-slate-600">
                            @if ($step->completed_at)
                                {{ $step->completed_at->format('Y-m-d H:i') }}
                                @if ($step->completedByUser)
                                    <span class="block text-[11px] text-slate-500">{{ $step->completedByUser->name }}</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-xs">
                            @if ($isAtVendor)
                                <span class="rounded bg-violet-100 px-1.5 py-0.5 font-medium text-violet-800">{{ __('At vendor') }}</span>
                                @if ($outsourceCtx['vendor'] ?? null)
                                    <span class="mt-0.5 block text-slate-500">{{ $outsourceCtx['vendor']->vendor_name }}</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <x-admin.card>
        <x-admin.empty-state
            :title="__('No production route defined')"
            :description="__('Route steps are copied from the product catalog when the job card is created. Edit the product route template or re-create the job from a configured catalogue item.')"
        />
    </x-admin.card>
@endif

@if (($outsourceCtx['can_outsource'] ?? false) || ($outsourceCtx['can_return'] ?? false) || ($outsourceCtx['vendor'] ?? null))
    @include('admin.production.job-cards.workspace.partials.outsource', [
        'jobCard' => $jobCard,
        'tabData' => $tabData,
    ])
@endif
