<?php

namespace Tests\Feature\Integrations;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Integrations\Concerns\CreatesIntegrationTenant;

class IntegrationPermissionsTest extends IntegrationTestCase
{
    use CreatesIntegrationTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_unauthorized_users_are_blocked(): void
    {
        [$company, $branch, $user] = $this->integrationTenant(['crm.customers.view']);
        $this->actingAsTenant($user, $company, $branch);

        $this->get(route('admin.integrations.email.index'))->assertForbidden();
        $this->get(route('admin.integrations.sms.index'))->assertForbidden();
        $this->get(route('admin.integrations.api-keys.index'))->assertForbidden();
        $this->get(route('admin.integrations.webhooks.index'))->assertForbidden();
        $this->get(route('admin.integrations.providers.index'))->assertForbidden();
    }

    public function test_view_permission_allows_read_only_access(): void
    {
        [$company, $branch, $user] = $this->integrationTenant(['integrations.view']);
        $this->actingAsTenant($user, $company, $branch);

        $this->get(route('admin.integrations.email.index'))->assertOk();
        $this->get(route('admin.integrations.email.create'))->assertForbidden();
    }
}
