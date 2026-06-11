<?php

namespace Tests\Feature\Website;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\WebsiteSetting;
use App\Services\Website\WebsiteContentBaselineService;
use App\Services\Website\WebsiteSettingsService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Support\BuildsWebsiteSettingsPayload;
use Tests\TestCase;

class WebsiteSettingsPublicRenderingTest extends TestCase
{
    use BuildsWebsiteSettingsPayload;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        app(WebsiteContentBaselineService::class)->seed();
    }

    public function test_footer_reads_database_setting_first(): void
    {
        $this->setSetting('footer.tagline', 'CMS footer description from database.');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('CMS footer description from database.', false);
    }

    public function test_footer_falls_back_to_config_when_database_missing(): void
    {
        WebsiteSetting::query()->where('key', 'footer.tagline')->delete();
        app(WebsiteSettingsService::class)->clearCache();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('commercial printing, branding, packaging', false);
    }

    public function test_phone_and_email_render_correctly_on_public_site(): void
    {
        $this->setSetting('contact.phone', '+254 722 222 222');
        $this->setSetting('contact.phone_href', 'tel:+254722222222');
        $this->setSetting('contact.email', 'public@janaprints.com');
        $this->setSetting('contact.email_href', 'mailto:public@janaprints.com');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('+254 722 222 222', false);
        $response->assertSee('tel:+254722222222', false);
        $response->assertSee('public@janaprints.com', false);
        $response->assertSee('mailto:public@janaprints.com', false);
    }

    public function test_whatsapp_link_uses_database_settings(): void
    {
        $this->setSetting('whatsapp.number', '254733333333');
        $this->setSetting('whatsapp.message', 'Hello from CMS WhatsApp settings');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee(
            'https://wa.me/254733333333?text='.rawurlencode('Hello from CMS WhatsApp settings'),
            false,
        );
    }

    public function test_whatsapp_link_can_use_explicit_database_link_override(): void
    {
        $this->setSetting('whatsapp.link', 'https://wa.me/254744444444?text=Override');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('https://wa.me/254744444444?text=Override', false);
    }

    public function test_map_renders_database_embed_url(): void
    {
        $embed = 'https://maps.example.test/embed/cms-location';

        $this->setSetting('contact.map_enabled', '1');
        $this->setSetting('contact.map_embed', $embed);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee($embed, false);
        $response->assertDontSee('data-contact-location-fallback', false);
    }

    public function test_map_disabled_shows_fallback_location_card(): void
    {
        $this->setSetting('contact.map_enabled', '0');
        $this->setSetting('contact.address', 'CMS Fallback Address, Nairobi');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('data-contact-location-fallback', false);
        $response->assertSee('CMS Fallback Address, Nairobi', false);
        $response->assertDontSee('<iframe', false);
    }

    public function test_invalid_json_is_rejected_on_settings_update(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->put(route('admin.website.settings.footer-contact.update'), $this->footerContactPayload([
                'footer_nav' => '{not-valid-json',
            ]))
            ->assertSessionHasErrors('footer_nav');
    }

    public function test_invalid_email_is_rejected_on_settings_update(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->put(route('admin.website.settings.footer-contact.update'), $this->footerContactPayload([
                'contact_email' => 'not-an-email',
            ]))
            ->assertSessionHasErrors('contact_email');
    }

    public function test_social_links_render_only_valid_links(): void
    {
        $this->setSetting('footer.social', [
            ['label' => 'Instagram', 'href' => 'https://instagram.com/janaprints', 'icon' => 'instagram'],
            ['label' => 'Facebook', 'href' => '#', 'icon' => 'facebook'],
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('https://instagram.com/janaprints', false);
        $response->assertDontSee('aria-label="Facebook"', false);
    }

    public function test_settings_page_is_permission_protected(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.website.settings.footer-contact'))
            ->assertForbidden();
    }

    public function test_phone_and_email_hrefs_are_auto_generated_when_blank(): void
    {
        $user = $this->adminUser();

        $payload = $this->footerContactPayload([
            'contact_phone' => '+254 712 345 678',
            'contact_phone_href' => '',
            'contact_email' => 'hello@janaprints.com',
            'contact_email_href' => '',
        ]);

        $this->actingAs($user)
            ->put(route('admin.website.settings.footer-contact.update'), $payload)
            ->assertRedirect(route('admin.website.settings.footer-contact'));

        $this->assertDatabaseHas('website_settings', [
            'key' => 'contact.phone_href',
            'value' => 'tel:254712345678',
        ]);
        $this->assertDatabaseHas('website_settings', [
            'key' => 'contact.email_href',
            'value' => 'mailto:hello@janaprints.com',
        ]);
    }

    public function test_admin_settings_update_changes_rendered_public_footer(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->put(route('admin.website.settings.footer-contact.update'), $this->footerContactPayload([
                'footer_tagline' => 'Updated through admin settings form.',
                'footer_copyright' => '© {year} Jana Prints CMS. All rights reserved.',
                'footer_location_suffix' => 'Nairobi HQ',
            ]))
            ->assertRedirect(route('admin.website.settings.footer-contact'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Updated through admin settings form.', false)
            ->assertSee('Jana Prints CMS. All rights reserved.', false)
            ->assertSee('Nairobi HQ', false);
    }

    protected function setSetting(string $key, mixed $value): void
    {
        $meta = config('website_cms.settings')[$key] ?? [];
        $type = $meta['type'] ?? 'string';

        if ($type === 'json' && is_array($value)) {
            $stored = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } elseif ($type === 'boolean') {
            $stored = $value ? '1' : '0';
        } else {
            $stored = (string) $value;
        }

        WebsiteSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => $meta['group'] ?? 'contact',
                'type' => $type,
                'value' => $stored,
                'is_active' => true,
            ],
        );

        app(WebsiteSettingsService::class)->clearCache();
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
