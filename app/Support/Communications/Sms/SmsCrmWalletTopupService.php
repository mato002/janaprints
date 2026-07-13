<?php

namespace App\Support\Communications\Sms;

use App\Models\Communications\SmsWalletTopup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SmsCrmWalletTopupService
{
    public function __construct(
        protected PradytecSmsWalletClient $client,
        protected SmsCreditService $credits,
    ) {}

    public function configured(): bool
    {
        return $this->client->configured();
    }

    /**
     * @return array{enabled: bool, error?: string, currency: string, min_amount: float, max_amount: float}
     */
    public function uiConfig(): array
    {
        if (! $this->configured()) {
            return [
                'enabled' => false,
                'error' => __('Configure PRADYTEC_SMS_API_KEY and PRADYTEC_SMS_CLIENT_ID to top up via M-Pesa.'),
                'currency' => (string) config('pradytec_sms.currency', 'KES'),
                'min_amount' => (float) config('pradytec_sms.min_topup_amount', 10),
                'max_amount' => (float) config('pradytec_sms.max_topup_amount', 50000),
            ];
        }

        return [
            'enabled' => true,
            'currency' => (string) config('pradytec_sms.currency', 'KES'),
            'min_amount' => (float) config('pradytec_sms.min_topup_amount', 10),
            'max_amount' => (float) config('pradytec_sms.max_topup_amount', 50000),
        ];
    }

    /**
     * @return array{ok: bool, error?: string, balance?: float, currency?: string, price_per_unit?: float, source: string}
     */
    public function liveBalance(int $companyId): array
    {
        if ($this->configured()) {
            $remote = $this->client->balance();

            if ($remote['ok'] ?? false) {
                return [
                    'ok' => true,
                    'balance' => (float) ($remote['balance'] ?? 0),
                    'currency' => (string) ($remote['currency'] ?? config('pradytec_sms.currency')),
                    'price_per_unit' => (float) ($remote['price_per_unit'] ?? 1),
                    'source' => 'crm',
                ];
            }
        }

        $local = $this->credits->balanceFor($companyId);

        return [
            'ok' => true,
            'balance' => (float) $local->remaining_credits,
            'currency' => (string) config('pradytec_sms.currency', 'KES'),
            'price_per_unit' => (float) $local->cost_per_sms,
            'source' => 'local',
            'error' => $this->configured() ? __('CRM balance unavailable; showing local ledger.') : null,
        ];
    }

    public function initiate(int $companyId, float $amount, string $phone, User $actor, ?string $notes = null): SmsWalletTopup
    {
        if (! $this->configured()) {
            throw ValidationException::withMessages([
                'sms_topup' => __('Pradytec SMS wallet is not configured.'),
            ]);
        }

        $result = $this->client->initiateTopup($amount, $phone);

        if (! ($result['ok'] ?? false)) {
            throw ValidationException::withMessages([
                'sms_topup' => (string) ($result['error'] ?? __('Could not initiate M-Pesa top-up.')),
            ]);
        }

        return SmsWalletTopup::query()->create([
            'company_id' => $companyId,
            'requested_by' => $actor->id,
            'reference' => 'JP-SMS-'.Str::upper(Str::random(10)),
            'provider_transaction_id' => $result['transaction_id'] ?: null,
            'checkout_request_id' => $result['checkout_request_id'] ?: null,
            'amount' => (float) ($result['amount'] ?? $amount),
            'phone_number' => (string) ($result['phone_number'] ?? $phone),
            'status' => 'pending',
            'message' => (string) ($result['message'] ?? __('Please check your phone for the M-Pesa prompt.')),
            'meta' => array_filter([
                'notes' => $notes,
                'initiated_at' => now()->toIso8601String(),
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function status(string $reference, int $companyId, ?User $actor = null): array
    {
        $topup = SmsWalletTopup::query()
            ->where('company_id', $companyId)
            ->where('reference', $reference)
            ->first();

        if (! $topup) {
            return ['ok' => false, 'status' => 'unknown', 'message' => __('Payment request not found.'), 'terminal' => true];
        }

        if ($actor && $topup->requested_by && (int) $topup->requested_by !== (int) $actor->id && ! $actor->can('communications.sms.audit')) {
            return ['ok' => false, 'status' => 'forbidden', 'message' => __('You cannot view this payment request.'), 'terminal' => true];
        }

        if ($topup->isTerminal()) {
            return $this->payload($topup);
        }

        $providerTxnId = trim((string) $topup->provider_transaction_id);

        if ($providerTxnId === '') {
            if ($this->timedOut($topup)) {
                $topup->update([
                    'status' => 'expired',
                    'message' => __('M-Pesa prompt expired before payment completed.'),
                    'completed_at' => now(),
                ]);

                return $this->payload($topup->fresh() ?? $topup);
            }

            return $this->payload($topup, pendingMessage: __('Waiting for provider transaction…'));
        }

        $provider = $this->client->topupStatus($providerTxnId);

        if (! ($provider['ok'] ?? false)) {
            if ($this->timedOut($topup)) {
                $topup->update([
                    'status' => 'failed',
                    'message' => (string) ($provider['error'] ?? __('Could not confirm payment.')),
                    'completed_at' => now(),
                ]);

                return $this->payload($topup->fresh() ?? $topup);
            }

            return [
                'ok' => true,
                'reference' => $topup->reference,
                'status' => 'pending',
                'message' => (string) ($provider['error'] ?? __('Checking payment status…')),
                'terminal' => false,
                'next_poll_seconds' => 5,
            ];
        }

        $transaction = $provider['transaction'] ?? [];
        $status = Str::lower((string) ($transaction['status'] ?? 'pending'));

        if (in_array($status, ['completed', 'success', 'paid'], true)) {
            $this->completeTopup($topup, $transaction);

            return $this->payload($topup->fresh() ?? $topup);
        }

        if (in_array($status, ['failed', 'cancelled', 'canceled', 'timeout', 'expired'], true)) {
            $topup->update([
                'status' => $status === 'expired' || $status === 'timeout' ? 'expired' : 'failed',
                'message' => (string) ($transaction['message'] ?? $transaction['result_desc'] ?? __('Payment was not completed.')),
                'completed_at' => now(),
                'meta' => array_merge($topup->meta ?? [], ['provider' => $transaction]),
            ]);

            return $this->payload($topup->fresh() ?? $topup);
        }

        if ($this->timedOut($topup)) {
            $topup->update([
                'status' => 'expired',
                'message' => __('M-Pesa prompt timed out or was cancelled on your phone.'),
                'completed_at' => now(),
                'meta' => array_merge($topup->meta ?? [], ['provider' => $transaction]),
            ]);

            return $this->payload($topup->fresh() ?? $topup);
        }

        return $this->payload(
            $topup,
            pendingMessage: $status === 'processing'
                ? __('Processing M-Pesa payment…')
                : __('Waiting for M-Pesa confirmation on your phone…'),
        );
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    protected function completeTopup(SmsWalletTopup $topup, array $transaction): void
    {
        DB::transaction(function () use ($topup, $transaction) {
            /** @var SmsWalletTopup $locked */
            $locked = SmsWalletTopup::query()->lockForUpdate()->findOrFail($topup->id);

            if ($locked->status === 'completed' && $locked->local_credit_applied) {
                return;
            }

            $amount = (float) ($transaction['amount'] ?? $locked->amount);
            $receipt = (string) ($transaction['mpesa_receipt'] ?? $locked->provider_transaction_id ?? $locked->reference);
            $balanceAfter = isset($transaction['balance']) ? (float) $transaction['balance'] : null;

            if (! $locked->local_credit_applied && $amount > 0) {
                $actor = $locked->requester;
                $this->credits->purchase(
                    (int) $locked->company_id,
                    $amount,
                    $actor,
                    __('CRM M-Pesa top-up :receipt', ['receipt' => $receipt]),
                );
            }

            $locked->update([
                'status' => 'completed',
                'mpesa_receipt' => $receipt !== '' ? $receipt : $locked->mpesa_receipt,
                'provider_balance_after' => $balanceAfter,
                'local_credit_applied' => true,
                'message' => __('Payment confirmed. SMS credits updated.'),
                'completed_at' => now(),
                'meta' => array_merge($locked->meta ?? [], [
                    'provider' => $transaction,
                    'local_wallet_topup_applied_at' => now()->toIso8601String(),
                ]),
            ]);
        });
    }

    protected function timedOut(SmsWalletTopup $topup): bool
    {
        if (! $topup->created_at) {
            return false;
        }

        $timeout = max(60, (int) config('pradytec_sms.pending_timeout_seconds', 120));

        return $topup->created_at->diffInSeconds(now()) >= $timeout;
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(SmsWalletTopup $topup, ?string $pendingMessage = null): array
    {
        $terminal = $topup->isTerminal();

        return [
            'ok' => true,
            'reference' => $topup->reference,
            'status' => $topup->status,
            'message' => $terminal
                ? (string) ($topup->message ?: __('Payment update received.'))
                : (string) ($pendingMessage ?: $topup->message ?: __('Waiting for M-Pesa confirmation…')),
            'amount' => (float) $topup->amount,
            'phone' => $topup->phone_number,
            'terminal' => $terminal,
            'next_poll_seconds' => $terminal ? 0 : 5,
            'provider_balance_after' => $topup->provider_balance_after !== null
                ? (float) $topup->provider_balance_after
                : null,
        ];
    }
}
