@php
    $files = $workspace['artwork_files'];
    $active = $files[0] ?? null;
@endphp

<section class="qr-360__card qr-360__card--artwork">
    <div class="qr-360__card-head">
        <h2 class="qr-360__card-title">{{ __('Artwork Review') }}</h2>
        @if ($active)
            <div class="flex flex-wrap gap-2">
                <button type="button" class="crm-360__btn crm-360__btn--ghost crm-360__btn--sm" @click="artworkOpen = true">{{ __('Expand') }}</button>
                <a href="{{ $active['download_url'] }}" class="crm-360__btn crm-360__btn--outline crm-360__btn--sm">{{ __('Download') }}</a>
            </div>
        @endif
    </div>

    @if ($active)
        <div class="qr-360__artwork">
            <div class="qr-360__artwork-stage">
                @foreach ($files as $file)
                    <div @if (count($files) > 1) x-show="activeArtwork === @js($file['id'])" @endif>
                        @if ($file['is_image'])
                            <img
                                src="{{ $file['preview_url'] }}"
                                alt="{{ $file['name'] }}"
                                class="qr-360__artwork-image"
                                loading="eager"
                            >
                        @elseif ($file['is_pdf'])
                            <iframe
                                src="{{ $file['preview_url'] }}"
                                title="{{ $file['name'] }}"
                                class="qr-360__artwork-pdf"
                            ></iframe>
                        @else
                            <div class="qr-360__artwork-file">
                                <x-admin.icon name="document-text" class="h-12 w-12 text-slate-400" />
                                <p class="mt-3 text-sm font-semibold text-slate-700">{{ $file['name'] }}</p>
                                <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ $file['extension'] }} {{ __('file') }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if (count($files) > 1)
                <div class="qr-360__artwork-thumbs">
                    @foreach ($files as $file)
                        <button
                            type="button"
                            class="qr-360__artwork-thumb"
                            :class="activeArtwork === @js($file['id']) && 'qr-360__artwork-thumb--active'"
                            @click="activeArtwork = @js($file['id'])"
                        >
                            @if ($file['is_image'])
                                <img src="{{ $file['preview_url'] }}" alt="" class="h-full w-full object-cover">
                            @else
                                <span class="text-[10px] font-bold uppercase">{{ $file['extension'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="qr-360__artwork-meta">
                <p class="font-medium text-slate-800">{{ $active['name'] }}</p>
                <p class="text-xs text-slate-500">
                    {{ strtoupper($active['extension']) }}
                    · {{ number_format($active['size'] / 1024, 1) }} KB
                    · {{ __('Uploaded') }} {{ $active['uploaded_at']->format('d M Y, H:i') }}
                </p>
            </div>
        </div>
    @else
        <div class="qr-360__artwork-empty">
            <x-admin.icon name="color-swatch" class="h-10 w-10 text-slate-300" />
            <p class="mt-3 text-sm font-medium text-slate-600">{{ __('No artwork uploaded') }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ __('Customer did not attach artwork with this request.') }}</p>
        </div>
    @endif
</section>
