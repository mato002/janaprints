<?php

namespace App\Services\EmailIdentity;

class MailboxAddressResolver
{
    /**
     * @return list<string>
     */
    public static function purposes(): array
    {
        return [
            'support',
            'info',
            'hr',
            'accounts',
            'production',
            'sales',
            'notifications',
            'noreply',
            'billing',
        ];
    }

    public function resolve(string $purpose): ?string
    {
        $purpose = strtolower(trim($purpose));

        if ($purpose === '') {
            return $this->fallback();
        }

        $department = config("mailboxes.department.{$purpose}");
        if (filled($department)) {
            return (string) $department;
        }

        $system = config("mailboxes.system.{$purpose}");
        if (filled($system)) {
            return (string) $system;
        }

        return $this->fallback();
    }

    public function fallback(): ?string
    {
        return config('mail.from.address')
            ?: config('mailboxes.department.info')
            ?: config('mailboxes.system.noreply');
    }
}
