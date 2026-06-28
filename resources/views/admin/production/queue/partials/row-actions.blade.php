@if ($row['job_360_url'])
    <a href="{{ $row['job_360_url'] }}" class="erp-btn-primary text-xs py-2 px-3" data-turbo-frame="erp-main">{{ __('Open Job 360') }}</a>
@endif

@if (! empty($row['customer_360_url']))
    <a href="{{ $row['customer_360_url'] }}" class="erp-btn-secondary text-xs py-2 px-3" data-turbo-frame="erp-main">{{ __('Customer 360') }}</a>
@endif

@if (! empty($row['print_url']))
    <a href="{{ $row['print_url'] }}" class="erp-btn-secondary text-xs py-2 px-3" target="_blank" rel="noopener">{{ __('Print') }}</a>
@endif

@if (! empty($row['quick_actions']))
    <details class="relative inline-block text-left">
        <summary class="erp-btn-secondary cursor-pointer list-none text-xs py-2 px-3 [&::-webkit-details-marker]:hidden">{{ __('More') }}</summary>
        <div class="absolute right-0 z-10 mt-1 min-w-[10rem] rounded-md border border-erp-border bg-white py-1 shadow-lg">
            @foreach ($row['quick_actions'] as $action)
                @if (($action['type'] ?? 'link') === 'post')
                    <form method="POST" action="{{ $action['url'] }}" class="block">
                        @csrf
                        <button type="submit" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">{{ $action['label'] }}</button>
                    </form>
                @else
                    <a href="{{ $action['url'] }}" class="block px-3 py-2 text-xs text-slate-700 hover:bg-slate-50" data-turbo-frame="erp-main">{{ $action['label'] }}</a>
                @endif
            @endforeach
        </div>
    </details>
@endif
