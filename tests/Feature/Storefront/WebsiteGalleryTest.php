<?php

namespace Tests\Feature\Storefront;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\WebsiteGalleryItem;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebsiteGalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('public');
    }

    public function test_gallery_page_loads_with_fallback_projects(): void
    {
        $response = $this->get(route('storefront.gallery'));

        $response->assertOk();
        $response->assertSee('Our Work Gallery');
        $response->assertSee('Print &amp; Branding Projects', false);
        $response->assertSee('data-portfolio-grid', false);
        $response->assertSee('public-masonry-gallery', false);
        $response->assertSee('Commercial Print Production');
        $response->assertSee('Premium Business Cards');
    }

    public function test_full_gallery_merges_admin_and_storefront_images_without_duplicates(): void
    {
        WebsiteGalleryItem::query()->create([
            'uuid' => (string) str()->uuid(),
            'title' => 'Admin Business Cards',
            'slug' => 'admin-business-cards',
            'category' => 'business-cards',
            'image_path' => '/images/storefront/gallery/business-cards.jpg',
            'alt_text' => 'Admin business cards',
            'is_published' => true,
        ]);

        $items = app(\App\Services\Storefront\PublicGalleryService::class)->allItems();
        $images = collect($items)->pluck('image')->all();

        $this->assertContains('/images/storefront/gallery/business-cards.jpg', $images);
        $this->assertContains('/images/storefront/gallery/print-production.jpg', $images);
        $this->assertSame(count($images), count(array_unique($images)));
    }

    public function test_our_work_route_aliases_gallery(): void
    {
        $this->get(route('storefront.our-work'))
            ->assertOk()
            ->assertSee('Our Work Gallery');
    }

    public function test_homepage_shows_view_full_gallery_button(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('View Full Gallery')
            ->assertSee('Recent Work Delivered')
            ->assertSee('data-portfolio-item', false);
    }

    public function test_gallery_cards_do_not_expose_internal_metadata(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Corporate Business Cards');
        $response->assertSee('public-masonry-gallery', false);
        $response->assertDontSee('public-masonry-item__caption', false);
        $response->assertDontSee('View Work', false);
        $response->assertDontSee('data-portfolio-empty', false);
    }

    public function test_category_filters_only_show_categories_with_items(): void
    {
        WebsiteGalleryItem::query()->create([
            'uuid' => (string) str()->uuid(),
            'title' => 'Only Cards',
            'slug' => 'only-cards',
            'category' => 'business-cards',
            'image_path' => '/images/storefront/gallery/business-cards.jpg',
            'alt_text' => 'Business cards',
            'is_published' => true,
        ]);

        $filters = app(\App\Services\Storefront\PublicGalleryService::class)->categoriesWithItems();
        $slugs = collect($filters)->pluck('slug')->all();

        $this->assertContains('all', $slugs);
        $this->assertContains('business-cards', $slugs);
        $this->assertContains('packaging', $slugs);
        $this->assertNotContains('nonexistent-category', $slugs);
    }

    public function test_admin_can_create_gallery_item(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post(route('admin.website.gallery.store'), [
            'title' => 'Corporate Business Cards',
            'category' => 'business-cards',
            'description' => 'Premium matte business cards for a Nairobi corporate client.',
            'location' => 'Nairobi',
            'quantity_label' => '2,500 cards',
            'timeline_label' => '3 business days',
            'materials_label' => '350gsm art card, matte lamination',
            'outcome' => 'Consistent brand reproduction across all staff cards.',
            'alt_text' => 'Corporate business cards printed by Jana Prints',
            'is_featured' => true,
            'is_published' => true,
            'sort_order' => 1,
            'image' => UploadedFile::fake()->image('project.jpg', 1200, 800),
        ]);

        $response->assertRedirect(route('admin.website.gallery.index'));

        $this->assertDatabaseHas('website_gallery_items', [
            'title' => 'Corporate Business Cards',
            'category' => 'business-cards',
            'materials_label' => '350gsm art card, matte lamination',
            'is_featured' => true,
            'is_published' => true,
        ]);
    }

    public function test_gallery_modal_includes_structured_project_detail_markup(): void
    {
        WebsiteGalleryItem::query()->create([
            'uuid' => (string) str()->uuid(),
            'title' => 'Startup Brand Launch Cards',
            'slug' => 'startup-brand-launch-cards',
            'category' => 'business-cards',
            'description' => 'Launch-day business cards for a tech startup.',
            'location' => 'Nairobi',
            'quantity_label' => '1,000 cards',
            'timeline_label' => '2 days',
            'materials_label' => '400gsm black core card, soft-touch laminate',
            'outcome' => 'Cards delivered in time for the investor pitch event.',
            'image_path' => '/images/storefront/gallery/business-cards.jpg',
            'alt_text' => 'Startup business cards',
            'is_published' => true,
        ]);

        $this->get(route('storefront.gallery'))
            ->assertOk()
            ->assertSee('data-portfolio-modal-detail="materials"', false)
            ->assertSee('data-portfolio-modal-detail="quantity"', false)
            ->assertSee('data-portfolio-modal-detail="timeline"', false)
            ->assertSee('data-portfolio-modal-detail="outcome"', false)
            ->assertSee('data-portfolio-modal-location', false)
            ->assertSee('Materials Used', false)
            ->assertSee('Quantity Produced', false)
            ->assertSee('Completion Timeline', false)
            ->assertSee('"materials_label":"400gsm black core card, soft-touch laminate"', false);
    }

    public function test_gallery_image_url_uses_relative_storage_path(): void
    {
        $item = WebsiteGalleryItem::query()->create([
            'uuid' => (string) str()->uuid(),
            'title' => 'Test Item',
            'slug' => 'test-item',
            'category' => 'brochures',
            'image_path' => 'website-gallery/sample.jpg',
            'alt_text' => 'Test alt',
            'is_published' => true,
        ]);

        $this->assertSame('/storage/website-gallery/sample.jpg', $item->publicImageUrl());
    }

    public function test_homepage_follows_reorganized_hierarchy(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('public-hero-proof', false);
        $response->assertDontSee('Scroll to Explore', false);
        $response->assertSee('Everything You Need To Print, Brand &amp; Grow', false);
        $response->assertSee('Recent Work Delivered', false);
        $response->assertSee('View Full Gallery', false);
        $response->assertSee('How Jana Prints Delivers', false);
        $response->assertSee('Inside Jana Prints', false);
        $response->assertSee('Finishing, Packaging &amp; Dispatch', false);
        $response->assertSee('The People Behind Every Project', false);
        $response->assertSee('Design Team', false);
        $response->assertSee('The Jana Prints Quality Promise', false);
        $response->assertSee('data-portfolio-item', false);
        $response->assertDontSee('Production Capabilities', false);
        $response->assertDontSee('Trusted By Businesses Across Kenya', false);
        $response->assertDontSee('Why Businesses Choose Jana Prints', false);

        $content = $response->getContent();
        preg_match_all('/<section id="(services|recent-work|workflow|inside-jana|team|testimonials|quote-form|contact|location)"/', $content, $sectionMatches);

        $this->assertSame(
            ['services', 'recent-work', 'workflow', 'inside-jana', 'team', 'testimonials', 'quote-form', 'contact', 'location'],
            $sectionMatches[1]
        );
        $this->assertStringContainsString('The Jana Prints Quality Promise', $content);
    }

    public function test_homepage_uses_wide_layout_containers(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('public-container--hero', false)
            ->assertSee('public-container--wide', false)
            ->assertSee('public-header__bar', false)
            ->assertSee('public-hero__grid', false);
    }

    public function test_gallery_page_uses_wide_portfolio_container(): void
    {
        $this->get(route('storefront.gallery'))
            ->assertOk()
            ->assertSee('public-container--wide', false);
    }

    public function test_homepage_has_consolidated_contact_and_cta_sections(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="contact"', false);
        $response->assertSee('Get Your Free Quotation', false);
        $response->assertSee('Talk To Jana Prints', false);
        $response->assertSee('id="location"', false);
        $response->assertSee('Contact Details', false);
        $response->assertSee('public-contact-icon-bubble', false);
        $response->assertSee('Ready To Start Your Next Print Project?', false);
        $response->assertSee('Inquiry Type', false);
        $response->assertSee('Start WhatsApp Chat', false);
        $response->assertSee('Talk to our team during business hours.', false);
        $response->assertDontSee('Ready To Bring Your Ideas To Life?', false);
        $response->assertDontSee('How Would You Like To Proceed?', false);
        $response->assertDontSee('Need A Quick Response?', false);
        $response->assertDontSee('Visit, Call Or Email Us', false);
    }

    public function test_contact_route_redirects_to_home_contact_section(): void
    {
        $this->get(route('storefront.contact'))
            ->assertRedirect(route('home').'#contact');
    }

    public function test_consultation_url_targets_contact_section_with_type(): void
    {
        $this->assertSame(
            route('home').'?type=consultation#contact',
            \App\Support\Storefront\StorefrontUrls::contactSection('consultation')
        );
    }

    public function test_gallery_page_shows_database_gallery_item_when_published(): void
    {
        WebsiteGalleryItem::query()->create([
            'uuid' => (string) str()->uuid(),
            'title' => 'Fleet Branding Project',
            'slug' => 'fleet-branding-project',
            'category' => 'vehicle-branding',
            'description' => 'Full fleet wrap for a logistics company.',
            'location' => 'Nairobi',
            'quantity_label' => '12 vehicles',
            'timeline_label' => '14 days',
            'image_path' => '/images/storefront/gallery/vehicle-branding.jpg',
            'alt_text' => 'Fleet vehicle branding by Jana Prints',
            'is_featured' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->get(route('storefront.gallery'))
            ->assertOk()
            ->assertSee('Fleet Branding Project');
    }

    protected function adminUser(): User
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);

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
