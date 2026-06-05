<?php

namespace App\Support\Integrations;

class IntegrationProviderCatalog
{
    /**
     * @return list<array{category: string, provider_key: string, name: string, description: string}>
     */
    public static function definitions(): array
    {
        return [
            ['category' => 'google', 'provider_key' => 'google_workspace', 'name' => 'Google Workspace', 'description' => 'SMTP, Calendar, Drive'],
            ['category' => 'microsoft', 'provider_key' => 'microsoft_365', 'name' => 'Microsoft 365', 'description' => 'Outlook, OneDrive'],
            ['category' => 'whatsapp', 'provider_key' => 'whatsapp_meta', 'name' => 'WhatsApp (Meta Cloud API)', 'description' => 'Meta Cloud API'],
            ['category' => 'whatsapp', 'provider_key' => 'whatsapp_twilio', 'name' => 'WhatsApp (Twilio)', 'description' => 'Twilio WhatsApp'],
            ['category' => 'accounting', 'provider_key' => 'quickbooks', 'name' => 'QuickBooks', 'description' => 'Accounting sync'],
            ['category' => 'accounting', 'provider_key' => 'xero', 'name' => 'Xero', 'description' => 'Accounting sync'],
            ['category' => 'payments', 'provider_key' => 'mpesa', 'name' => 'M-Pesa', 'description' => 'Mobile payments'],
            ['category' => 'payments', 'provider_key' => 'stripe', 'name' => 'Stripe', 'description' => 'Card payments'],
            ['category' => 'payments', 'provider_key' => 'flutterwave', 'name' => 'Flutterwave', 'description' => 'Payment gateway'],
            ['category' => 'shipping', 'provider_key' => 'fargo_courier', 'name' => 'Fargo Courier', 'description' => 'Courier integration'],
            ['category' => 'shipping', 'provider_key' => 'g4s', 'name' => 'G4S', 'description' => 'Courier integration'],
            ['category' => 'shipping', 'provider_key' => 'custom_courier', 'name' => 'Custom Courier', 'description' => 'Custom courier connector'],
        ];
    }

    public function ensureForCompany(int $companyId): void
    {
        foreach (self::definitions() as $definition) {
            \App\Models\Integrations\IntegrationProvider::query()->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'provider_key' => $definition['provider_key'],
                ],
                [
                    'category' => $definition['category'],
                    'name' => $definition['name'],
                    'status' => \App\Enums\IntegrationProviderStatus::Disconnected,
                ],
            );
        }
    }
}
