<?php

namespace App\Support;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\QuotationStatus;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Sales\Quotation;
use Illuminate\Validation\ValidationException;

class ArtworkApprovalValidator
{
    public static function assertCanCreateFromQuotation(Quotation $quotation): ArtworkRequest
    {
        if ($quotation->status !== QuotationStatus::Accepted) {
            throw ValidationException::withMessages([
                'quotation' => __('Quotation must be accepted before creating a sales order.'),
            ]);
        }

        if (! $quotation->customer_id) {
            throw ValidationException::withMessages([
                'customer' => __('A valid customer is required.'),
            ]);
        }

        $artworkRequest = ArtworkRequest::query()
            ->where('quotation_id', $quotation->id)
            ->where('company_id', $quotation->company_id)
            ->where('branch_id', $quotation->branch_id)
            ->first();

        if (! $artworkRequest) {
            throw ValidationException::withMessages([
                'artwork' => __('No artwork request is linked to this quotation.'),
            ]);
        }

        if ($artworkRequest->status !== ArtworkRequestStatus::Approved) {
            throw ValidationException::withMessages([
                'artwork' => __('Artwork must be approved before creating a sales order.'),
            ]);
        }

        if ($artworkRequest->current_version < 1) {
            throw ValidationException::withMessages([
                'artwork' => __('Artwork must have at least one uploaded version.'),
            ]);
        }

        $hasApprovedVersion = ArtworkApproval::query()
            ->where('artwork_request_id', $artworkRequest->id)
            ->where('artwork_version_id', $artworkRequest->currentVersionRecord()?->id)
            ->where('decision', ArtworkApprovalDecision::Approved)
            ->exists();

        if (! $hasApprovedVersion) {
            throw ValidationException::withMessages([
                'artwork' => __('The latest artwork version must be approved.'),
            ]);
        }

        return $artworkRequest;
    }
}
