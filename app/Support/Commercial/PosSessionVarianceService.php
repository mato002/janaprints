<?php

namespace App\Support\Commercial;

use App\Enums\PosSessionStatus;
use App\Models\Pos\PosSession;
use Illuminate\Validation\ValidationException;

class PosSessionVarianceService
{
    public function tolerance(): float
    {
        return (float) config('pos.cash_variance_tolerance', 100);
    }

    public function calculate(float $expectedCash, float $actualCash): float
    {
        return round($actualCash - $expectedCash, 2);
    }

    public function exceedsTolerance(float $variance): bool
    {
        return abs($variance) > $this->tolerance();
    }

    /**
     * @return array{variance: float, requires_approval: bool, status: PosSessionStatus}
     */
    public function resolveCloseStatus(float $expectedCash, float $actualCash): array
    {
        $variance = $this->calculate($expectedCash, $actualCash);
        $requiresApproval = $this->exceedsTolerance($variance);

        return [
            'variance' => $variance,
            'requires_approval' => $requiresApproval,
            'status' => $requiresApproval ? PosSessionStatus::PendingApproval : PosSessionStatus::Closed,
        ];
    }

    public function assertCanApprove(PosSession $session): void
    {
        if ($session->status !== PosSessionStatus::PendingApproval) {
            throw ValidationException::withMessages([
                'session' => __('Only sessions pending variance approval can be approved.'),
            ]);
        }
    }

    public function approveVariance(PosSession $session, int $approvedBy): PosSession
    {
        $this->assertCanApprove($session);

        $session->update([
            'status' => PosSessionStatus::Closed,
            'variance_approved_by' => $approvedBy,
            'variance_approved_at' => now(),
            'closed_at' => $session->closed_at ?? now(),
            'closed_by' => $session->closed_by ?? $approvedBy,
        ]);

        return $session->fresh(['cashier', 'branch', 'closer', 'varianceApprover']);
    }
}
