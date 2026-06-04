@php
    $chain = $chain ?? [];
@endphp

<ol class="job-360-traceability" role="list">
    @foreach ($chain as $step)
        @switch($step['badge_state'] ?? 'pending')
            @case('complete')
                @php($badgeClass = 'bg-emerald-100 text-emerald-800')
                @break
            @case('pending')
                @php($badgeClass = 'bg-amber-100 text-amber-800')
                @break
            @case('failed')
                @php($badgeClass = 'bg-red-100 text-red-800')
                @break
            @case('inactive')
                @php($badgeClass = 'bg-slate-50 text-slate-500 border border-dashed border-slate-300')
                @break
            @case('not_linked')
            @case('missing')
                @php($badgeClass = 'bg-slate-100 text-slate-600')
                @break
            @default
                @php($badgeClass = 'bg-slate-100 text-slate-600')
        @endswitch
        <li class="job-360-traceability__step job-360-traceability__step--{{ $step['badge_state'] ?? 'pending' }}">
            <div class="job-360-traceability__marker" aria-hidden="true">
                <span class="job-360-traceability__dot"></span>
            </div>
            <div class="job-360-traceability__body rounded-lg border border-erp-border bg-erp-card p-4 shadow-card {{ ! empty($step['empty']) ? 'border-dashed' : '' }}">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <h4 class="text-sm font-semibold text-erp-primary">{{ $step['label'] }}</h4>
                    <span class="erp-badge shrink-0 text-[10px] uppercase tracking-wide {{ $badgeClass }}">
                        {{ $step['badge'] }}
                    </span>
                </div>

                @if (! empty($step['empty']))
                    <p class="mt-2 text-sm font-medium text-slate-600">{{ $step['reference'] }}</p>
                    @if (! empty($step['empty_message']))
                        <p class="mt-1 text-xs text-slate-500">{{ $step['empty_message'] }}</p>
                    @endif
                @elseif (! empty($step['url']))
                    <a
                        href="{{ $step['url'] }}"
                        class="mt-2 block text-sm font-semibold text-erp-accent hover:text-erp-accent-hover"
                        data-turbo-frame="erp-main"
                    >{{ $step['reference'] }}</a>
                @else
                    <p class="mt-2 text-sm font-semibold text-erp-primary">{{ $step['reference'] }}</p>
                @endif

                @if (! empty($step['detail']))
                    <p class="mt-1 text-xs text-slate-500">{{ $step['detail'] }}</p>
                @endif
            </div>
        </li>
    @endforeach
</ol>
