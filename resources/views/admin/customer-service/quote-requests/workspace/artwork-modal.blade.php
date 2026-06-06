@if (! empty($workspace['artwork_files']))
    <div
        x-show="artworkOpen"
        x-cloak
        class="qr-360__artwork-modal"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="artworkOpen = false"
    >
        <div class="qr-360__artwork-modal-backdrop" @click="artworkOpen = false"></div>
        <div class="qr-360__artwork-modal-panel">
            @php
                $modalFile = $workspace['artwork_files'][0];
            @endphp
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-white">{{ $modalFile['name'] }}</h3>
                <button type="button" class="crm-360__btn crm-360__btn--ghost text-white" @click="artworkOpen = false">{{ __('Close') }}</button>
            </div>
            @foreach ($workspace['artwork_files'] as $file)
                <div x-show="activeArtwork === @js($file['id'])" x-cloak>
                    @if ($file['is_image'])
                        <img src="{{ $file['preview_url'] }}" alt="{{ $file['name'] }}" class="max-h-[80vh] w-full rounded-xl object-contain">
                    @elseif ($file['is_pdf'])
                        <iframe src="{{ $file['preview_url'] }}" title="{{ $file['name'] }}" class="h-[80vh] w-full rounded-xl bg-white"></iframe>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
