<?php

namespace Tests\Unit\Support\Storefront;

use App\Support\Storefront\StorefrontUrls;
use Tests\TestCase;

class StorefrontUrlsTest extends TestCase
{
    public function test_quote_form_url_is_absolute_and_targets_storefront_quote_page(): void
    {
        $url = StorefrontUrls::quoteForm();

        $this->assertStringStartsWith('http', $url);
        $this->assertStringEndsWith('#quote-form', $url);
        $this->assertStringContainsString(route('storefront.quote'), $url);
    }
}
