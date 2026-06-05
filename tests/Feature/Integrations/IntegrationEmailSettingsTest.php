<?php

namespace Tests\Feature\Integrations;

use App\Enums\IntegrationEmailProvider;
use App\Models\ActivityLog;
use App\Models\Integrations\IntegrationEmailSetting;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Integrations\Concerns\CreatesIntegrationTenant;

class IntegrationEmailSettingsTest extends IntegrationTestCase
{
    use CreatesIntegrationTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_email_settings_create_update_and_test_connection(): void
    {
        Mail::fake();
        [$company, $branch, $user] = $this->integrationTenant(['integrations.email.manage']);
        $this->actingAsTenant($user, $company, $branch);

        $this->post(route('admin.integrations.email.store'), [
            'provider' => IntegrationEmailProvider::Smtp->value,
            'from_name' => 'Jana Prints',
            'from_email' => 'noreply@janaprints.test',
            'smtp_host' => 'smtp.test.local',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'user',
            'smtp_password' => 'secret-pass',
        ])->assertRedirect();

        $setting = IntegrationEmailSetting::query()->first();
        $this->assertNotNull($setting);
        $this->assertEquals('Jana Prints', $setting->from_name);
        $this->assertNotEquals('secret-pass', $setting->getAttributes()['smtp_password'] ?? '');
        $this->assertEquals('secret-pass', $setting->smtp_password);

        $this->put(route('admin.integrations.email.update', $setting), [
            'provider' => IntegrationEmailProvider::Smtp->value,
            'from_name' => 'Jana Prints ERP',
            'from_email' => 'erp@janaprints.test',
            'smtp_host' => 'smtp.test.local',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'user',
        ])->assertRedirect();

        $setting->refresh();
        $this->assertEquals('Jana Prints ERP', $setting->from_name);

        $this->post(route('admin.integrations.email.activate', $setting))->assertRedirect();
        $setting->refresh();
        $this->assertTrue($setting->is_active);

        $this->assertTrue(
            ActivityLog::query()->where('model_type', IntegrationEmailSetting::class)->exists()
        );
    }
}
