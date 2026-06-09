<div data-pdf-branding-header class="pdf-branding-header">
    @if (! empty($pdfLogoDataUri))
        <div class="pdf-branding-header__logo-wrap">
            <img src="{{ $pdfLogoDataUri }}" alt="{{ $pdfCompanyName ?? config('app.name') }}" class="pdf-branding-header__logo">
        </div>
    @elseif (! empty($pdfCompanyName))
        <p class="pdf-branding-header__company">{{ $pdfCompanyName }}</p>
    @endif
</div>
