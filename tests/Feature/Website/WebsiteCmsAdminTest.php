<?php

namespace Tests\Feature\Website;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\WebsiteGalleryItem;
use App\Models\WebsiteMediaItem;
use App\Services\Website\WebsiteContentBaselineService;
use App\Services\Website\WebsiteMediaResolver;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Support\BuildsWebsiteSettingsPayload;
use Tests\TestCase;

class WebsiteCmsAdminTest extends TestCase
{
    use BuildsWebsiteSettingsPayload;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        Storage::fake('public');
        app(WebsiteContentBaselineService::class)->seed();
    }

    public function test_workspace_shows_website_content_cards_by_permission(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.workspaces.administration.catalog', ['section' => 'website']))
            ->assertOk()
            ->assertSee('Gallery')
            ->assertSee('Media Library')
            ->assertSee('Footer & Contact')
            ->assertSee('SEO / Global')
            ->assertSee(route('admin.workspaces.administration.section', [
                'section' => 'website',
                'tab' => 'gallery',
            ]), false)
            ->assertSee(route('admin.workspaces.administration.section', [
                'section' => 'website',
                'tab' => 'media-library',
            ]), false);
    }

    public function test_workspace_hides_cards_without_permission(): void
    {
        [, , $user] = $this->tenantUser(['users.view']);

        $this->actingAs($user)
            ->followingRedirects()
            ->get(route('admin.workspaces.administration.section', ['section' => 'website-content']))
            ->assertOk()
            ->assertDontSee(route('admin.website.gallery.index'), false)
            ->assertDontSee(route('admin.website.media.index'), false)
            ->assertDontSee(route('admin.website.settings.footer-contact'), false);
    }

    public function test_media_library_lists_seeded_slots(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->withHeaders($this->workspaceHeaders())
            ->get(route('admin.website.media.index'))
            ->assertOk()
            ->assertSee('cards')
            ->assertSee('services.corporate-printing', false);
    }

    public function test_media_upload_replaces_image_and_requires_alt_text(): void
    {
        $user = $this->adminUser();
        $item = WebsiteMediaItem::query()->where('slot_key', 'cards')->firstOrFail();

        $this->actingAs($user)
            ->put(route('admin.website.media.update', $item), [
                'alt_text' => '',
                'label' => 'Business Cards',
                'sort_order' => 10,
                'is_active' => true,
            ])
            ->assertSessionHasErrors('alt_text');

        $this->actingAs($user)
            ->put(route('admin.website.media.update', $item), [
                'alt_text' => 'Uploaded business cards image',
                'label' => 'Business Cards',
                'sort_order' => 10,
                'is_active' => true,
                'image' => UploadedFile::fake()->image('cards.jpg', 800, 600),
            ])
            ->assertRedirect();

        $item->refresh();
        $this->assertNotNull($item->image_path);
        $this->assertStringStartsWith('website-media/', $item->image_path);
    }

    public function test_media_reset_to_fallback_removes_uploaded_image(): void
    {
        $user = $this->adminUser();
        $item = WebsiteMediaItem::query()->where('slot_key', 'banner')->firstOrFail();

        $this->actingAs($user)
            ->put(route('admin.website.media.update', $item), [
                'alt_text' => 'Banner alt',
                'image' => UploadedFile::fake()->image('banner.jpg'),
                'is_active' => true,
            ]);

        $this->actingAs($user)
            ->post(route('admin.website.media.reset-image', $item))
            ->assertRedirect();

        $this->assertNull($item->fresh()->image_path);
    }

    public function test_media_inactive_falls_back_on_public_site(): void
    {
        WebsiteMediaItem::query()->where('slot_key', 'brochure')->update([
            'image_path' => 'website-media/inactive-brochure.jpg',
            'is_active' => false,
        ]);
        app(WebsiteMediaResolver::class)->clearCache();

        $path = app(WebsiteMediaResolver::class)->resolvePath('brochure');

        $this->assertStringNotContainsString('inactive-brochure', $path);
    }

    public function test_gallery_form_renders_publish_guidance_for_sales_user(): void
    {
        $editor = $this->salesUser();
        $item = WebsiteGalleryItem::query()->create([
            'title' => 'Draft Project',
            'slug' => 'draft-project',
            'category' => 'brochures',
            'alt_text' => 'Draft project image',
            'image_path' => 'website-gallery/draft.jpg',
            'is_published' => false,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        Storage::disk('public')->put('website-gallery/draft.jpg', 'fake');

        $this->actingAs($editor)
            ->get(route('admin.website.gallery.edit', $item))
            ->assertOk()
            ->assertSee(__('Publish permission required to change visibility.'), false)
            ->assertSee(__('Hidden from public site'), false);
    }

    public function test_gallery_move_reorders_items(): void
    {
        $user = $this->adminUser();

        $first = WebsiteGalleryItem::query()->create([
            'title' => 'First',
            'slug' => 'first',
            'category' => 'brochures',
            'alt_text' => 'First image',
            'image_path' => 'website-gallery/first.jpg',
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $second = WebsiteGalleryItem::query()->create([
            'title' => 'Second',
            'slug' => 'second',
            'category' => 'brochures',
            'alt_text' => 'Second image',
            'image_path' => 'website-gallery/second.jpg',
            'is_published' => true,
            'sort_order' => 2,
        ]);

        Storage::disk('public')->put('website-gallery/first.jpg', 'fake');
        Storage::disk('public')->put('website-gallery/second.jpg', 'fake');

        $this->actingAs($user)
            ->post(route('admin.website.gallery.move', $second), ['direction' => 'up'])
            ->assertRedirect(route('admin.website.gallery.index'));

        $this->assertSame(1, $second->fresh()->sort_order);
        $this->assertSame(2, $first->fresh()->sort_order);
    }

    public function test_products_section_filter_lists_product_slots(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->withHeaders($this->workspaceHeaders())
            ->get(route('admin.website.media.index', ['section' => 'products']))
            ->assertOk()
            ->assertSee('products.', false)
            ->assertSee(__('Products'), false);
    }

    public function test_website_content_workspace_shows_cms_support_once_and_gallery_shows_role_guidance(): void
    {
        $editor = $this->salesUser();
        $guideLabel = __('Website CMS Guide');
        $checklistLabel = __('Deployment Checklist');

        $workspace = $this->actingAs($editor)
            ->followingRedirects()
            ->get(route('admin.workspaces.administration.section', ['section' => 'website-content']))
            ->assertOk()
            ->assertSee($guideLabel, false)
            ->assertSee($checklistLabel, false);

        $this->assertSame(1, substr_count($workspace->getContent(), $guideLabel));
        $this->assertSame(1, substr_count($workspace->getContent(), $checklistLabel));

        $this->actingAs($editor)
            ->withHeaders($this->workspaceHeaders())
            ->get(route('admin.website.gallery.index'))
            ->assertOk()
            ->assertDontSee($guideLabel, false)
            ->assertDontSee($checklistLabel, false)
            ->assertSee(__('You can prepare gallery content. Publishing requires approval permission.'), false);
    }

    public function test_gallery_publish_requires_publish_permission(): void
    {
        $editor = $this->salesUser();
        $item = WebsiteGalleryItem::query()->create([
            'title' => 'Hidden Project',
            'slug' => 'hidden-project',
            'category' => 'brochures',
            'alt_text' => 'Hidden project image',
            'image_path' => 'website-gallery/test.jpg',
            'is_published' => false,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        Storage::disk('public')->put('website-gallery/test.jpg', 'fake');

        $this->actingAs($editor)
            ->put(route('admin.website.gallery.update', $item), [
                'title' => 'Hidden Project',
                'category' => 'brochures',
                'alt_text' => 'Hidden project image',
                'is_published' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ])
            ->assertRedirect();

        $this->assertFalse($item->fresh()->is_published);
    }

    public function test_unauthorized_user_cannot_access_cms_pages(): void
    {
        [, , $user] = $this->tenantUser(['users.view']);

        $this->actingAs($user)
            ->withHeaders($this->workspaceHeaders())
            ->get(route('admin.website.media.index'))
            ->assertForbidden();
        $this->actingAs($user)
            ->get(route('admin.website.settings.footer-contact'))
            ->assertForbidden();
        $this->actingAs($user)
            ->withHeaders($this->workspaceHeaders())
            ->get(route('admin.website.gallery.index'))
            ->assertForbidden();
    }

    public function test_public_site_renders_with_database_content_and_config_fallback(): void
    {
        WebsiteMediaItem::query()->where('slot_key', 'cards')->update([
            'image_path' => 'website-media/live-cards.jpg',
            'is_active' => true,
        ]);
        app(WebsiteMediaResolver::class)->clearCache();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('/storage/website-media/live-cards.jpg', false);

        WebsiteMediaItem::query()->delete();
        app(WebsiteMediaResolver::class)->clearCache();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('/images/storefront/services/business-cards.jpg', false);
    }

    protected function workspaceHeaders(): array
    {
        return ['Turbo-Frame' => 'module-workspace-content'];
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

    protected function salesUser(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user->assignRole(Role::findByName('Sales', 'web'));

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return $user;
    }
}
