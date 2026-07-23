<?php

namespace App\Support\Sales;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkPriority;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerArtworkStatus;
use App\Enums\DocumentType;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Crm\CustomerArtwork;
use App\Models\Sales\Quotation;
use App\Support\EnumLabel;
use App\Support\Platform\NumberingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class QuotationArtworkLinkService
{
    /**
     * @return array{
     *     linked: ?array<string, mixed>,
     *     library: list<array<string, mixed>>,
     *     requests: list<array<string, mixed>>,
     *     can_link: bool
     * }
     */
    public function presentForQuotation(Quotation $quotation): array
    {
        $quotation->loadMissing(['customer', 'artworkRequest']);

        $linked = null;
        $request = $quotation->artworkRequest;

        if ($request) {
            $linked = [
                'id' => $request->id,
                'number' => $request->request_number,
                'title' => $request->title,
                'status' => $request->status->value,
                'status_label' => EnumLabel::of($request->status),
                'is_approved' => $request->status === ArtworkRequestStatus::Approved,
                'url' => route('admin.artwork.show', $request),
            ];
        }

        if (! $quotation->customer_id) {
            return [
                'linked' => $linked,
                'library' => [],
                'requests' => [],
                'can_link' => false,
            ];
        }

        $library = CustomerArtwork::query()
            ->where('customer_id', $quotation->customer_id)
            ->where('company_id', $quotation->company_id)
            ->where('branch_id', $quotation->branch_id)
            ->where('is_active_version', true)
            ->where('status', CustomerArtworkStatus::Active)
            ->orderByDesc('uploaded_at')
            ->get()
            ->map(fn (CustomerArtwork $artwork) => [
                'id' => $artwork->id,
                'label' => $artwork->artwork_name.' ('.$artwork->versionLabel().')',
                'type' => $artwork->artworkTypeLabel(),
                'uploaded_at' => $artwork->uploaded_at?->format('Y-m-d'),
                'preview_url' => $artwork->isPreviewable() ? $artwork->previewUrl() : null,
            ])
            ->all();

        $requests = ArtworkRequest::query()
            ->where('customer_id', $quotation->customer_id)
            ->where('company_id', $quotation->company_id)
            ->where('branch_id', $quotation->branch_id)
            ->where(function ($query) use ($quotation) {
                $query->whereNull('quotation_id')
                    ->orWhere('quotation_id', $quotation->id);
            })
            ->where('status', ArtworkRequestStatus::Approved)
            ->orderByDesc('id')
            ->get()
            ->map(fn (ArtworkRequest $artworkRequest) => [
                'id' => $artworkRequest->id,
                'label' => $artworkRequest->request_number.' — '.$artworkRequest->title,
                'status_label' => EnumLabel::of($artworkRequest->status),
                'url' => route('admin.artwork.show', $artworkRequest),
            ])
            ->all();

        return [
            'linked' => $linked,
            'library' => $library,
            'requests' => $requests,
            'can_link' => true,
        ];
    }

    public function link(Quotation $quotation, string $source, int $artworkId, int $userId): ArtworkRequest
    {
        if (! $quotation->customer_id) {
            throw ValidationException::withMessages([
                'customer' => __('Assign a customer to this quotation before linking artwork.'),
            ]);
        }

        return match ($source) {
            'library' => $this->linkFromLibrary($quotation, $artworkId, $userId),
            'request' => $this->linkExistingRequest($quotation, $artworkId),
            default => throw ValidationException::withMessages([
                'artwork_source' => __('Select artwork from the customer library or an approved artwork request.'),
            ]),
        };
    }

    public function linkFromLibrary(Quotation $quotation, int $customerArtworkId, int $userId): ArtworkRequest
    {
        $libraryArtwork = CustomerArtwork::query()
            ->where('customer_id', $quotation->customer_id)
            ->where('company_id', $quotation->company_id)
            ->where('branch_id', $quotation->branch_id)
            ->where('is_active_version', true)
            ->where('status', CustomerArtworkStatus::Active)
            ->findOrFail($customerArtworkId);

        if (! $libraryArtwork->file_path || ! Storage::disk('local')->exists($libraryArtwork->file_path)) {
            throw ValidationException::withMessages([
                'customer_artwork_id' => __('The selected library file is missing from storage.'),
            ]);
        }

        return DB::transaction(function () use ($quotation, $libraryArtwork, $userId) {
            $this->detachOtherQuotationArtwork($quotation);

            $request = ArtworkRequest::query()->create([
                'company_id' => $quotation->company_id,
                'branch_id' => $quotation->branch_id,
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'request_number' => app(NumberingService::class)->next(
                    DocumentType::ArtworkRequest,
                    $quotation->company_id,
                    $quotation->branch_id,
                ),
                'title' => $libraryArtwork->artwork_name,
                'description' => __('Linked from customer artwork library (:version).', [
                    'version' => $libraryArtwork->versionLabel(),
                ]),
                'requested_by' => $userId,
                'priority' => ArtworkPriority::Normal,
                'status' => ArtworkRequestStatus::Approved,
                'current_version' => 1,
            ]);

            $targetPath = sprintf(
                'artwork/%d/%d/versions/%s',
                $quotation->company_id,
                $request->id,
                basename($libraryArtwork->file_path),
            );

            Storage::disk('local')->makeDirectory(dirname($targetPath));
            Storage::disk('local')->copy($libraryArtwork->file_path, $targetPath);

            $version = ArtworkVersion::query()->create([
                'artwork_request_id' => $request->id,
                'version_number' => 1,
                'file_path' => $targetPath,
                'original_name' => $libraryArtwork->file_name,
                'mime_type' => $libraryArtwork->mime_type,
                'size' => Storage::disk('local')->size($targetPath),
                'uploaded_by' => $userId,
                'notes' => __('Imported from customer artwork library.'),
            ]);

            ArtworkApproval::query()->create([
                'company_id' => $quotation->company_id,
                'branch_id' => $quotation->branch_id,
                'artwork_request_id' => $request->id,
                'artwork_version_id' => $version->id,
                'approved_by' => $userId,
                'decision' => ArtworkApprovalDecision::Approved,
                'comments' => __('Auto-approved from customer artwork library.'),
            ]);

            return $request->fresh(['versions', 'approvals']);
        });
    }

    public function linkExistingRequest(Quotation $quotation, int $artworkRequestId): ArtworkRequest
    {
        $request = ArtworkRequest::query()
            ->where('customer_id', $quotation->customer_id)
            ->where('company_id', $quotation->company_id)
            ->where('branch_id', $quotation->branch_id)
            ->findOrFail($artworkRequestId);

        if ($request->status !== ArtworkRequestStatus::Approved) {
            throw ValidationException::withMessages([
                'artwork_request_id' => __('Only approved artwork requests can be linked to a quotation.'),
            ]);
        }

        if ($request->quotation_id !== null && (int) $request->quotation_id !== (int) $quotation->id) {
            throw ValidationException::withMessages([
                'artwork_request_id' => __('This artwork request is already linked to another quotation.'),
            ]);
        }

        if ($request->current_version < 1 || $request->currentVersionRecord() === null) {
            throw ValidationException::withMessages([
                'artwork_request_id' => __('The artwork request must have an uploaded version.'),
            ]);
        }

        return DB::transaction(function () use ($quotation, $request) {
            $this->detachOtherQuotationArtwork($quotation, $request->id);

            $request->update(['quotation_id' => $quotation->id]);

            return $request->fresh(['versions', 'approvals']);
        });
    }

    protected function detachOtherQuotationArtwork(Quotation $quotation, ?int $exceptRequestId = null): void
    {
        ArtworkRequest::query()
            ->where('quotation_id', $quotation->id)
            ->when($exceptRequestId, fn ($query) => $query->where('id', '!=', $exceptRequestId))
            ->update(['quotation_id' => null]);
    }
}
