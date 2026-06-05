<?php

namespace App\Support\Integrations;

use App\Models\Integrations\IntegrationSmsSetting;
use Illuminate\Support\Facades\Http;

class SmsSettingsService
{
    public function __construct(
        protected IntegrationAuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function save(IntegrationSmsSetting $setting, array $data, int $userId): IntegrationSmsSetting
    {
        $old = $setting->exists ? $setting->getOriginal() : [];
        $data['updated_by'] = $userId;

        if (empty($data['api_key'])) {
            unset($data['api_key']);
        }
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $setting->fill($data);
        $setting->save();

        $this->audit->logChange($setting, $setting->wasRecentlyCreated ? 'created' : 'updated', $old, $setting->getAttributes());

        return $setting;
    }

    public function activate(IntegrationSmsSetting $setting, int $userId): void
    {
        IntegrationSmsSetting::query()
            ->where('company_id', $setting->company_id)
            ->where('id', '!=', $setting->id)
            ->update(['is_active' => false]);

        $old = $setting->getOriginal();
        $setting->update(['is_active' => true, 'updated_by' => $userId]);
        $this->audit->logChange($setting, 'provider_activated', $old, $setting->getAttributes());
    }

    public function deactivate(IntegrationSmsSetting $setting, int $userId): void
    {
        $old = $setting->getOriginal();
        $setting->update(['is_active' => false, 'updated_by' => $userId]);
        $this->audit->logChange($setting, 'provider_deactivated', $old, $setting->getAttributes());
    }

    public function verifyCredentials(IntegrationSmsSetting $setting): array
    {
        try {
            $healthy = filled($setting->api_key) || (filled($setting->username) && filled($setting->password));

            $setting->update([
                'last_health_check_at' => now(),
                'health_status' => $healthy ? 'healthy' : 'unhealthy',
            ]);

            return [
                'success' => $healthy,
                'message' => $healthy ? __('Credentials configured.') : __('Missing required credentials.'),
            ];
        } catch (\Throwable $e) {
            $setting->update([
                'last_health_check_at' => now(),
                'health_status' => 'unhealthy',
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function sendTestSms(IntegrationSmsSetting $setting, string $phone): array
    {
        try {
            if (! filled($setting->api_url)) {
                return ['success' => false, 'message' => __('API URL is required for test SMS.')];
            }

            $response = Http::timeout(15)
                ->withHeaders($this->authHeaders($setting))
                ->post($setting->api_url, [
                    'to' => $phone,
                    'message' => __('Jana Prints ERP — test SMS'),
                    'sender_id' => $setting->sender_id,
                ]);

            if ($response->successful()) {
                $setting->increment('sms_sent_today');
                $setting->increment('sms_sent_month');
                $setting->update(['health_status' => 'healthy', 'last_health_check_at' => now()]);

                return ['success' => true, 'message' => __('Test SMS dispatched.')];
            }

            $setting->increment('failed_count');
            $setting->update(['health_status' => 'unhealthy', 'last_health_check_at' => now()]);

            return ['success' => false, 'message' => $response->body()];
        } catch (\Throwable $e) {
            $setting->increment('failed_count');
            $setting->update(['health_status' => 'unhealthy', 'last_health_check_at' => now()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @return array<string, string>
     */
    protected function authHeaders(IntegrationSmsSetting $setting): array
    {
        $headers = ['Accept' => 'application/json'];

        if (filled($setting->api_key)) {
            $headers['Authorization'] = 'Bearer '.$setting->api_key;
        }

        return $headers;
    }
}
