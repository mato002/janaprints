<?php

namespace App\Support\Communications\Sms;

use App\Enums\SmsCreditTransactionType;
use App\Models\Communications\SmsCreditBalance;
use App\Models\Communications\SmsCreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SmsCreditService
{
    public function balanceFor(int $companyId): SmsCreditBalance
    {
        return SmsCreditBalance::query()->firstOrCreate(
            ['company_id' => $companyId],
            [
                'opening_credits' => 1000,
                'purchased_credits' => 0,
                'used_credits' => 0,
                'remaining_credits' => 1000,
                'cost_per_sms' => 1,
            ],
        );
    }

    public function purchase(int $companyId, float $credits, ?User $actor = null, ?string $description = null): SmsCreditTransaction
    {
        return DB::transaction(function () use ($companyId, $credits, $actor, $description) {
            $balance = SmsCreditBalance::query()->lockForUpdate()->firstOrCreate(
                ['company_id' => $companyId],
                ['opening_credits' => 0, 'purchased_credits' => 0, 'used_credits' => 0, 'remaining_credits' => 0, 'cost_per_sms' => 1],
            );

            $balance->purchased_credits = (float) $balance->purchased_credits + $credits;
            $balance->remaining_credits = (float) $balance->remaining_credits + $credits;
            $balance->save();

            return $this->recordTransaction($balance, SmsCreditTransactionType::Purchase, $credits, $actor, $description);
        });
    }

    public function consume(
        int $companyId,
        float $credits,
        ?int $campaignId = null,
        ?int $messageId = null,
        ?int $branchId = null,
        ?int $departmentId = null,
        ?User $actor = null,
    ): SmsCreditTransaction {
        return DB::transaction(function () use ($companyId, $credits, $campaignId, $messageId, $branchId, $departmentId, $actor) {
            $balance = SmsCreditBalance::query()->lockForUpdate()->where('company_id', $companyId)->firstOrFail();

            if ((float) $balance->remaining_credits < $credits) {
                throw ValidationException::withMessages([
                    'credits' => __('Insufficient SMS credits.'),
                ]);
            }

            $balance->used_credits = (float) $balance->used_credits + $credits;
            $balance->remaining_credits = (float) $balance->remaining_credits - $credits;
            $balance->save();

            return SmsCreditTransaction::query()->create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'department_id' => $departmentId,
                'transaction_type' => SmsCreditTransactionType::Usage,
                'sms_campaign_id' => $campaignId,
                'sms_message_id' => $messageId,
                'amount' => -abs($credits),
                'cost_per_sms' => $balance->cost_per_sms,
                'balance_after' => $balance->remaining_credits,
                'description' => __('SMS message delivery'),
                'created_by' => $actor?->id,
            ]);
        });
    }

    protected function recordTransaction(
        SmsCreditBalance $balance,
        SmsCreditTransactionType $type,
        float $amount,
        ?User $actor,
        ?string $description,
    ): SmsCreditTransaction {
        return SmsCreditTransaction::query()->create([
            'company_id' => $balance->company_id,
            'transaction_type' => $type,
            'amount' => $amount,
            'cost_per_sms' => $balance->cost_per_sms,
            'balance_after' => $balance->remaining_credits,
            'description' => $description,
            'created_by' => $actor?->id,
        ]);
    }
}
