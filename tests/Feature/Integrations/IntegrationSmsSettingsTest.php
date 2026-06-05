<?php

namespace Tests\Feature\Integrations;

use App\Enums\IntegrationSmsProvider;
use App\Models\Integrations\IntegrationSmsSetting;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Integrations\Concerns\CreatesIntegrationTenant;

class IntegrationSmsSettingsTest extends IntegrationTestCase
{
    use CreatesIntegrationTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_sms_settings_create_update_and_test_sms(): void
    {
        Http::fake(['*' => Http::response(['status' => 'ok'], 200)]);

        [$company, $branch, $user] = $this->integrationTenant(['integrations.sms.manage']);
        $this->actingAsTenant($user, $company, $branch);

        $this->post(route('admin.integrations.sms.store'), [
            'provider' => IntegrationSmsProvider::Twilio->value,
            'api_url' => 'https://api.sms.test/send',
            'api_key' => 'twilio-key-secret',
            'sender_id' => 'JANAPRINTS',
        ])->assertRedirect();

        $setting = IntegrationSmsSetting::query()->first();
        $this->assertNotNull($setting);
        $this->assertEquals('JANAPRINTS', $setting->sender_id);

        $this->put(route('admin.integrations.sms.update', $setting), [
            'provider' => IntegrationSmsProvider::Twilio->value,
            'api_url' => 'https://api.sms.test/send',
            'sender_id' => 'JANA',
            'callback_url' => 'https://erp.test/sms/callback',
        ])->assertRedirect();

        $setting->refresh();
        $this->assertEquals('JANA', $setting->sender_id);

        $this->post(route('admin.integrations.sms.verify', $setting))->assertRedirect();
        $setting->refresh();
        $this->assertEquals('healthy', $setting->health_status);

        $this->post(route('admin.integrations.sms.send-test', $setting), [
            'phone' => '+254700000000',
        ])->assertRedirect();

        $setting->refresh();
        $this->assertGreaterThanOrEqual(1, $setting->sms_sent_today);
    }
}
