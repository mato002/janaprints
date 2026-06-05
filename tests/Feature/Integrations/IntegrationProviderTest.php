<?php

namespace Tests\Feature\Integrations;

use App\Enums\IntegrationProviderStatus;
use App\Models\Integrations\IntegrationProvider;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Integrations\Concerns\CreatesIntegrationTenant;

class IntegrationProviderTest extends IntegrationTestCase
{
    use CreatesIntegrationTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_provider_connect_and_disconnect(): void
    {
        [$company, $branch, $user] = $this->integrationTenant(['integrations.providers.manage']);
        $this->actingAsTenant($user, $company, $branch);

        $this->get(route('admin.integrations.providers.index'))->assertOk();

        $provider = IntegrationProvider::query()
            ->where('company_id', $company->id)
            ->where('provider_key', 'stripe')
            ->first();

        $this->assertNotNull($provider);

        $this->post(route('admin.integrations.providers.connect', $provider), [
            'api_key' => 'sk_test_12345',
            'client_id' => 'stripe-client',
        ])->assertRedirect();

        $provider->refresh();
        $this->assertEquals(IntegrationProviderStatus::Connected, $provider->status);
        $this->assertNotNull($provider->config);

        $this->post(route('admin.integrations.providers.disconnect', $provider))->assertRedirect();
        $provider->refresh();
        $this->assertEquals(IntegrationProviderStatus::Disconnected, $provider->status);
        $this->assertNull($provider->config);
    }
}
