<?php

namespace App\Services\EmailIdentity;

class DepartmentMailboxCatalogService
{
    public function __construct(
        protected MailboxAddressResolver $mailboxAddresses,
    ) {}

    /**
     * @return list<array{
     *     purpose: string,
     *     label: string,
     *     address: ?string,
     *     configured: bool,
     *     used_fallback: bool,
     *     recommended_use: string
     * }>
     */
    public function entries(): array
    {
        $fallback = $this->mailboxAddresses->fallback();
        $entries = [];

        foreach (MailboxAddressResolver::purposes() as $purpose) {
            $direct = $this->directAddressFor($purpose);
            $configured = filled($direct);
            $address = $configured ? $direct : $fallback;
            $catalog = config("email_senders.catalog.{$purpose}", []);

            $entries[] = [
                'purpose' => $purpose,
                'label' => (string) ($catalog['label'] ?? ucfirst($purpose)),
                'address' => $address,
                'configured' => $configured,
                'used_fallback' => ! $configured && filled($fallback),
                'recommended_use' => (string) ($catalog['recommended_use'] ?? ''),
            ];
        }

        return $entries;
    }

    protected function directAddressFor(string $purpose): ?string
    {
        $department = config("mailboxes.department.{$purpose}");
        if (filled($department)) {
            return (string) $department;
        }

        $system = config("mailboxes.system.{$purpose}");
        if (filled($system)) {
            return (string) $system;
        }

        return null;
    }
}
