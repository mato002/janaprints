@php
    $panel = $walkInPanel ?? ['mode' => 'customer', 'title' => __('Context')];
    $mode = $panel['mode'] ?? 'customer';
@endphp

<aside class="space-y-3">
    <x-admin.card>
        <div class="mb-3 flex items-start justify-between gap-2">
            <h3 class="text-sm font-semibold text-slate-900">{{ $panel['title'] ?? __('Context') }}</h3>
            @if (! empty($panel['customer_360_url']))
                <a href="{{ $panel['customer_360_url'] }}" class="shrink-0 text-xs font-medium text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ __('Customer 360') }}</a>
            @endif
        </div>

        @if ($mode === 'customer')
            @include('admin.sales.desk.partials.walk-in-panel.customer', ['panel' => $panel])
        @elseif ($mode === 'specification')
            @include('admin.sales.desk.partials.walk-in-panel.specification', ['panel' => $panel])
        @elseif ($mode === 'order')
            @include('admin.sales.desk.partials.walk-in-panel.order', ['panel' => $panel])
        @else
            @include('admin.sales.desk.partials.walk-in-panel.release', ['panel' => $panel])
        @endif
    </x-admin.card>
</aside>
