<?php

namespace App\Support\Crm;

use App\Enums\ArtworkFileType;
use App\Enums\ArtworkPriority;
use App\Enums\ArtworkRequestStatus;
use App\Enums\DocumentType;
use App\Enums\LeadStatus;
use App\Models\Artwork\ArtworkFile;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadSource;
use App\Models\Crm\LeadStage;
use App\Models\PublicQuoteRequest;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\ArtworkFileHelper;
use App\Support\Platform\NumberingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicQuoteRequestCrmBridgeService
{
    public function __construct(
        protected CrmSettings $crmSettings,
        protected LeadQuotationService $leadQuotation,
    ) {}

    public function integrate(PublicQuoteRequest $quoteRequest): ?Lead
    {
        if ($quoteRequest->lead_id) {
            return Lead::query()->find($quoteRequest->lead_id);
        }

        [$company, $branch] = $this->resolveTenant();

        if (! $company || ! $branch) {
            Log::warning('Public quote CRM bridge skipped: tenant not resolved.', [
                'quote_request_id' => $quoteRequest->id,
            ]);

            return null;
        }

        if (! $this->crmSettings->publicQuoteAutoCreateLead($company->id)) {
            return null;
        }

        return DB::transaction(function () use ($quoteRequest, $company, $branch) {
            $assignee = $this->resolveAssignee($company->id);

            $lead = $this->createLead($quoteRequest, $company, $branch, $assignee);

            $quoteRequest->update([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'lead_id' => $lead->id,
                'assigned_to' => $assignee?->id ?? $quoteRequest->assigned_to,
            ]);

            $quotation = null;

            if ($assignee && $this->crmSettings->publicQuoteAutoDraftQuotation($company->id)) {
                $quotation = $this->createDraftQuotation($lead, $assignee, $quoteRequest);
            }

            $artworkRequest = $this->linkArtwork($quoteRequest, $lead, $quotation, $assignee, $company, $branch);

            if ($artworkRequest) {
                $quoteRequest->update(['artwork_request_id' => $artworkRequest->id]);
            }

            return $lead->fresh(['leadSource', 'assignee', 'publicQuoteRequest']);
        });
    }

    /**
     * @return array{0: ?Company, 1: ?Branch}
     */
    protected function resolveTenant(): array
    {
        $companyCode = config('leads.crm.default_company_code', 'JANA');
        $branchCode = config('leads.crm.default_branch_code', 'HQ');

        $company = Company::query()->where('code', $companyCode)->where('is_active', true)->first();
        $branch = $company
            ? Branch::query()->where('company_id', $company->id)->where('code', $branchCode)->where('is_active', true)->first()
            : null;

        if (! $branch && $company) {
            $branch = Branch::query()
                ->where('company_id', $company->id)
                ->where('is_active', true)
                ->orderByDesc('is_head_office')
                ->first();
        }

        return [$company, $branch];
    }

    protected function resolveAssignee(int $companyId): ?User
    {
        $configuredId = $this->crmSettings->publicQuoteDefaultAssigneeId($companyId);

        if ($configuredId) {
            $configured = User::query()
                ->where('company_id', $companyId)
                ->where('id', $configuredId)
                ->where('is_active', true)
                ->first();

            if ($configured) {
                return $configured;
            }
        }

        $role = config('leads.crm.default_assignee_role', 'Sales');

        return User::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->where('name', $role))
            ->orderBy('id')
            ->first();
    }

    protected function createLead(
        PublicQuoteRequest $quoteRequest,
        Company $company,
        Branch $branch,
        ?User $assignee,
    ): Lead {
        $stage = LeadStage::query()
            ->where('company_id', $company->id)
            ->where('slug', 'new')
            ->first();

        $source = $this->resolveLeadSource($company->id, $quoteRequest->source);

        return Lead::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'lead_source_id' => $source?->id,
            'assigned_to' => $assignee?->id,
            'public_quote_request_id' => $quoteRequest->id,
            'stage_id' => $stage?->id,
            'lead_name' => $quoteRequest->name,
            'company_name' => $quoteRequest->company ?: $quoteRequest->name,
            'phone' => $quoteRequest->phone,
            'email' => $quoteRequest->email,
            'estimated_value' => $quoteRequest->expected_value ?? 0,
            'probability' => $quoteRequest->probability ?? 0,
            'expected_close_date' => $quoteRequest->target_follow_up_at,
            'status' => LeadStatus::Open,
            'notes' => $this->buildLeadNotes($quoteRequest),
        ]);
    }

    protected function buildLeadNotes(PublicQuoteRequest $quoteRequest): string
    {
        $lines = [
            __('Public quote request :ref', ['ref' => $quoteRequest->reference()]),
            __('Service') . ': ' . $quoteRequest->service_needed,
        ];

        if ($quoteRequest->quantity) {
            $lines[] = __('Quantity') . ': ' . $quoteRequest->quantity;
        }

        if ($quoteRequest->deadline) {
            $lines[] = __('Deadline') . ': ' . $quoteRequest->deadline;
        }

        $lines[] = '';
        $lines[] = $quoteRequest->message;

        return implode("\n", $lines);
    }

    protected function resolveLeadSource(int $companyId, string $source): ?LeadSource
    {
        $name = PublicQuoteRequest::leadSourceNameFor($source);

        return LeadSource::query()->firstOrCreate(
            ['company_id' => $companyId, 'name' => $name],
            ['is_active' => true],
        );
    }

    protected function createDraftQuotation(Lead $lead, User $assignee, PublicQuoteRequest $quoteRequest): ?Quotation
    {
        if (! $assignee->can('quotations.create')) {
            return null;
        }

        try {
            $quotation = $this->leadQuotation->quickQuote($lead->fresh(), $assignee);

            $quotation->update([
                'notes' => trim(($quotation->notes ?? '') . "\n" . __('Auto-created from public quote request :ref', [
                    'ref' => $quoteRequest->reference(),
                ])),
            ]);

            $quoteRequest->update(['quotation_id' => $quotation->id]);

            return $quotation;
        } catch (\Throwable $exception) {
            Log::warning('Public quote draft quotation skipped.', [
                'lead_id' => $lead->id,
                'quote_request_id' => $quoteRequest->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    protected function linkArtwork(
        PublicQuoteRequest $quoteRequest,
        Lead $lead,
        ?Quotation $quotation,
        ?User $assignee,
        Company $company,
        Branch $branch,
    ): ?ArtworkRequest {
        if (! $quoteRequest->artwork_path || ! $assignee) {
            return null;
        }

        if (! $quotation || ! $lead->fresh()->customer_id) {
            return null;
        }

        $customerId = $lead->fresh()->customer_id;

        $artworkRequest = ArtworkRequest::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customerId,
            'quotation_id' => $quotation->id,
            'request_number' => app(NumberingService::class)->next(
                DocumentType::ArtworkRequest,
                $company->id,
                $branch->id,
            ),
            'title' => __('Storefront artwork — :service', ['service' => $quoteRequest->service_needed]),
            'description' => __('Uploaded with public quote request :ref', ['ref' => $quoteRequest->reference()]),
            'requested_by' => $assignee->id,
            'priority' => ArtworkPriority::Normal,
            'status' => ArtworkRequestStatus::Requested,
            'current_version' => 0,
        ]);

        $this->copyArtworkFile($quoteRequest, $artworkRequest, $assignee);

        return $artworkRequest;
    }

    protected function copyArtworkFile(
        PublicQuoteRequest $quoteRequest,
        ArtworkRequest $artworkRequest,
        User $assignee,
    ): void {
        $sourceDisk = config('leads.artwork.disk', 'public');

        if (! Storage::disk($sourceDisk)->exists($quoteRequest->artwork_path)) {
            return;
        }

        $extension = strtolower(pathinfo($quoteRequest->artwork_path, PATHINFO_EXTENSION));
        $fileType = ArtworkFileHelper::typeFromExtension($extension);

        if (! $fileType) {
            return;
        }

        $targetPath = 'artwork/'.$artworkRequest->company_id.'/'.$artworkRequest->id.'/files/'.Str::uuid().'.'.$extension;
        $contents = Storage::disk($sourceDisk)->get($quoteRequest->artwork_path);

        Storage::disk('local')->put($targetPath, $contents);

        ArtworkFile::query()->create([
            'company_id' => $artworkRequest->company_id,
            'branch_id' => $artworkRequest->branch_id,
            'artwork_request_id' => $artworkRequest->id,
            'uploaded_by' => $assignee->id,
            'file_type' => $fileType,
            'original_name' => $quoteRequest->artwork_original_name ?? basename($quoteRequest->artwork_path),
            'path' => $targetPath,
            'mime_type' => Storage::disk($sourceDisk)->mimeType($quoteRequest->artwork_path) ?: 'application/octet-stream',
            'size' => Storage::disk($sourceDisk)->size($quoteRequest->artwork_path),
        ]);
    }
}
