@props(['items'])

@php
    use App\Support\Navigation\WorkspaceEmbed;
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<section id="hr-action-center" class="mb-3" aria-label="{{ __('HR Action Center') }}">
    <x-admin.card :padding="false">
        <div class="border-b border-erp-border px-4 py-2.5">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-erp-primary">{{ __('HR Action Center') }}</h2>
            <p class="text-[11px] text-slate-500">{{ __('Pending approvals and items requiring HR attention.') }}</p>
        </div>
        <div class="grid grid-cols-1 gap-px bg-erp-border sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
            @foreach ($items as $item)
                @php
                    $severityClass = match ($item['severity']) {
                        'high' => 'border-l-red-500 bg-red-50/60',
                        'medium' => 'border-l-amber-500 bg-amber-50/60',
                        'low' => 'border-l-emerald-500 bg-emerald-50/40',
                        default => 'border-l-slate-200 bg-white',
                    };
                @endphp
                @if ($item['clickable'] ?? false)
                    <a
                        href="{{ \App\Support\Navigation\WorkspaceEmbed::url($item['url']) }}"
                        class="flex min-h-[5.5rem] flex-col justify-between border-l-4 px-3 py-3 transition-colors hover:bg-slate-50 {{ $severityClass }}"
                        data-turbo-frame="{{ $turboFrame }}"
                    >
                        <span class="text-[11px] font-medium leading-tight text-slate-600">{{ $item['label'] }}</span>
                        <span class="text-2xl font-bold tabular-nums text-erp-primary">{{ $item['count'] }}</span>
                    </a>
                @else
                    <div class="flex min-h-[5.5rem] flex-col justify-between border-l-4 px-3 py-3 {{ $severityClass }}">
                        <span class="text-[11px] font-medium leading-tight text-slate-600">{{ $item['label'] }}</span>
                        <span class="text-2xl font-bold tabular-nums text-erp-primary">{{ $item['count'] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </x-admin.card>
</section>
