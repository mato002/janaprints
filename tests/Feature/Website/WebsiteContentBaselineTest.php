<?php

namespace Tests\Feature\Website;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\WebsiteMediaItem;
use App\Models\WebsiteSetting;
use App\Services\Website\WebsiteContentBaselineService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\WebsiteContentBaselineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebsiteContentBaselineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_baseline_seeder_creates_expected_media_slots(): void
    {
        $baseline = app(WebsiteContentBaselineService::class);
        $expected = $baseline->mediaSlotCount();

        $this->assertGreaterThan(30, $expected);

        $this->seed(WebsiteContentBaselineSeeder::class);

        $this->assertSame($expected, WebsiteMediaItem::query()->count());
        $this->assertNotNull(WebsiteMediaItem::query()->where('slot_key', 'cards')->first());
        $this->assertNotNull(WebsiteMediaItem::query()->where('slot_key', 'inside_jana.production-floor')->first());
        $this->assertNotNull(WebsiteMediaItem::query()->where('slot_key', 'gallery_preview.print-production')->first());
    }

    public function test_baseline_seeder_creates_expected_settings(): void
    {
        $baseline = app(WebsiteContentBaselineService::class);
        $expected = $baseline->settingsCount();

        $this->assertGreaterThan(20, $expected);

        $this->seed(WebsiteContentBaselineSeeder::class);

        $this->assertSame($expected, WebsiteSetting::query()->count());
        $this->assertNotNull(WebsiteSetting::query()->where('key', 'footer.tagline')->first());
        $this->assertNotNull(WebsiteSetting::query()->where('key', 'contact.phone')->first());
        $this->assertNotNull(WebsiteSetting::query()->where('key', 'social.instagram')->first());
    }

    public function test_rerunning_baseline_seeder_does_not_duplicate_rows(): void
    {
        $baseline = app(WebsiteContentBaselineService::class);

        $baseline->seed();
        $mediaCount = WebsiteMediaItem::query()->count();
        $settingsCount = WebsiteSetting::query()->count();

        $baseline->seed();

        $this->assertSame($mediaCount, WebsiteMediaItem::query()->count());
        $this->assertSame($settingsCount, WebsiteSetting::query()->count());
    }

    public function test_rerunning_baseline_seeder_does_not_overwrite_custom_db_values(): void
    {
        $baseline = app(WebsiteContentBaselineService::class);
        $baseline->seed();

        $media = WebsiteMediaItem::query()->where('slot_key', 'cards')->firstOrFail();
        $media->update([
            'image_path' => 'website-media/custom-cards.jpg',
            'fallback_path' => '/images/custom-fallback.jpg',
        ]);

        $setting = WebsiteSetting::query()->where('key', 'footer.tagline')->firstOrFail();
        $setting->update([
            'value' => 'Custom CMS footer copy',
            'fallback_value' => 'Custom fallback should remain',
        ]);

        $baseline->seed();

        $media->refresh();
        $setting->refresh();

        $this->assertSame('website-media/custom-cards.jpg', $media->image_path);
        $this->assertSame('/images/custom-fallback.jpg', $media->fallback_path);
        $this->assertSame('Custom CMS footer copy', $setting->value);
        $this->assertSame('Custom fallback should remain', $setting->fallback_value);
    }

    public function test_media_library_lists_seeded_slots(): void
    {
        $user = $this->adminUser();

        app(WebsiteContentBaselineService::class)->seed();

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.website.media.index'))
            ->assertOk()
            ->assertSee('cards')
            ->assertSee('inside_jana.production-floor')
            ->assertSee('Hero Collage');
    }

    public function test_settings_screen_lists_seeded_settings(): void
    {
        $user = $this->adminUser();

        app(WebsiteContentBaselineService::class)->seed();

        $this->actingAs($user)
            ->get(route('admin.website.settings.footer-contact'))
            ->assertOk()
            ->assertSee('footer.tagline')
            ->assertSee('contact.phone')
            ->assertSee('social.instagram');
    }

    public function test_artisan_baseline_command_runs_idempotently(): void
    {
        $this->artisan('website:content-baseline')->assertSuccessful();
        $firstMediaCount = WebsiteMediaItem::query()->count();
        $firstSettingsCount = WebsiteSetting::query()->count();

        $this->artisan('website:content-baseline')->assertSuccessful();

        $this->assertSame($firstMediaCount, WebsiteMediaItem::query()->count());
        $this->assertSame($firstSettingsCount, WebsiteSetting::query()->count());
    }

    protected function adminUser(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user->assignRole(Role::findByName('Super Admin', 'web'));

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return $user;
    }
}
