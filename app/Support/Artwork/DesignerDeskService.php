<?php

namespace App\Support\Artwork;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkCommentType;
use App\Enums\ArtworkRequestStatus;
use App\Models\ActivityLog;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkFile;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionSpecification;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class DesignerDeskService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $designerId = (int) $request->user()->id;
        $baseQuery = $this->assignedQuery($designerId);
        $today = now()->startOfDay();

        $requests = (clone $baseQuery)
            ->with(['customer:id,company_name,public_id', 'quotation:id,quotation_number,public_id'])
            ->latest('updated_at')
            ->paginate(25)
            ->withQueryString();

        $allAssigned = (clone $baseQuery)->get(['id', 'status', 'due_date', 'priority', 'created_at', 'updated_at']);

        return [
            'summary' => $this->summaryGroups($allAssigned, $today, $designerId),
            'urgent' => $this->urgentQueue($allAssigned, $today),
            'requests' => $requests,
            'rows' => collect($requests->items())->map(fn (ArtworkRequest $row) => $this->presentRow($row, $today)),
            'today_activity' => $this->todayActivity($designerId),
            'has_assignments' => $allAssigned->isNotEmpty(),
            'operatorMode' => $request->user()?->prefersDesignerOperatorMode() ?? false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function panel(ArtworkRequest $artworkRequest, User $user, bool $operatorMode = false): array
    {
        abort_unless((int) $artworkRequest->assigned_designer_id === (int) $user->id, 403);

        $artworkRequest->load([
            'customer:id,public_id,company_name,customer_code,phone,email',
            'quotation:id,public_id,quotation_number,prepared_by',
            'quotation.preparer:id,name',
            'quotation.items',
            'requester:id,name',
            'files.uploader:id,name',
            'versions.uploader:id,name',
            'comments.user:id,name',
            'approvals.approver:id,name',
            'approvals.artworkVersion',
        ]);

        $salesOrder = SalesOrder::query()
            ->where('artwork_request_id', $artworkRequest->id)
            ->with(['inventoryItem:id,item_name', 'customerPrintSpecification:id,name'])
            ->first();

        if (! $salesOrder && $artworkRequest->quotation_id) {
            $salesOrder = SalesOrder::query()
                ->where('quotation_id', $artworkRequest->quotation_id)
                ->with(['inventoryItem:id,item_name', 'customerPrintSpecification:id,name'])
                ->first();
        }

        $productionSpecQuery = ProductionSpecification::query()
            ->with(['materialInventoryItem:id,item_name', 'paperInventoryItem:id,item_name']);

        if ($salesOrder) {
            $productionSpecQuery->where('sales_order_id', $salesOrder->id);
        } elseif ($artworkRequest->quotation_id) {
            $productionSpecQuery->where('quotation_id', $artworkRequest->quotation_id);
        } else {
            $productionSpecQuery->whereRaw('1 = 0');
        }

        $productionSpec = $productionSpecQuery->latest('id')->first();

        $jobCard = ProductionJobCard::query()
            ->where('artwork_request_id', $artworkRequest->id)
            ->latest('id')
            ->first(['id', 'public_id', 'job_card_number', 'required_date']);

        $context = $this->presentContext($artworkRequest, $salesOrder, $productionSpec, $jobCard);
        $canEdit = $user->can('update', $artworkRequest);
        $canUploadVersion = $user->can('create', [\App\Models\Artwork\ArtworkVersion::class, $artworkRequest]);

        return [
            'header' => [
                'request_number' => $artworkRequest->request_number,
                'title' => $artworkRequest->title,
                'status' => $this->statusLabel($artworkRequest->status),
                'status_value' => $artworkRequest->status->value,
                'priority' => strtoupper($artworkRequest->priority->value),
                'priority_value' => $artworkRequest->priority->value,
                'version' => $artworkRequest->current_version ?: 0,
                'is_editable' => $artworkRequest->status->isEditable(),
                'due_display' => $this->dueDisplay($artworkRequest->due_date),
                'is_late' => $artworkRequest->due_date && $artworkRequest->due_date->lt(now()->startOfDay())
                    && ! in_array($artworkRequest->status, [ArtworkRequestStatus::Approved, ArtworkRequestStatus::Rejected], true),
                'is_due_today' => $artworkRequest->due_date?->isSameDay(now()),
            ],
            'context' => $context,
            'specifications' => $this->presentSpecifications($productionSpec),
            'files' => [
                'customer' => $artworkRequest->files->map(fn (ArtworkFile $file) => [
                    'id' => $file->public_id,
                    'name' => $file->original_name,
                    'type' => $file->file_type->value,
                    'uploader' => $file->uploader?->name,
                    'download_url' => Route::has('admin.artwork.files.download')
                        ? route('admin.artwork.files.download', [$artworkRequest, $file])
                        : null,
                ])->values()->all(),
                'versions' => $artworkRequest->versions->map(fn (ArtworkVersion $version) => [
                    'number' => $version->version_number,
                    'name' => $version->original_name,
                    'uploader' => $version->uploader?->name,
                    'notes' => $version->notes,
                    'is_current' => $version->version_number === $artworkRequest->current_version,
                    'previewable' => $version->isPreviewable(),
                    'is_pdf' => $version->mime_type === 'application/pdf',
                    'preview_url' => $version->isPreviewable() ? $version->previewUrl() : null,
                    'download_url' => Route::has('admin.artwork.versions.download')
                        ? route('admin.artwork.versions.download', [$artworkRequest, $version])
                        : null,
                ])->values()->all(),
                'can_upload_reference' => $canEdit,
                'can_upload_version' => $canUploadVersion,
                'upload_reference_url' => route('admin.artwork.files.store', $artworkRequest),
                'upload_version_url' => route('admin.artwork.versions.store', $artworkRequest),
            ],
            'revision_notes' => $this->presentRevisionNotes($artworkRequest),
            'readiness' => $this->productionReadiness($artworkRequest, $salesOrder, $jobCard),
            'primary_actions' => $this->primaryActions($artworkRequest, $user, $salesOrder, $jobCard),
            'comments_url' => route('admin.artwork.comments.store', $artworkRequest),
            'guidance' => $this->guidance($artworkRequest),
        ];
    }

    protected function assignedQuery(int $designerId): Builder
    {
        return ArtworkRequest::forTenant()
            ->where('assigned_designer_id', $designerId);
    }

    /**
     * @param  Collection<int, ArtworkRequest>  $assigned
     * @return array{operational: list<array<string, mixed>>, performance: list<array<string, mixed>>}
     */
    protected function summaryGroups(Collection $assigned, Carbon $today, int $designerId): array
    {
        $active = $assigned->reject(fn (ArtworkRequest $r) => in_array($r->status, [
            ArtworkRequestStatus::Approved,
            ArtworkRequestStatus::Rejected,
        ], true));

        $productivity = $this->productivityMetrics($designerId);

        return [
            'operational' => [
                ['key' => 'assigned', 'label' => __('Assigned'), 'value' => $assigned->count(), 'tone' => 'primary'],
                ['key' => 'working', 'label' => __('Working'), 'value' => $assigned->whereIn('status', [
                    ArtworkRequestStatus::InDesign,
                    ArtworkRequestStatus::RevisionRequested,
                ])->count(), 'tone' => 'blue'],
                ['key' => 'waiting_customer', 'label' => __('Waiting'), 'value' => $assigned->where('status', ArtworkRequestStatus::Submitted)->count(), 'tone' => 'indigo'],
                ['key' => 'revision', 'label' => __('Revision'), 'value' => $assigned->where('status', ArtworkRequestStatus::RevisionRequested)->count(), 'tone' => 'amber'],
                ['key' => 'approved', 'label' => __('Approved'), 'value' => $assigned->where('status', ArtworkRequestStatus::Approved)->count(), 'tone' => 'emerald'],
                ['key' => 'ready_today', 'label' => __('Ready Today'), 'value' => $active->filter(fn (ArtworkRequest $r) => $r->due_date?->isSameDay($today))->count(), 'tone' => 'violet'],
                ['key' => 'late', 'label' => __('Late'), 'value' => $active->filter(fn (ArtworkRequest $r) => $r->due_date && $r->due_date->lt($today))->count(), 'tone' => 'rose'],
            ],
            'performance' => [
                ['key' => 'completed_today', 'label' => __('Completed Today'), 'value' => $productivity['completed_today'], 'tone' => 'primary'],
                ['key' => 'completed_week', 'label' => __('Completed Week'), 'value' => $productivity['completed_week'], 'tone' => 'primary'],
                ['key' => 'avg_approval', 'label' => __('Approval Time'), 'value' => $productivity['avg_approval_hours'] ? $productivity['avg_approval_hours'].'h' : '—', 'tone' => 'slate'],
                ['key' => 'revision_rate', 'label' => __('Revision Rate'), 'value' => $productivity['revision_rate'].'%', 'tone' => 'slate'],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function todayActivity(int $designerId): array
    {
        $requestIds = ArtworkRequest::forTenant()
            ->where('assigned_designer_id', $designerId)
            ->pluck('id');

        if ($requestIds->isEmpty()) {
            return [];
        }

        $since = now()->subDays(7);
        $events = collect();

        ArtworkRequest::query()
            ->whereIn('id', $requestIds)
            ->where('status', ArtworkRequestStatus::Approved)
            ->where('updated_at', '>=', $since)
            ->with('customer:id,company_name')
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->each(function (ArtworkRequest $request) use ($events) {
                $events->push([
                    'at' => $request->updated_at,
                    'label' => __('Job :number approved', ['number' => $request->request_number]),
                    'tone' => 'success',
                ]);
            });

        ArtworkFile::query()
            ->whereIn('artwork_request_id', $requestIds)
            ->where('created_at', '>=', $since)
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->each(function (ArtworkFile $file) use ($events) {
                $events->push([
                    'at' => $file->created_at,
                    'label' => __('Customer uploaded :file', ['file' => $file->original_name]),
                    'tone' => 'info',
                ]);
            });

        ArtworkApproval::query()
            ->whereIn('artwork_request_id', $requestIds)
            ->where('decision', ArtworkApprovalDecision::RevisionRequested)
            ->where('created_at', '>=', $since)
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->each(function (ArtworkApproval $approval) use ($events) {
                $events->push([
                    'at' => $approval->created_at,
                    'label' => __('Revision requested'),
                    'tone' => 'warning',
                ]);
            });

        ActivityLog::query()
            ->forTenant()
            ->where('model_type', ArtworkRequest::class)
            ->whereIn('model_id', $requestIds)
            ->where('created_at', '>=', $since)
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->each(function (ActivityLog $log) use ($events) {
                $events->push([
                    'at' => $log->created_at,
                    'label' => match ($log->action) {
                        'updated' => __('Specification updated'),
                        default => __('Activity recorded'),
                    },
                    'tone' => 'neutral',
                ]);
            });

        return $events
            ->filter(fn (array $event) => $event['at'] !== null)
            ->sortByDesc('at')
            ->take(6)
            ->map(fn (array $event) => [
                'time' => $event['at']->format('H:i'),
                'label' => $event['label'],
                'tone' => $event['tone'],
            ])
            ->values()
            ->all();
    }

    protected function dueDisplay(?Carbon $dueDate): ?string
    {
        if (! $dueDate) {
            return null;
        }

        if ($dueDate->isSameDay(now())) {
            return __('Today');
        }

        if ($dueDate->isSameDay(now()->addDay())) {
            return __('Tomorrow');
        }

        return $dueDate->format('d M Y');
    }

    /**
     * @return list<array{label: string, value: string|null}>
     */
    protected function presentSpecifications(?ProductionSpecification $spec): array
    {
        if (! $spec) {
            return [];
        }

        $finish = collect([
            $spec->finishing_type,
            $spec->lamination ? __('Lamination') : null,
            $spec->foiling ? __('Foiling') : null,
            $spec->spot_uv ? __('Spot UV') : null,
        ])->filter()->implode(', ') ?: null;

        return array_values(array_filter([
            ['label' => __('Size'), 'value' => $spec->finished_size ?? $spec->size],
            ['label' => __('Material'), 'value' => $spec->materialInventoryItem?->item_name ?? $spec->paperInventoryItem?->item_name],
            ['label' => __('Colours'), 'value' => $spec->colour_mode ?? $spec->ink_type?->label()],
            ['label' => __('Finish'), 'value' => $finish],
        ], fn (array $row) => filled($row['value'])));
    }

    /**
     * @return array{customer: list<string>, sales: list<string>, internal: list<string>}
     */
    protected function presentRevisionNotes(ArtworkRequest $request): array
    {
        $customer = $request->comments
            ->where('comment_type', ArtworkCommentType::Customer)
            ->pluck('comment')
            ->filter()
            ->values()
            ->all();

        $internal = $request->comments
            ->where('comment_type', ArtworkCommentType::Internal)
            ->pluck('comment')
            ->filter()
            ->values()
            ->all();

        $sales = collect();
        if ($request->description) {
            $sales->push($request->description);
        }

        foreach ($request->approvals->where('decision', ArtworkApprovalDecision::RevisionRequested) as $approval) {
            if ($approval->comments) {
                $customer[] = $approval->comments;
            }
        }

        return [
            'customer' => $customer,
            'sales' => $sales->all(),
            'internal' => $internal,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function primaryActions(
        ArtworkRequest $request,
        User $user,
        ?SalesOrder $salesOrder,
        ?ProductionJobCard $jobCard,
    ): array {
        $actions = [];

        if ($user->can('startDesign', $request) && $request->status === ArtworkRequestStatus::Requested) {
            $actions[] = [
                'key' => 'accept',
                'label' => __('Accept Job'),
                'type' => 'post',
                'url' => route('admin.artwork.start-design', $request),
                'variant' => 'primary',
            ];
        } elseif ($user->can('startDesign', $request) && $request->status === ArtworkRequestStatus::RevisionRequested) {
            $actions[] = [
                'key' => 'resume',
                'label' => __('Resume Design'),
                'type' => 'post',
                'url' => route('admin.artwork.start-design', $request),
                'variant' => 'secondary',
            ];
        }

        if ($user->can('create', [\App\Models\Artwork\ArtworkVersion::class, $request]) && $request->status->isEditable()) {
            $actions[] = [
                'key' => 'upload',
                'label' => __('Upload Revision'),
                'type' => 'scroll',
                'target' => 'designer-desk-upload',
                'variant' => 'secondary',
            ];
        }

        if ($user->can('submit', $request)) {
            $actions[] = [
                'key' => 'submit',
                'label' => __('Send for Approval'),
                'type' => 'post',
                'url' => route('admin.artwork.submit', $request),
                'variant' => 'primary',
            ];
        }

        $readyForProduction = $request->status === ArtworkRequestStatus::Approved
            && $request->current_version >= 1
            && ($salesOrder !== null || $jobCard !== null);

        if ($readyForProduction) {
            $actions[] = [
                'key' => 'production',
                'label' => __('Ready for Production'),
                'type' => 'badge',
                'variant' => 'success',
            ];
        }

        return $actions;
    }

    /**
     * @param  Collection<int, ArtworkRequest>  $assigned
     * @return list<array<string, mixed>>
     */
    protected function urgentQueue(Collection $assigned, Carbon $today): array
    {
        $active = $assigned->reject(fn (ArtworkRequest $r) => in_array($r->status, [
            ArtworkRequestStatus::Approved,
            ArtworkRequestStatus::Rejected,
        ], true));

        $newCutoff = now()->subDays(3);

        return [
            [
                'key' => 'due_today',
                'label' => __('Due Today'),
                'count' => $active->filter(fn (ArtworkRequest $r) => $r->due_date?->isSameDay($today))->count(),
                'tone' => 'amber',
            ],
            [
                'key' => 'overdue',
                'label' => __('Overdue'),
                'count' => $active->filter(fn (ArtworkRequest $r) => $r->due_date && $r->due_date->lt($today))->count(),
                'tone' => 'rose',
            ],
            [
                'key' => 'waiting_customer',
                'label' => __('Waiting Customer'),
                'count' => $assigned->where('status', ArtworkRequestStatus::Submitted)->count(),
                'tone' => 'indigo',
            ],
            [
                'key' => 'new_assignment',
                'label' => __('New Assignment'),
                'count' => $assigned
                    ->where('status', ArtworkRequestStatus::Requested)
                    ->filter(fn (ArtworkRequest $r) => $r->created_at?->gte($newCutoff))
                    ->count(),
                'tone' => 'blue',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function productivityMetrics(int $designerId): array
    {
        $approvedQuery = ArtworkRequest::forTenant()
            ->where('assigned_designer_id', $designerId)
            ->where('status', ArtworkRequestStatus::Approved);

        $completedToday = (clone $approvedQuery)
            ->where('updated_at', '>=', now()->startOfDay())
            ->count();

        $completedWeek = (clone $approvedQuery)
            ->where('updated_at', '>=', now()->startOfWeek())
            ->count();

        $submittedCount = ArtworkRequest::forTenant()
            ->where('assigned_designer_id', $designerId)
            ->whereIn('status', [
                ArtworkRequestStatus::Submitted,
                ArtworkRequestStatus::Approved,
                ArtworkRequestStatus::RevisionRequested,
                ArtworkRequestStatus::Rejected,
            ])
            ->count();

        $revisionCount = ArtworkApproval::query()
            ->where('decision', ArtworkApprovalDecision::RevisionRequested)
            ->whereHas('artworkRequest', fn (Builder $q) => $q
                ->forTenant()
                ->where('assigned_designer_id', $designerId))
            ->count();

        $revisionRate = $submittedCount > 0
            ? round(($revisionCount / $submittedCount) * 100)
            : 0;

        $avgApprovalHours = ArtworkRequest::forTenant()
            ->where('assigned_designer_id', $designerId)
            ->where('status', ArtworkRequestStatus::Approved)
            ->whereNotNull('updated_at')
            ->get(['created_at', 'updated_at'])
            ->map(fn (ArtworkRequest $r) => $r->created_at?->diffInHours($r->updated_at))
            ->filter(fn ($hours) => $hours !== null)
            ->avg();

        return [
            'completed_today' => $completedToday,
            'completed_week' => $completedWeek,
            'avg_approval_hours' => $avgApprovalHours ? round($avgApprovalHours, 1) : null,
            'revision_rate' => $revisionRate,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentRow(ArtworkRequest $request, Carbon $today): array
    {
        $isLate = $request->due_date
            && $request->due_date->lt($today)
            && ! in_array($request->status, [ArtworkRequestStatus::Approved, ArtworkRequestStatus::Rejected], true);

        $isDueToday = $request->due_date?->isSameDay($today) && ! $isLate;

        return [
            'key' => $request->public_id,
            'request_number' => $request->request_number,
            'customer' => $request->customer?->company_name,
            'title' => $request->title,
            'priority' => $request->priority->value,
            'priority_label' => ucfirst($request->priority->value),
            'status' => $request->status->value,
            'status_label' => $this->statusLabel($request->status),
            'due_date' => $request->due_date?->format('d M Y'),
            'version' => $request->current_version ?: '—',
            'is_late' => $isLate,
            'is_due_today' => $isDueToday,
            'is_revision' => $request->status === ArtworkRequestStatus::RevisionRequested,
            'is_waiting' => $request->status === ArtworkRequestStatus::Submitted,
            'is_editable' => $request->status->isEditable(),
            'quotation_number' => $request->quotation?->quotation_number,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentContext(
        ArtworkRequest $request,
        ?SalesOrder $salesOrder,
        ?ProductionSpecification $spec,
        ?ProductionJobCard $jobCard,
    ): array {
        $firstItem = $request->quotation?->items->first();
        $product = $spec?->product_description
            ?? $salesOrder?->inventoryItem?->item_name
            ?? $salesOrder?->customerPrintSpecification?->name
            ?? $firstItem?->item_name
            ?? $request->title;

        $quantity = $spec?->quantity ?? $firstItem?->quantity ?? $salesOrder?->customerPrintSpecification?->default_quantity;
        $dimensions = collect([$spec?->finished_size, $spec?->size])->filter()->unique()->implode(' / ') ?: null;
        $material = $spec?->materialInventoryItem?->item_name
            ?? $spec?->paperInventoryItem?->item_name
            ?? null;
        $printingMethod = collect([
            $spec?->production_type ? str_replace('_', ' ', ucfirst($spec->production_type->value)) : null,
            $spec?->colour_mode,
            $spec?->ink_type?->label(),
        ])->filter()->implode(' · ') ?: null;

        $productionDeadline = $jobCard?->required_date?->format('d M Y')
            ?? $salesOrder?->required_date?->format('d M Y');

        return [
            'customer' => $request->customer?->company_name,
            'customer_contact' => collect([
                $request->customer?->phone,
                $request->customer?->email,
            ])->filter()->implode(' · ') ?: null,
            'quotation_number' => $request->quotation?->quotation_number,
            'sales_order_number' => $salesOrder?->order_number,
            'product' => $product,
            'quantity' => $quantity ? (string) $quantity : null,
            'dimensions' => $dimensions,
            'material' => $material,
            'printing_method' => $printingMethod,
            'due_date' => $request->due_date?->format('d M Y'),
            'salesperson' => $request->quotation?->preparer?->name ?? $request->requester?->name,
            'production_deadline' => $productionDeadline,
            'description' => $request->description,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function productionReadiness(
        ArtworkRequest $request,
        ?SalesOrder $salesOrder,
        ?ProductionJobCard $jobCard,
    ): array {
        $approved = $request->status === ArtworkRequestStatus::Approved;

        $items = [
            __('Fonts outlined'),
            __('CMYK'),
            __('Bleed'),
            __('Images embedded'),
            __('Dimensions verified'),
        ];

        if ($approved) {
            $items[] = __('Customer approved');
        }

        return collect($items)->map(fn (string $label) => [
            'label' => $label,
            'done' => $approved,
        ])->all();
    }

    protected function guidance(ArtworkRequest $request): ?string
    {
        return match (true) {
            $request->status === ArtworkRequestStatus::Requested => __('Start design, then upload a version before submitting for approval.'),
            $request->status === ArtworkRequestStatus::InDesign && $request->lacksUploadedVersion() => __('Upload at least one artwork version before submitting for approval.'),
            $request->lacksUploadedVersion() && $request->status->isEditable() => __('Upload a version to continue.'),
            $request->status === ArtworkRequestStatus::RevisionRequested => __('Review revision notes, update the design, and resubmit.'),
            $request->status === ArtworkRequestStatus::Submitted => __('Waiting for customer approval.'),
            default => null,
        };
    }

    protected function statusLabel(ArtworkRequestStatus $status): string
    {
        return match ($status) {
            ArtworkRequestStatus::Requested => __('Requested'),
            ArtworkRequestStatus::InDesign => __('In Design'),
            ArtworkRequestStatus::Submitted => __('Submitted'),
            ArtworkRequestStatus::Approved => __('Approved'),
            ArtworkRequestStatus::RevisionRequested => __('Revision Requested'),
            ArtworkRequestStatus::Rejected => __('Rejected'),
        };
    }
}
