@if ($workspace['artwork'])
    <div
        x-show="artworkOpen"
        x-cloak
        class="qr-intake__artwork-modal"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="artworkOpen = false"
    >
        <div class="qr-intake__artwork-modal-backdrop" @click="artworkOpen = false"></div>
        <div class="qr-intake__artwork-modal-panel">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-white">{{ $workspace['artwork']['name'] }}</h3>
                <button type="button" class="crm-360__btn crm-360__btn--ghost text-white" @click="artworkOpen = false">{{ __('Close') }}</button>
            </div>
            @if ($workspace['artwork']['is_image'])
                <img src="{{ $workspace['artwork']['preview_url'] }}" alt="{{ $workspace['artwork']['name'] }}" class="max-h-[80vh] w-full rounded-xl object-contain">
            @elseif ($workspace['artwork']['is_pdf'])
                <iframe src="{{ $workspace['artwork']['preview_url'] }}" title="{{ $workspace['artwork']['name'] }}" class="h-[80vh] w-full rounded-xl bg-white"></iframe>
            @endif
        </div>
    </div>
@endif
