@php($chain = $tabData['chain'] ?? [])

<div class="job-360__traceability">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('End-to-end traceability') }}</h3>

    <ol class="job-360__chain flex flex-col gap-0 sm:flex-row sm:flex-wrap sm:items-stretch">
        @foreach ($chain as $index => $step)
            <li class="job-360__chain-step flex min-w-0 flex-1 flex-col sm:max-w-[11rem]">
                <div class="rounded-lg border border-erp-border bg-erp-page p-3 h-full {{ ($step['state'] ?? '') === 'placeholder' ? 'border-dashed' : '' }}">
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $step['label'] }}</span>
                    @if (! empty($step['placeholder']))
                        <p class="mt-2 text-xs text-slate-500">{{ $step['placeholder_message'] ?? __('Coming soon') }}</p>
                    @elseif (! empty($step['url']))
                        <a href="{{ $step['url'] }}" class="mt-1 block text-sm font-semibold text-erp-accent hover:text-erp-accent-hover" data-turbo-frame="erp-main">
                            {{ $step['reference'] }}
                        </a>
                    @else
                        <p class="mt-1 text-sm font-medium text-erp-primary">{{ $step['reference'] }}</p>
                    @endif
                    @if (! empty($step['state']) && $step['state'] !== 'placeholder')
                        <span class="erp-badge erp-badge--draft mt-2 text-[10px]">{{ $step['state'] }}</span>
                    @endif
                </div>
                @if (! $loop->last)
                    <span class="job-360__chain-arrow hidden sm:inline text-slate-300 px-1 self-center" aria-hidden="true">→</span>
                @endif
            </li>
        @endforeach
    </ol>
</div>
