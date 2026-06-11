<?php

namespace Tests\Feature\Website;

use App\Models\WebsiteMediaItem;
use App\Services\Website\WebsiteContentBaselineService;
use App\Services\Website\WebsiteMediaResolver;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteMediaPublicRenderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('public');
        app(WebsiteContentBaselineService::class)->seed();
    }

    public function test_hero_renders_database_media_image_when_present(): void
    {
        WebsiteMediaItem::query()->where('slot_key', 'cards')->update([
            'image_path' => 'website-media/hero-cards.jpg',
            'is_active' => true,
        ]);
        app(WebsiteMediaResolver::class)->clearCache();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('/storage/website-media/hero-cards.jpg', false);
    }

    public function test_hero_falls_back_to_config_image_when_database_missing(): void
    {
        WebsiteMediaItem::query()->where('slot_key', 'cards')->delete();
        app(WebsiteMediaResolver::class)->clearCache();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('/images/storefront/services/business-cards.jpg', false);
    }

    public function test_services_section_uses_database_media_image_when_present(): void
    {
        WebsiteMediaItem::query()->where('slot_key', 'services.corporate-printing')->update([
            'image_path' => 'website-media/corporate-printing.jpg',
            'is_active' => true,
        ]);
        app(WebsiteMediaResolver::class)->clearCache();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('/storage/website-media/corporate-printing.jpg', false);
    }

    public function test_inside_jana_section_uses_database_media_image_when_present(): void
    {
        WebsiteMediaItem::query()->where('slot_key', 'inside_jana.production-floor')->update([
            'image_path' => 'website-media/inside-floor.jpg',
            'is_active' => true,
        ]);
        app(WebsiteMediaResolver::class)->clearCache();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('/storage/website-media/inside-floor.jpg', false);
    }

    public function test_team_section_uses_database_media_image_when_present(): void
    {
        WebsiteMediaItem::query()->where('slot_key', 'team.management')->update([
            'image_path' => 'website-media/management-team.jpg',
            'is_active' => true,
        ]);
        app(WebsiteMediaResolver::class)->clearCache();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('/storage/website-media/management-team.jpg', false);
    }

    public function test_inactive_media_item_falls_back_safely(): void
    {
        WebsiteMediaItem::query()->where('slot_key', 'brochure')->update([
            'image_path' => 'website-media/inactive-brochure.jpg',
            'is_active' => false,
        ]);
        app(WebsiteMediaResolver::class)->clearCache();

        $path = app(WebsiteMediaResolver::class)->resolvePath('brochure');

        $this->assertStringContainsString('/images/storefront/services/brochures.jpg', $path);
        $this->assertStringNotContainsString('inactive-brochure', $path);
    }

    public function test_missing_media_item_does_not_break_public_site(): void
    {
        WebsiteMediaItem::query()->delete();
        app(WebsiteMediaResolver::class)->clearCache();

        $this->get(route('home'))->assertOk();
        $this->get(route('storefront.services'))->assertOk();
        $this->get(route('storefront.products'))->assertOk();
        $this->get(route('storefront.gallery'))->assertOk();
    }

    public function test_resolver_uses_four_tier_fallback_order(): void
    {
        $resolver = app(WebsiteMediaResolver::class);

        WebsiteMediaItem::query()->where('slot_key', 'packaging')->update([
            'image_path' => null,
            'fallback_path' => '/images/custom/db-fallback.jpg',
            'is_active' => true,
        ]);
        $resolver->clearCache();
        $this->assertSame('/images/custom/db-fallback.jpg', $resolver->resolvePath('packaging'));

        WebsiteMediaItem::query()->where('slot_key', 'packaging')->update([
            'image_path' => 'website-media/packaging-upload.jpg',
        ]);
        $resolver->clearCache();
        $this->assertSame('/storage/website-media/packaging-upload.jpg', $resolver->resolvePath('packaging'));

        WebsiteMediaItem::query()->where('slot_key', 'nonexistent-slot-xyz')->delete();
        $resolver->clearCache();
        $this->assertSame(
            config('public-images.default'),
            $resolver->resolvePath('nonexistent-slot-xyz'),
        );
    }

    public function test_gallery_page_still_renders_with_existing_merge_logic(): void
    {
        $response = $this->get(route('storefront.gallery'));

        $response->assertOk();
        $response->assertSee('data-portfolio-grid', false);
        $response->assertSee('Commercial Print Production');
    }

    public function test_admin_media_replacement_changes_rendered_public_image(): void
    {
        $item = WebsiteMediaItem::query()->where('slot_key', 'banner')->firstOrFail();

        WebsiteMediaItem::query()->whereKey($item->id)->update([
            'image_path' => 'website-media/replaced-banner.jpg',
            'is_active' => true,
        ]);
        app(WebsiteMediaResolver::class)->clearCache();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('/storage/website-media/replaced-banner.jpg', false);
    }
}
