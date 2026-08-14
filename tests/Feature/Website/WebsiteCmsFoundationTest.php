<?php

namespace Tests\Feature\Website;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\WebsiteMediaItem;
use App\Models\WebsiteSetting;
use App\Services\Website\WebsiteMediaResolver;
use App\Services\Website\WebsiteSettingsService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Support\BuildsWebsiteSettingsPayload;
use Tests\TestCase;

class WebsiteCmsFoundationTest extends TestCase
{
    use BuildsWebsiteSettingsPayload;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        Storage::fake('public');
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $user->givePermissionTo($permission);
        }

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }

    protected function workspaceHeaders(): array
    {
        return ['Turbo-Frame' => 'module-workspace-content'];
    }

    public function test_website_media_items_migration_creates_expected_table(): void
    {
        $this->assertTrue(Schema::hasTable('website_media_items'));
        $this->assertTrue(Schema::hasColumns('website_media_items', [
            'uuid',
            'slot_key',
            'section',
            'label',
            'image_path',
            'fallback_path',
            'alt_text',
            'sort_order',
            'is_active',
            'created_by',
            'updated_by',
        ]));
    }

    public function test_website_settings_migration_creates_expected_table(): void
    {
        $this->assertTrue(Schema::hasTable('website_settings'));
        $this->assertTrue(Schema::hasColumns('website_settings', [
            'uuid',
            'key',
            'group',
            'type',
            'value',
            'fallback_value',
            'is_active',
            'created_by',
            'updated_by',
        ]));
    }

    public function test_media_resolver_returns_database_upload_first(): void
    {
        WebsiteMediaItem::query()->create([
            'uuid' => (string) str()->uuid(),
            'slot_key' => 'cards',
            'section' => 'hero',
            'label' => 'Business Cards',
            'image_path' => 'website-media/custom-cards.jpg',
            'fallback_path' => '/images/storefront/services/business-cards.jpg',
            'alt_text' => 'Custom uploaded cards',
            'is_active' => true,
        ]);

        $path = app(WebsiteMediaResolver::class)->resolvePath('cards');

        $this->assertSame('/storage/website-media/custom-cards.jpg', $path);
    }

    public function test_media_resolver_falls_back_to_config_value(): void
    {
        WebsiteMediaItem::query()->create([
            'uuid' => (string) str()->uuid(),
            'slot_key' => 'cards',
            'section' => 'hero',
            'label' => 'Business Cards',
            'image_path' => null,
            'fallback_path' => '/images/storefront/services/business-cards.jpg',
            'alt_text' => 'Business cards',
            'is_active' => true,
        ]);

        $path = app(WebsiteMediaResolver::class)->resolvePath('cards');

        $this->assertSame('/images/storefront/services/business-cards.jpg', $path);
    }

    public function test_media_resolver_falls_back_to_default_when_slot_unknown(): void
    {
        $path = app(WebsiteMediaResolver::class)->resolvePath('nonexistent-slot');

        $this->assertSame(config('public-images.default'), $path);
    }

    public function test_media_resolver_skips_inactive_database_upload(): void
    {
        WebsiteMediaItem::query()->create([
            'uuid' => (string) str()->uuid(),
            'slot_key' => 'cards',
            'section' => 'hero',
            'label' => 'Business Cards',
            'image_path' => 'website-media/custom-cards.jpg',
            'fallback_path' => '/images/storefront/services/business-cards.jpg',
            'alt_text' => 'Custom uploaded cards',
            'is_active' => false,
        ]);

        $path = app(WebsiteMediaResolver::class)->resolvePath('cards');

        $this->assertSame('/images/storefront/services/business-cards.jpg', $path);
    }

    public function test_settings_service_returns_database_value_first(): void
    {
        WebsiteSetting::query()->create([
            'uuid' => (string) str()->uuid(),
            'key' => 'footer.tagline',
            'group' => 'footer',
            'type' => 'string',
            'value' => 'Custom footer tagline from CMS',
            'fallback_value' => config('site.footer.tagline'),
            'is_active' => true,
        ]);

        $value = app(WebsiteSettingsService::class)->get('footer.tagline');

        $this->assertSame('Custom footer tagline from CMS', $value);
    }

    public function test_settings_service_falls_back_to_config_value(): void
    {
        $value = app(WebsiteSettingsService::class)->get('footer.tagline');

        $this->assertSame(config('site.footer.tagline'), $value);
    }

    public function test_settings_service_falls_back_to_database_fallback_value_before_config(): void
    {
        WebsiteSetting::query()->create([
            'uuid' => (string) str()->uuid(),
            'key' => 'footer.tagline',
            'group' => 'footer',
            'type' => 'string',
            'value' => null,
            'fallback_value' => 'Stored fallback tagline',
            'is_active' => true,
        ]);

        $value = app(WebsiteSettingsService::class)->get('footer.tagline');

        $this->assertSame('Stored fallback tagline', $value);
    }

    public function test_media_admin_routes_require_permissions(): void
    {
        [, , $user] = $this->tenantUser(['users.view']);

        $this->actingAs($user)
            ->withHeaders($this->workspaceHeaders())
            ->get(route('admin.website.media.index'))
            ->assertForbidden();
    }

    public function test_settings_admin_routes_require_permissions(): void
    {
        [, , $user] = $this->tenantUser(['users.view']);

        $this->actingAs($user)
            ->get(route('admin.website.settings.footer-contact'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_view_media_library_and_update_slot(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->withHeaders($this->workspaceHeaders())
            ->get(route('admin.website.media.index'))
            ->assertOk()
            ->assertSee('Media Library');

        $item = WebsiteMediaItem::query()->where('slot_key', 'cards')->first();
        $this->assertNotNull($item);

        $response = $this->actingAs($user)->put(route('admin.website.media.update', $item), [
            'alt_text' => 'Updated business cards alt text',
            'label' => 'Business Cards',
            'sort_order' => 10,
            'is_active' => true,
            'image' => UploadedFile::fake()->image('cards.jpg', 800, 600),
        ]);

        $response->assertRedirect(route('admin.website.media.index', ['section' => $item->section]));

        $item->refresh();
        $this->assertSame('Updated business cards alt text', $item->alt_text);
        $this->assertNotNull($item->image_path);
        $this->assertTrue(str_starts_with($item->image_path, 'website-media/'));
    }

    public function test_authorized_user_can_toggle_media_slot_active_state(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->withHeaders($this->workspaceHeaders())
            ->get(route('admin.website.media.index'))
            ->assertOk();

        $item = WebsiteMediaItem::query()->where('slot_key', 'brochure')->first();
        $this->assertNotNull($item);
        $this->assertTrue($item->is_active);

        $this->actingAs($user)
            ->post(route('admin.website.media.toggle-active', $item))
            ->assertRedirect();

        $this->assertFalse($item->fresh()->is_active);
    }

    public function test_authorized_user_can_update_footer_contact_settings(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.website.settings.footer-contact'))
            ->assertOk()
            ->assertSee('Footer & Contact Settings');

        $payload = $this->footerContactPayload([
            'footer_tagline' => 'Updated CMS footer description.',
            'contact_phone' => '+254 711 111 111',
            'contact_phone_href' => 'tel:+254711111111',
            'contact_email' => 'cms@janaprints.com',
            'contact_email_href' => 'mailto:cms@janaprints.com',
            'whatsapp_number' => '254711111111',
            'whatsapp_message' => 'Hello from CMS settings',
        ]);

        $this->actingAs($user)
            ->put(route('admin.website.settings.footer-contact.update'), $payload)
            ->assertRedirect(route('admin.website.settings.footer-contact'));

        $this->assertDatabaseHas('website_settings', [
            'key' => 'footer.tagline',
            'value' => 'Updated CMS footer description.',
        ]);

        $this->assertSame(
            'Updated CMS footer description.',
            app(WebsiteSettingsService::class)->get('footer.tagline'),
        );
    }

    public function test_website_content_workspace_lists_new_cms_cards(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.workspaces.administration.catalog', ['section' => 'website']))
            ->assertOk()
            ->assertSee(route('admin.workspaces.administration.section', [
                'section' => 'website',
                'tab' => 'gallery',
            ]), false)
            ->assertSee(route('admin.workspaces.administration.section', [
                'section' => 'website',
                'tab' => 'media-library',
            ]), false)
            ->assertSee(route('admin.workspaces.administration.section', [
                'section' => 'website',
                'tab' => 'footer-contact',
            ]), false)
            ->assertSee(route('admin.workspaces.administration.section', [
                'section' => 'website',
                'tab' => 'seo-global',
            ]), false);
    }

    public function test_new_permissions_are_seeded(): void
    {
        foreach ([
            'website.media.view',
            'website.media.create',
            'website.media.edit',
            'website.media.delete',
            'website.settings.view',
            'website.settings.edit',
        ] as $permission) {
            $this->assertNotNull(Permission::findByName($permission, 'web'));
        }
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
