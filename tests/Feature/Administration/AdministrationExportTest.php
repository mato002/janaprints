<?php

namespace Tests\Feature\Administration;

use App\Enums\IntegrationEmailProvider;
use App\Models\Integrations\IntegrationEmailSetting;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Integrations\Concerns\CreatesIntegrationTenant;
use Tests\TestCase;

class AdministrationExportTest extends TestCase
{
    use CreatesIntegrationTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_email_settings_index_renders_server_export_links(): void
    {
        [$company, $branch, $user] = $this->integrationTenant(['integrations.view']);
        $this->actingAsTenant($user, $company, $branch);

        $this->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.integrations.email.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee(__('Export CSV'), false)
            ->assertSee('email-providers', false);
    }

    public function test_email_providers_export_downloads_csv(): void
    {
        [$company, $branch, $user] = $this->integrationTenant(['integrations.view']);
        $this->actingAsTenant($user, $company, $branch);

        IntegrationEmailSetting::query()->create([
            'company_id' => $company->id,
            'provider' => IntegrationEmailProvider::Smtp,
            'from_name' => 'Jana Prints',
            'from_email' => 'noreply@janaprints.test',
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.administration.exports', [
            'listing' => 'email-providers',
            'format' => 'csv',
        ]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertStringContainsString('noreply@janaprints.test', $response->streamedContent());
    }

    public function test_activity_logs_export_requires_permission(): void
    {
        [$company, $branch, $user] = $this->integrationTenant(['integrations.view']);
        $this->actingAsTenant($user, $company, $branch);

        $this->get(route('admin.administration.exports', [
            'listing' => 'activity-logs',
            'format' => 'csv',
        ]))->assertForbidden();
    }
}
