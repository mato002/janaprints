<?php

namespace App\Support\Communications\Email;

use App\Enums\EmailAccountStatus;
use App\Enums\EmailProvider;
use App\Enums\EmailVerificationStatus;
use App\Models\Communications\EmailAccount;
use App\Services\EmailIdentity\EmailSenderResolver;
use Illuminate\Support\Collection;

class EmailAccountService
{
    public function __construct(
        protected EmailSenderResolver $senderResolver,
    ) {}

    public function defaultForCompany(int $companyId, ?int $branchId = null): ?EmailAccount
    {
        $query = EmailAccount::query()
            ->where('company_id', $companyId)
            ->where('status', EmailAccountStatus::Active);

        if ($branchId) {
            $branchAccount = (clone $query)->where('branch_id', $branchId)->where('is_default', true)->first();
            if ($branchAccount) {
                return $branchAccount;
            }
        }

        return $query->where('is_default', true)->first()
            ?? $query->orderBy('id')->first();
    }

    /**
     * @return Collection<int, EmailAccount>
     */
    public function listForCompany(int $companyId): Collection
    {
        return EmailAccount::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['branch', 'department'])
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    public function ensureDefaultAccount(int $companyId, int $userId, string $purpose = 'system_alert'): EmailAccount
    {
        $existing = $this->defaultForCompany($companyId);

        if ($existing) {
            return $this->alignWithPurpose($existing, $purpose);
        }

        $sender = $this->senderResolver->resolve($purpose);
        $fromEmail = filled($sender->address)
            ? (string) $sender->address
            : 'noreply@'.strtolower(preg_replace('/\s+/', '', config('app.name', 'janaprints'))).'.local';

        $account = EmailAccount::query()->create([
            'company_id' => $companyId,
            'branch_id' => tenant()->branchId(),
            'name' => __('Primary email'),
            'from_email' => $fromEmail,
            'from_name' => (string) config('mail.from.name', config('app.name')),
            'reply_to_email' => (string) (config('mailboxes.department.info') ?: $fromEmail),
            'reply_to_name' => (string) config('mail.from.name', config('app.name')),
            'provider' => EmailProvider::Unconfigured,
            'status' => EmailAccountStatus::Active,
            'verification_status' => EmailVerificationStatus::Pending,
            'is_default' => true,
            'created_by' => $userId,
        ]);

        return $this->alignWithPurpose($account, $purpose);
    }

    public function accountForPurpose(int $companyId, int $userId, string $purpose = 'system_alert'): EmailAccount
    {
        return $this->ensureDefaultAccount($companyId, $userId, $purpose);
    }

    public function alignWithPurpose(EmailAccount $account, string $purpose): EmailAccount
    {
        $sender = $this->senderResolver->resolve($purpose);

        if (! filled($sender->address)) {
            return $account;
        }

        $fromName = (string) config('mail.from.name', config('app.name'));
        $replyTo = (string) (config('mailboxes.department.info') ?: $sender->address);

        if ($account->from_email === $sender->address
            && $account->reply_to_email === $replyTo
            && $account->from_name === $fromName) {
            return $account;
        }

        $account->update([
            'from_email' => $sender->address,
            'from_name' => $fromName,
            'reply_to_email' => $replyTo,
            'reply_to_name' => $fromName,
        ]);

        return $account->fresh();
    }
}
