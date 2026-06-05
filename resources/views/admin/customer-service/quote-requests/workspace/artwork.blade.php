<section class="crm-360__card">
    <div class="crm-360__card-head">
        <h2 class="crm-360__card-title">{{ __('Artwork Preview') }}</h2>
        @if ($workspace['artwork'])
            <div class="flex flex-wrap gap-2">
                <button type="button" class="crm-360__btn crm-360__btn--ghost crm-360__btn--sm" @click="artworkOpen = true">{{ __('Expand') }}</button>
                <a href="{{ $workspace['artwork']['download_url'] }}" class="crm-360__btn crm-360__btn--outline crm-360__btn--sm">{{ __('Download') }}</a>
            </div>
        @endif
    </div>

    @if ($workspace['artwork'])
        <div class="qr-intake__artwork">
            <div class="qr-intake__artwork-main">
                @if ($workspace['artwork']['is_image'])
                    <img
                        src="{{ $workspace['artwork']['preview_url'] }}"
                        alt="{{ $workspace['artwork']['name'] }}"
                        class="qr-intake__artwork-image"
                        loading="eager"
                    >
                @elseif ($workspace['artwork']['is_pdf'])
                    <iframe
                        src="{{ $workspace['artwork']['preview_url'] }}"
                        title="{{ $workspace['artwork']['name'] }}"
                        class="qr-intake__artwork-pdf"
                    ></iframe>
                @else
                    <div class="qr-intake__artwork-file">
                        <x-admin.icon name="document-text" class="h-12 w-12 text-slate-400" />
                        <p class="mt-3 text-sm font-semibold text-slate-700">{{ $workspace['artwork']['name'] }}</p>
                        <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ $workspace['artwork']['extension'] }} {{ __('file') }}</p>
                    </div>
                @endif
            </div>

            <div class="qr-intake__artwork-meta">
                <p class="font-medium text-slate-800">{{ $workspace['artwork']['name'] }}</p>
                <p class="text-xs text-slate-500">{{ strtoupper($workspace['artwork']['extension']) }} · {{ number_format($workspace['artwork']['size'] / 1024, 1) }} KB</p>
            </div>
        </div>
    @else
        <div class="qr-intake__artwork-empty">
            <x-admin.icon name="color-swatch" class="h-10 w-10 text-slate-300" />
            <p class="mt-3 text-sm font-medium text-slate-600">{{ __('No artwork uploaded') }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ __('Customer did not attach artwork with this request.') }}</p>
        </div>
    @endif
</section>
