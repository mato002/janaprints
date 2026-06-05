<?php

namespace Tests\Feature\Integrations;

use App\Enums\IntegrationApiKeyEnvironment;
use App\Models\ActivityLog;
use App\Models\Integrations\IntegrationApiKey;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Integrations\Concerns\CreatesIntegrationTenant;

class IntegrationApiKeyTest extends IntegrationTestCase
{
    use CreatesIntegrationTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_api_key_generate_revoke_and_permissions(): void
    {
        [$company, $branch, $user] = $this->integrationTenant(['integrations.api.manage']);
        $this->actingAsTenant($user, $company, $branch);

        $response = $this->post(route('admin.integrations.api-keys.store'), [
            'name' => 'ERP Mobile App',
            'description' => 'Mobile client access',
            'environment' => IntegrationApiKeyEnvironment::Production->value,
            'permissions' => ['quotations.view', 'customers.view'],
        ]);

        $response->assertRedirect();
        $this->assertNotNull(session('generated_secret'));

        $apiKey = IntegrationApiKey::query()->first();
        $this->assertNotNull($apiKey);
        $this->assertTrue($apiKey->is_active);
        $this->assertEquals(['quotations.view', 'customers.view'], $apiKey->permissions);

        $this->post(route('admin.integrations.api-keys.regenerate', $apiKey))
            ->assertRedirect()
            ->assertSessionHas('generated_secret');

        $this->post(route('admin.integrations.api-keys.disable', $apiKey))->assertRedirect();
        $apiKey->refresh();
        $this->assertFalse($apiKey->is_active);

        $this->delete(route('admin.integrations.api-keys.revoke', $apiKey))->assertRedirect();
        $apiKey->refresh();
        $this->assertNotNull($apiKey->revoked_at);

        $this->assertTrue(
            ActivityLog::query()->where('model_type', IntegrationApiKey::class)->where('action', 'revoked')->exists()
        );
    }
}
