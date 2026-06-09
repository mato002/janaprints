<?php

namespace App\Support\Integrations;

use App\Models\Integrations\IntegrationWhatsappSetting;
use Illuminate\Support\Facades\Http;

class WhatsappSettingsService
{
    public function __construct(
        protected IntegrationAuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function save(IntegrationWhatsappSetting $setting, array $data, int $userId): IntegrationWhatsappSetting
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

    public function activate(IntegrationWhatsappSetting $setting, int $userId): void
    {
        IntegrationWhatsappSetting::query()
            ->where('company_id', $setting->company_id)
            ->where('id', '!=', $setting->id)
            ->update(['is_active' => false]);

        $old = $setting->getOriginal();
        $setting->update(['is_active' => true, 'updated_by' => $userId]);
        $this->audit->logChange($setting, 'provider_activated', $old, $setting->getAttributes());
    }

    public function deactivate(IntegrationWhatsappSetting $setting, int $userId): void
    {
        $old = $setting->getOriginal();
        $setting->update(['is_active' => false, 'updated_by' => $userId]);
        $this->audit->logChange($setting, 'provider_deactivated', $old, $setting->getAttributes());
    }

    public function sendTestMessage(IntegrationWhatsappSetting $setting, string $phone): array
    {
        try {
            $url = filled($setting->api_url)
                ? (string) $setting->api_url
                : "https://graph.facebook.com/v19.0/{$setting->phone_number_id}/messages";

            $response = Http::timeout(15)
                ->withToken((string) $setting->api_key)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => ltrim($phone, '+'),
                    'type' => 'text',
                    'text' => ['body' => __('Jana Prints ERP — test WhatsApp message')],
                ]);

            if ($response->successful()) {
                $setting->increment('messages_sent_today');
                $setting->increment('messages_sent_month');
                $setting->update(['health_status' => 'healthy', 'last_health_check_at' => now()]);

                return ['success' => true, 'message' => __('Test WhatsApp message dispatched.')];
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
}
