<?php

namespace App\Support\Commercial;

use App\Enums\CommercialComplaintStatus;
use App\Models\Commercial\CommercialComplaint;
use App\Models\User;
use Illuminate\Support\Carbon;

class ComplaintService
{
    public function assign(CommercialComplaint $complaint, int $userId): CommercialComplaint
    {
        $status = $complaint->status === CommercialComplaintStatus::Open
            ? CommercialComplaintStatus::Assigned
            : $complaint->status;

        $complaint->update([
            'assigned_to' => $userId,
            'status' => $status,
        ]);

        return $complaint->fresh();
    }

    public function transition(CommercialComplaint $complaint, CommercialComplaintStatus $status, ?string $notes = null): CommercialComplaint
    {
        abort_unless($complaint->status->canTransitionTo($status), 422, __('Invalid status transition.'));

        $payload = ['status' => $status];

        if ($status === CommercialComplaintStatus::Resolved) {
            $payload['resolved_at'] = now();
            $payload['resolution_notes'] = $notes ?? $complaint->resolution_notes;
        }

        if ($status === CommercialComplaintStatus::Closed) {
            $payload['closed_at'] = now();
            if ($notes) {
                $payload['resolution_notes'] = $notes;
            }
        }

        if ($status === CommercialComplaintStatus::Reopened) {
            $payload['resolved_at'] = null;
            $payload['closed_at'] = null;
        }

        $complaint->update($payload);

        return $complaint->fresh();
    }

    public function openComplaintCountForCustomer(int $companyId, int $customerId): int
    {
        return CommercialComplaint::query()
            ->where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->whereNotIn('status', [
                CommercialComplaintStatus::Resolved->value,
                CommercialComplaintStatus::Closed->value,
            ])
            ->count();
    }
}
