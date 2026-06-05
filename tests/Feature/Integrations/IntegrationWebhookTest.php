<?php

namespace Tests\Feature\Integrations;

use App\Enums\IntegrationWebhookEvent;
use App\Enums\IntegrationWebhookStatus;
use App\Models\Integrations\IntegrationWebhook;
use App\Models\Integrations\IntegrationWebhookDelivery;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Integrations\Concerns\CreatesIntegrationTenant;

class IntegrationWebhookTest extends IntegrationTestCase
{
    use CreatesIntegrationTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_webhook_create_retry_and_disable(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);

        [$company, $branch, $user] = $this->integrationTenant(['integrations.webhooks.manage']);
        $this->actingAsTenant($user, $company, $branch);

        $this->post(route('admin.integrations.webhooks.store'), [
            'name' => 'CRM Events',
            'endpoint_url' => 'https://hooks.test/crm',
            'event_types' => [IntegrationWebhookEvent::CustomerCreated->value],
            'status' => IntegrationWebhookStatus::Active->value,
            'retry_count' => 3,
        ])->assertRedirect();

        $webhook = IntegrationWebhook::query()->first();
        $this->assertNotNull($webhook);

        $this->post(route('admin.integrations.webhooks.test', $webhook))->assertRedirect();
        $this->assertGreaterThan(0, IntegrationWebhookDelivery::query()->count());

        $delivery = IntegrationWebhookDelivery::query()->first();
        $delivery->update(['status' => 'failed']);

        Http::fake(['*' => Http::response('ok', 200)]);
        $this->post(route('admin.integrations.webhooks.retry', [$webhook, $delivery]))->assertRedirect();

        $this->post(route('admin.integrations.webhooks.disable', $webhook))->assertRedirect();
        $webhook->refresh();
        $this->assertEquals(IntegrationWebhookStatus::Disabled, $webhook->status);
    }
}
