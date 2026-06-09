<?php

namespace App\View\Composers;

use App\Support\Branding\BrandingAssets;
use Illuminate\View\View;

class BrandingViewComposer
{
    public function __construct(
        protected BrandingAssets $assets,
    ) {}

    public function compose(View $view): void
    {
        if ($view->offsetExists('brandingLogoUrl')) {
            return;
        }

        $view->with($this->assets->presentation());
    }
}
