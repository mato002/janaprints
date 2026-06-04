<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title">{{ __('Quick Actions') }}</h2></div>
    <div class="exec-quick-actions">
        @foreach ($dashboard['quick_actions'] as $action)
            @if (! empty($action['coming_soon']))
                <span class="exec-quick-btn exec-quick-btn--disabled" title="{{ __('Coming soon') }}">{{ $action['label'] }}</span>
            @elseif (! empty($action['route']) && Route::has($action['route']))
                @if (empty($action['permission']) || auth()->user()?->can($action['permission']))
                    <a href="{{ route($action['route']) }}" data-turbo-frame="erp-main" class="exec-quick-btn">{{ $action['label'] }}</a>
                @endif
            @endif
        @endforeach
    </div>
</section>
