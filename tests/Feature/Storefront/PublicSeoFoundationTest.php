<?php

namespace Tests\Feature\Storefront;

use Tests\TestCase;

class PublicSeoFoundationTest extends TestCase
{
    public function test_robots_txt_disallows_admin_and_internal_paths(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $content = $response->getContent();

        $this->assertStringContainsString('Disallow: /admin', $content);
        $this->assertStringContainsString('Disallow: /login', $content);
        $this->assertStringContainsString('Disallow: /register', $content);
        $this->assertStringContainsString('Disallow: /dashboard', $content);
        $this->assertStringContainsString('Sitemap:', $content);
    }

    public function test_sitemap_xml_contains_public_pages_only(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $content = $response->getContent();

        $this->assertStringContainsString(route('home'), $content);
        $this->assertStringContainsString(route('storefront.about'), $content);
        $this->assertStringContainsString(route('storefront.services'), $content);
        $this->assertStringContainsString(route('storefront.products'), $content);
        $this->assertStringContainsString(route('storefront.contact'), $content);
        $this->assertStringContainsString(route('storefront.quote'), $content);
        $this->assertStringContainsString(route('storefront.services.show', 'corporate-printing'), $content);
        $this->assertStringContainsString(route('storefront.products.show', 'business-cards'), $content);

        $this->assertStringNotContainsString('/admin', $content);
        $this->assertStringNotContainsString('/login', $content);
        $this->assertStringNotContainsString('/dashboard', $content);
        $this->assertStringNotContainsString('/profile', $content);
    }

    public function test_home_page_has_core_seo_tags(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('<title>', false);
        $response->assertSee('meta name="description"', false);
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:description"', false);
        $response->assertSee('name="twitter:card"', false);
    }

    public function test_service_page_has_unique_title_and_description(): void
    {
        $response = $this->get(route('storefront.services.show', 'large-format'));

        $response->assertOk();
        $response->assertSee('Large Format Printing Kenya', false);
        $response->assertSee('meta name="description"', false);
        $response->assertSee('roll-up banners', false);
    }

    public function test_login_page_has_noindex_robots_meta(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('meta name="robots" content="noindex, nofollow"', false);
    }

    public function test_home_page_has_structured_data_json_ld(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('application/ld+json', false);
        $response->assertSee('LocalBusiness', false);
        $response->assertSee('WebSite', false);
    }

    public function test_public_storefront_images_render_with_alt_text(): void
    {
        $response = $this->get(route('storefront.products'));

        $response->assertOk();
        $response->assertSee('alt="Premium business cards printing sample on quality cardstock"', false);
    }

    public function test_admin_path_is_not_in_sitemap(): void
    {
        $response = $this->get('/sitemap.xml');

        $content = $response->getContent();

        $this->assertStringNotContainsString(url('/admin'), $content);
    }
}
