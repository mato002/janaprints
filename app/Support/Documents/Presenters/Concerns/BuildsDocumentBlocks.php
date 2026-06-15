<?php

namespace App\Support\Documents\Presenters\Concerns;

use App\Models\Company;
use App\Models\Crm\Customer;
use App\Services\Documents\DocumentSettingsService;
use App\Support\Branding\BrandingAssets;

trait BuildsDocumentBlocks
{
    protected function documentSettings(): DocumentSettingsService
    {
        return app(DocumentSettingsService::class);
    }

    protected function documentsLogoDataUri(): ?string
    {
        return app(BrandingAssets::class)->documentsLogoDataUri();
    }

    /**
     * @return array{name: string, address: ?string, phone: ?string, email: ?string, website: ?string}
     */
    protected function companyBlock(?Company $company = null): array
    {
        $config = $this->documentSettings()->company($company?->id);

        return [
            'name' => $company?->name ?? ($config['name'] ?? config('app.name')),
            'address' => $company?->address ?? ($config['address'] ?? null),
            'phone' => $company?->phone ?? ($config['phone'] ?? null),
            'email' => $company?->email ?? ($config['email'] ?? null),
            'website' => $this->normalizeWebsite($config['website'] ?? null),
        ];
    }

    /**
     * @return array{name: ?string, company: ?string, phone: ?string, email: ?string, address: ?string, code: ?string, compact?: bool}
     */
    protected function customerBlock(?Customer $customer, bool $compact = false): array
    {
        if (! $customer) {
            return [
                'name' => null,
                'company' => null,
                'phone' => null,
                'email' => null,
                'address' => null,
                'code' => null,
            ];
        }

        if ($compact) {
            return [
                'name' => null,
                'company' => $customer->company_name ?: $customer->contact_person,
                'phone' => null,
                'email' => null,
                'address' => null,
                'code' => null,
                'compact' => true,
            ];
        }

        $address = collect([
            $customer->physical_address,
            $customer->postal_address,
            $customer->city,
        ])->filter()->implode(', ');

        return [
            'name' => $customer->contact_person,
            'company' => $customer->company_name,
            'phone' => $customer->phone ?? $customer->alternative_phone,
            'email' => $customer->email,
            'address' => $address !== '' ? $address : null,
            'code' => $customer->customer_code,
        ];
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    protected function paymentDetailsBlock(): array
    {
        return $this->invoicePaymentDetailsBlock(compact: false);
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    protected function invoicePaymentDetailsBlock(bool $compact = false, ?int $companyId = null): array
    {
        $payment = $this->documentSettings()->payment($companyId);
        $lines = [];

        if (! empty($payment['mpesa_paybill'])) {
            $lines[] = [
                'label' => $compact ? __('M-Pesa Paybill') : __('Lipa na M-Pesa Paybill'),
                'value' => (string) $payment['mpesa_paybill'],
            ];

            if (! empty($payment['mpesa_account'])) {
                $lines[] = [
                    'label' => __('Account Number'),
                    'value' => (string) $payment['mpesa_account'],
                ];
            }
        }

        if (! empty($payment['cheque_payable_to'])) {
            $lines[] = [
                'label' => __('Cheques Payable To'),
                'value' => (string) $payment['cheque_payable_to'],
            ];
        }

        if (! empty($payment['bank_name'])) {
            $lines[] = [
                'label' => __('Bank Name'),
                'value' => (string) $payment['bank_name'],
            ];
        }

        if (! empty($payment['bank_branch'])) {
            $lines[] = [
                'label' => __('Branch'),
                'value' => (string) $payment['bank_branch'],
            ];
        }

        if (! empty($payment['bank_account_name'])) {
            $lines[] = [
                'label' => __('Account Name'),
                'value' => (string) $payment['bank_account_name'],
            ];
        }

        if (! empty($payment['bank_account'])) {
            $lines[] = [
                'label' => __('Account Number'),
                'value' => (string) $payment['bank_account'],
            ];
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    protected function paymentFooterBlock(?int $companyId = null): array
    {
        $payment = $this->documentSettings()->payment($companyId);
        $lines = [];

        if (! empty($payment['mpesa_paybill'])) {
            $mpesa = __('Lipa Na M-Pesa - Paybill No. :paybill', ['paybill' => $payment['mpesa_paybill']]);

            if (! empty($payment['mpesa_account'])) {
                $mpesa .= ' '.__('Account Number: :account', ['account' => $payment['mpesa_account']]);
            }

            if (! empty($payment['cheque_payable_to'])) {
                $mpesa .= ' | '.__('Cheques Payable to : :name', ['name' => $payment['cheque_payable_to']]);
            }

            $lines[] = $mpesa;
        }

        $bankParts = array_filter([
            $payment['bank_name'] ?? null,
            $payment['bank_branch'] ?? null,
            ! empty($payment['bank_account_name'])
                ? __('Acc. Name: :name', ['name' => $payment['bank_account_name']])
                : null,
            ! empty($payment['bank_account'])
                ? __('Acc. No: :number', ['number' => $payment['bank_account']])
                : null,
        ]);

        if ($bankParts !== []) {
            $lines[] = __('Bank Transfer : :details', ['details' => implode(', ', $bankParts)]);
        }

        return $lines;
    }

    protected function documentFooterBlock(?int $companyId = null): array
    {
        return [
            'thanks' => $this->documentSettings()->footerThanks($companyId),
            'system' => $this->documentSettings()->footerSystem($companyId),
        ];
    }

    protected function documentTaxLabel(?int $companyId = null): string
    {
        return $this->documentSettings()->taxLabel($companyId);
    }

    protected function documentTerm(string $type, ?int $companyId = null): ?string
    {
        return $this->documentSettings()->term($type, $companyId);
    }

    /**
     * @return array{label: string, value: string}
     */
    protected function headerHighlightBlock(string $label, string $value): array
    {
        return [
            'label' => $label,
            'value' => $value,
        ];
    }

    protected function formatMoney(float $amount, string $currency): string
    {
        return $currency.' '.number_format($amount, 2);
    }

    /**
     * @param  list<array{label: string, value: ?string}>  $rows
     * @return list<array{label: string, value: string}>
     */
    protected function filterMetaRows(array $rows): array
    {
        return collect($rows)
            ->filter(fn (array $row) => filled($row['value'] ?? null))
            ->map(fn (array $row) => [
                'label' => $row['label'],
                'value' => (string) $row['value'],
            ])
            ->values()
            ->all();
    }

    protected function normalizeWebsite(?string $website): ?string
    {
        if ($website === null || $website === '') {
            return null;
        }

        return str_replace(['https://', 'http://'], '', rtrim($website, '/'));
    }

}
