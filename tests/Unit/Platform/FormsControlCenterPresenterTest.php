<?php

namespace Tests\Unit\Platform;

use App\Models\Company;
use App\Support\Platform\FormSettingsManager;
use App\Support\Platform\FormsControlCenterPresenter;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormsControlCenterPresenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_hub_builds_summary_categories_and_health(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $manager = app(FormSettingsManager::class);
        $forms = $manager->rows($company->id, null);

        $hub = app(FormsControlCenterPresenter::class)->hub($company->id, null, $forms);

        $this->assertSame(count(config('form_registry.forms', [])) + count(config('form_control_center.planned_forms', [])), $hub['summary']['total_forms']);
        $this->assertGreaterThan(0, $hub['summary']['active_forms']);
        $this->assertGreaterThan(0, $hub['summary']['planned_forms']);
        $this->assertGreaterThan(0, $hub['summary']['managed_fields']);
        $this->assertCount(6, $hub['categories']);
        $this->assertArrayHasKey('governance_issues', $hub['health']);
        $this->assertArrayHasKey('compliance', $hub);
        $this->assertArrayHasKey('compliance_percent', $hub['summary']);
        $this->assertArrayHasKey('governed_forms', $hub['summary']);
        $this->assertNotEmpty($hub['export_payload']['forms']);
    }
}
