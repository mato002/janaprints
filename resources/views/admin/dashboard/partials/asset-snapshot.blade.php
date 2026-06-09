@php
    $assets = $dashboard['asset_snapshot'] ?? null;
    $links = $assets['links'] ?? [];
@endphp

@if (! empty($assets['visible']))
    <section class="exec-panel exec-panel--assets">
        <div class="exec-panel__head exec-panel__head--split">
            <h2 class="exec-panel__title">{{ __('Asset Snapshot') }}</h2>
            @if ($links !== [])
                <nav class="exec-finance-links" aria-label="{{ __('Asset intelligence') }}">
                    @foreach ($links as $link)
                        <a href="{{ $link['url'] }}" data-turbo-frame="erp-main" class="exec-finance-links__item">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            @endif
        </div>
        <dl class="exec-dl exec-dl--grid">
            <div class="exec-dl__row"><dt>{{ __('Asset Count') }}</dt><dd>{{ $assets['asset_count'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('Net Book Value') }}</dt><dd>{{ $assets['net_book_value'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('Depreciation MTD') }}</dt><dd>{{ $assets['depreciation_mtd'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('Warranty Expiry') }}</dt><dd>{{ $assets['warranty_expiry'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('Assets Requiring Service') }}</dt><dd>{{ $assets['requiring_service'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('Critical Assets') }}</dt><dd>{{ $assets['critical_assets'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('End-of-Life Assets') }}</dt><dd>{{ $assets['end_of_life'] ?? '—' }}</dd></div>
        </dl>
    </section>
@endif
