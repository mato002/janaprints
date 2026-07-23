@if (! empty($operatorDeskShortcuts))
    <section class="exec-panel exec-panel--operator-desks" aria-label="{{ __('Operator desks') }}">
        <div class="exec-panel__head">
            <h2 class="exec-panel__title">{{ __('Operator Desks') }}</h2>
            <span class="exec-panel__meta">{{ __('Streamlined flows — one login, no role switching') }}</span>
        </div>
        <div class="exec-operator-desks">
            @foreach ($operatorDeskShortcuts as $desk)
                <a
                    href="{{ $desk['url'] }}"
                    data-turbo-frame="erp-main"
                    data-turbo-action="advance"
                    class="exec-operator-desks__card"
                >
                    <span class="exec-operator-desks__icon" aria-hidden="true">
                        <x-admin.icon :name="$desk['icon']" class="h-5 w-5" />
                    </span>
                    <span class="exec-operator-desks__body">
                        <span class="exec-operator-desks__label">{{ $desk['label'] }}</span>
                        <span class="exec-operator-desks__description">{{ $desk['description'] }}</span>
                    </span>
                    <x-admin.icon name="chevron-right" class="exec-operator-desks__chevron h-4 w-4" aria-hidden="true" />
                </a>
            @endforeach
        </div>
    </section>
@endif
