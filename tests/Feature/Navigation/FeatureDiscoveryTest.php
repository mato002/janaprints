<?php

namespace Tests\Feature\Navigation;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Discovery\FeatureRegistry;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_feature_registry_indexes_form_controls(): void
    {
        $this->actingAs($this->companyAdmin());

        $matches = app(FeatureRegistry::class)->search('form controls');

        $this->assertNotEmpty($matches);

        $formControls = collect($matches)->first(fn (array $entry) => $entry['label'] === 'Form Controls');

        $this->assertNotNull($formControls);
        $this->assertSame('administration', $formControls['module_key']);
        $this->assertStringContainsString('Configuration', $formControls['path']);
        $this->assertSame('admin.settings.forms.index', $formControls['route']);
        $this->assertNotEmpty($formControls['url']);
        $this->assertContains('form', $formControls['keywords']);
    }

    public function test_partial_keyword_search_finds_approval_and_numbering_features(): void
    {
        $this->actingAs($this->companyAdmin());

        $registry = app(FeatureRegistry::class);

        $approvalMatches = $registry->search('approval');
        $this->assertNotEmpty($approvalMatches);
        $this->assertTrue(
            collect($approvalMatches)->contains(fn (array $entry) => str_contains(strtolower($entry['label']), 'approval')),
        );

        $numberMatches = $registry->search('number');
        $this->assertNotEmpty($numberMatches);
        $this->assertTrue(
            collect($numberMatches)->contains(fn (array $entry) => str_contains(strtolower($entry['label']), 'number')),
        );
    }

    public function test_module_scoped_search_only_returns_active_module_entries(): void
    {
        $this->actingAs($this->companyAdmin());

        $matches = app(FeatureRegistry::class)->search('branch', 'administration');

        $this->assertNotEmpty($matches);
        $this->assertTrue(
            collect($matches)->every(fn (array $entry) => $entry['module_key'] === 'administration'),
        );
    }

    public function test_command_palette_is_rendered_in_admin_layout(): void
    {
        $response = $this->actingAs($this->companyAdmin())->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('id="erp-command-palette-input"', false);
        $response->assertSee(__('Search customers, jobs, reports, settings, features…'), false);
        $response->assertSee('Form Controls', false);
    }

    public function test_module_desk_renders_workspace_search(): void
    {
        $response = $this->actingAs($this->companyAdmin())
            ->get(route('admin.workspaces.administration.section', ['section' => 'configuration']));

        $response->assertOk();
        $response->assertSee('module-workspace-search', false);
        $response->assertSee(__('Search Administration…'), false);
    }

    public function test_feature_registry_is_driven_by_workspace_configs(): void
    {
        $this->actingAs($this->companyAdmin());

        $sources = config('feature_registry.workspace_sources', []);

        $this->assertNotEmpty($sources);
        $this->assertContains('administration_workspaces', $sources);

        $index = app(FeatureRegistry::class)->index();

        $this->assertGreaterThan(20, count($index));
        $this->assertTrue(
            collect($index)->contains(fn (array $entry) => $entry['module_key'] === 'commercial'),
        );
        $this->assertTrue(
            collect($index)->contains(fn (array $entry) => $entry['module_key'] === 'supply-chain'),
        );
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }
}
