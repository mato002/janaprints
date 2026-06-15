<?php

namespace App\Services\EmailIdentity;

use App\Models\EmailIdentity\CorporateMailbox;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Str;

class CorporateEmailGeneratorService
{
    public function preview(string $firstName, string $lastName): string
    {
        $localPart = $this->buildLocalPart($firstName, $lastName);
        $domain = $this->mailDomain();

        return "{$localPart}@{$domain}";
    }

    public function generate(string $firstName, string $lastName, ?int $excludeEmployeeId = null): string
    {
        $localPart = $this->buildLocalPart($firstName, $lastName);
        $domain = $this->mailDomain();

        return $this->ensureUnique("{$localPart}@{$domain}", $excludeEmployeeId);
    }

    public function buildLocalPart(string $firstName, string $lastName): string
    {
        $first = $this->normalizeNamePart($firstName);
        $last = $this->normalizeNamePart($lastName);

        if ($first === '' && $last === '') {
            return 'employee';
        }

        if ($first === '') {
            return $last;
        }

        if ($last === '') {
            return $first;
        }

        return "{$first}.{$last}";
    }

    protected function normalizeNamePart(string $value): string
    {
        return Str::slug(Str::lower(trim($value)), '');
    }

    protected function ensureUnique(string $baseEmail, ?int $excludeEmployeeId): string
    {
        [$localPart, $domain] = explode('@', $baseEmail, 2);
        $suffix = 0;

        while (true) {
            $candidateLocal = $suffix === 0 ? $localPart : "{$localPart}{$suffix}";
            $candidate = "{$candidateLocal}@{$domain}";

            if (! $this->emailTaken($candidate, $excludeEmployeeId)) {
                return $candidate;
            }

            $suffix++;
        }
    }

    protected function emailTaken(string $email, ?int $excludeEmployeeId): bool
    {
        $normalized = Str::lower($email);

        $employeeExists = Employee::query()
            ->whereRaw('LOWER(corporate_email) = ?', [$normalized])
            ->when($excludeEmployeeId, fn ($query) => $query->where('id', '!=', $excludeEmployeeId))
            ->exists();

        if ($employeeExists) {
            return true;
        }

        if (CorporateMailbox::query()->whereRaw('LOWER(email_address) = ?', [$normalized])->exists()) {
            return true;
        }

        return User::query()->whereRaw('LOWER(email) = ?', [$normalized])->exists();
    }

    protected function mailDomain(): string
    {
        return Str::lower((string) config('mailboxes.domain'));
    }
}
