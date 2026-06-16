<?php

namespace Tests\Unit\Support\Platform;

use App\Support\Platform\SettingsControlCenterPresenter;
use App\Support\Platform\SettingsRegistry;
use App\Support\Platform\SystemSettingsManager;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsControlCenterPresenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_hub_payload_groups_cards_by_business_domain(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        $presenter = new SettingsControlCenterPresenter(
            app(SettingsRegistry::class),
            app(SystemSettingsManager::class),
        );

        $payload = $presenter->hub($company->id, null);

        $this->assertSame(43, $payload['summary']['total_areas']);
        $this->assertCount(7, $payload['domains']);
        $this->assertSame('Organization', $payload['domains'][0]['label']);
        $this->assertSame('Roles & Access', $payload['domains'][0]['cards'][4]['title']);
        $this->assertSame('incomplete', $payload['domains'][0]['cards'][5]['status']);
    }

    public function test_registry_cards_expose_status_labels_without_completion_counts(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        $presenter = new SettingsControlCenterPresenter(
            app(SettingsRegistry::class),
            app(SystemSettingsManager::class),
        );

        $payload = $presenter->hub($company->id, null);
        $quotations = collect($payload['domains'])
            ->firstWhere('slug', 'sales')['cards'][1];

        $this->assertSame('Configured', $quotations['statusLabel']);
        $this->assertSame('success', $quotations['statusVariant']);
        $this->assertArrayNotHasKey('statusDetail', $quotations);
    }

    public function test_hub_payload_includes_filters_and_flat_cards(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        $presenter = new SettingsControlCenterPresenter(
            app(SettingsRegistry::class),
            app(SystemSettingsManager::class),
        );

        $payload = $presenter->hub($company->id, null);

        $this->assertCount(8, $payload['filters']);
        $this->assertSame('all', $payload['filters'][0]['slug']);
        $this->assertCount(43, $payload['cards']);
        $this->assertSame('organization', $payload['cards'][0]['domain_slug']);
    }
}
