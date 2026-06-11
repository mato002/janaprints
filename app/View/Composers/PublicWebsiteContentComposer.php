<?php

namespace App\View\Composers;

use App\Support\Website\PublicWebsiteContent;
use Illuminate\View\View;

class PublicWebsiteContentComposer
{
    public function __construct(
        protected PublicWebsiteContent $content,
    ) {}

    public function compose(View $view): void
    {
        $view->with([
            'websiteSite' => $this->content->site(),
            'websiteSeo' => $this->content->seo(),
            'websiteFooter' => $this->content->footer(),
            'websiteContact' => $this->content->contact(),
            'websiteWhatsapp' => $this->content->whatsapp(),
            'websiteWhatsappUrl' => $this->content->whatsappUrl(),
            'websiteFooterCopyright' => $this->content->footerCopyrightLine(),
        ]);
    }
}
