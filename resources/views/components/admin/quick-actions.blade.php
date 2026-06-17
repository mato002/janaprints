@props(['items' => []])

<div class="workspace-action-bar flex flex-wrap gap-2">
    @foreach ($items as $item)
        @if (! empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']))
            @if (empty($item['permission']) || auth()->user()?->can($item['permission']))
                <a href="{{ route($item['route'], $item['route_params'] ?? []) }}" class="erp-btn-secondary">{{ $item['label'] }}</a>
            @endif
        @elseif (! empty($item['url']))
            <a href="{{ $item['url'] }}" class="erp-btn-secondary">{{ $item['label'] }}</a>
        @endif
    @endforeach
    {{ $slot }}
</div>
