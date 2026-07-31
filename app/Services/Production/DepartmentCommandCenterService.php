<?php

namespace App\Services\Production;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\User;
use App\Services\Accounting\DeliveryInvoiceService;
use App\Support\Production\DepartmentQueueRegistry;
use App\Support\Production\JobCardOutsourceService;
use App\Support\Production\JobCardPrintUrl;
use App\Support\Production\ProductionImpositionCalculator;
use App\Support\Sales\SalesOrderFinancialStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DepartmentCommandCenterService
{
    public function __construct(
        protected ProductionQueueWorkspaceService $queues,
        protected DepartmentQueueRegistry $departments,
        protected JobProductionControlService $controls,
        protected ProductionAutoSchedulingService $scheduling,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request, ?string $department = null): array
    {
        $payload = $this->queues->buildIndex($request, $department);
        $metrics = $this->commandMetrics($request, $department);

        return array_merge($payload, [
            'command_center' => $this,
            'columns' => $this->columnsFor($department ?? 'all'),
            'command_metrics' => $metrics,
            'summary' => [
                'total_visible' => $payload['queues']->total(),
                'waiting' => $metrics['jobs_waiting'] ?? 0,
                'running' => $metrics['jobs_running'] ?? 0,
                'overdue' => $metrics['jobs_overdue'] ?? 0,
                'completed_today' => $metrics['jobs_completed_today'] ?? 0,
            ],
            'export_routes' => $this->exportRoutes($department),
        ]);
    }

    /**
     * @return list<array{key: string, label: string, class?: string}>
     */
    public function columnsFor(string $department): array
    {
        $definitions = [
            'all' => [
                'date', 'job_card_number', 'customer_name', 'product', 'quantity', 'finished_size',
                'paper_material', 'colour_mode', 'due_date', 'operator_name', 'machine_name',
                'production_status', 'payment_status',
            ],
            'offset' => [
                'date', 'job_card_number', 'customer_name', 'job_type', 'product', 'quantity', 'finished_size',
                'ink_colour', 'paper_type', 'binding', 'ups', 'estimated_sheets', 'serial_start', 'due_date',
                'unit_price', 'line_amount', 'payment_status', 'order_status',
            ],
            'digital' => [
                'date', 'job_card_number', 'customer_name', 'product', 'quantity', 'paper_material', 'ups',
                'estimated_sheets', 'finishing', 'unit_price', 'line_amount', 'due_date', 'payment_status',
                'order_status',
            ],
            'outsource' => [
                'date', 'job_card_number', 'customer_name', 'product', 'quantity', 'production_type',
                'vendor_name', 'vendor_cost', 'selling_price', 'margin', 'date_sent', 'expected_return',
                'returned_date', 'payment_status', 'production_status', 'invoice_status', 'outsource_notes',
            ],
            'large_format' => [
                'job_card_number', 'customer_name', 'material', 'width', 'height', 'square_metres',
                'machine_name', 'operator_name', 'finishing', 'eyelets', 'welding', 'due_date',
                'production_status', 'dispatch_status',
            ],
            'finishing' => [
                'job_card_number', 'customer_name', 'binding', 'lamination', 'foiling', 'spot_uv',
                'embossing', 'die_cutting', 'packaging', 'operator_name', 'production_status', 'qc_status',
            ],
        ];

        $keys = $definitions[$department] ?? $definitions['all'];

        return array_map(fn (string $key) => [
            'key' => $key,
            'label' => $this->columnLabel($key),
            'class' => $this->columnClass($key),
        ], $keys);
    }

    /**
     * @return array<string, mixed>
     */
    public function commandMetrics(?Request $request, ?string $department): array
    {
        $base = $this->queues->liveMetrics($request, $department);
        $scoped = fn () => ProductionQueue::query()->forTenant()
            ->when($department, fn ($q) => $this->departments->applyDepartmentScope($q, $department));

        $dueToday = (clone $scoped())->whereHas('jobCard', function ($q) {
            $q->whereDate('required_date', today())
                ->whereNotIn('status', [
                    ProductionJobCardStatus::Completed,
                    ProductionJobCardStatus::ReadyForDispatch,
                    ProductionJobCardStatus::Cancelled,
                ]);
        })->count();

        $machineUtilisation = $this->machineUtilisationForDepartment($department);

        $operatorCount = collect($base['operator_workload'] ?? []);
        $operatorUtilisation = $operatorCount->isEmpty()
            ? null
            : (int) round($operatorCount->avg('workload'), 0);

        return array_merge($base, [
            'jobs_due_today' => $dueToday,
            'machine_utilisation_percent' => $machineUtilisation,
            'operator_utilisation' => $operatorUtilisation,
            'average_completion_hours' => $base['average_queue_age_hours'],
        ]);
    }

    /**
     * @return list<array{key: string, label: string, class?: string}>
     */
    public function registerColumnsFor(string $department): array
    {
        $columns = $this->columnsFor($department);
        $keys = array_column($columns, 'key');

        foreach (['completion_date'] as $extra) {
            if (in_array($department, ['digital', 'offset'], true) && ! in_array($extra, $keys, true)) {
                $columns[] = ['key' => $extra, 'label' => $this->columnLabel($extra)];
            }
        }

        return $columns;
    }

    /**
     * @return list<array{values: list<string>, links: array<int, string|null>}>
     */
    public function registerRows(Request $request, string $department, ?User $user = null, int $limit = 2000): array
    {
        $columns = $this->registerColumnsFor($department);

        return $this->exportIndex($request, $department)
            ->take($limit)
            ->map(function (ProductionQueue $queue) use ($department, $user, $columns) {
                $presented = $this->presentCommandRow($queue, $department, $user);
                $jobCardIndex = null;

                $values = [];
                foreach ($columns as $index => $column) {
                    if ($column['key'] === 'job_card_number') {
                        $jobCardIndex = $index;
                    }
                    $values[] = (string) ($presented[$column['key']] ?? '—');
                }

                $links = [];
                if ($jobCardIndex !== null && ! empty($presented['job_360_url'])) {
                    $links[$jobCardIndex] = $presented['job_360_url'];
                }

                $customerIndex = array_search('customer_name', array_column($columns, 'key'), true);
                if ($customerIndex !== false && ! empty($presented['customer_360_url'])) {
                    $links[$customerIndex] = $presented['customer_360_url'];
                }

                return [
                    'values' => $values,
                    'links' => $links,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function presentCommandRow(ProductionQueue $queue, ?string $department, ?User $user = null): array
    {
        $row = $this->queues->presentRow($queue, $user);
        $job = $queue->jobCard;
        $spec = $job?->productionSpecification;
        $spec?->loadMissing(['paperInventoryItem', 'materialInventoryItem', 'printProductTemplate']);
        $job?->loadMissing(['outsourceVendor', 'salesOrder.items', 'costSheet', 'deliveryNotes', 'serialAllocation']);

        $qc = $job ? $this->controls->qcStatusSummary($job) : ['label' => '—', 'status' => 'none'];
        $dispatch = $job ? $this->dispatchStatusLabel($job) : '—';
        $financial = $job?->salesOrder
            ? app(SalesOrderFinancialStatusService::class)->snapshot($job->salesOrder)
            : null;
        $invoice = $job ? app(DeliveryInvoiceService::class)->billingStatusForJob($job->id) : null;
        $lineItem = $job?->salesOrder?->items?->first();
        $unitPrice = $lineItem?->unit_price;
        $lineAmount = $lineItem?->line_total ?? ($unitPrice && $spec?->quantity
            ? round((float) $unitPrice * (float) $spec->quantity, 2)
            : null);
        $sellingPrice = $job?->salesOrder?->total_amount ?? $lineAmount;
        $vendorCost = $job?->outsource_actual_cost ?? $job?->outsource_quoted_cost;
        $dimensions = $this->parseDimensions($spec?->finished_size ?? $spec?->size);
        $legacyStatus = $this->legacyOrderStatus($job, $dispatch);
        $sheetCount = ProductionImpositionCalculator::displaySheets(
            $spec?->quantity ?? $lineItem?->quantity,
            $spec?->ups,
            $spec?->estimated_sheets,
        );

        return array_merge($row, [
            'date' => $queue->created_at?->format('d/m/Y'),
            'completion_date' => $job?->actual_end_date?->format('Y-m-d')
                ?? (($job?->status === ProductionJobCardStatus::Completed) ? $job->updated_at?->format('Y-m-d') : '—'),
            'client' => $row['customer_name'],
            'paper_type' => $spec?->paperInventoryItem?->item_name,
            'paper_size' => $spec?->sheet_size,
            'material' => $spec?->materialInventoryItem?->item_name ?? $spec?->paperInventoryItem?->item_name,
            'lamination' => $spec?->lamination ? __('Yes') : ($spec ? __('No') : '—'),
            'ups' => $spec?->ups,
            'estimated_sheets' => $sheetCount,
            'job_type' => $this->resolveJobType($spec, $lineItem),
            'ink_colour' => $this->resolveInkColour($spec),
            'serial_start' => $this->resolveSerialStart($job, $spec),
            'printing_number' => $this->resolveSerialStart($job, $spec),
            'order_status' => $legacyStatus['label'],
            'order_status_variant' => $legacyStatus['variant'],
            'production_status' => $row['operational_status'],
            'production_status_variant' => $row['operational_variant'],
            'qc_status' => $qc['label'],
            'qc_status_variant' => $this->qcVariant($qc['status']),
            'dispatch_status' => $dispatch,
            'dispatch_status_variant' => $this->dispatchVariant($job),
            'payment_status' => $financial['financial_status_label'] ?? '—',
            'payment_status_variant' => $financial['financial_status_variant'] ?? 'neutral',
            'unit_price' => $unitPrice !== null ? number_format((float) $unitPrice, 2) : '—',
            'line_amount' => $lineAmount !== null ? number_format((float) $lineAmount, 2) : '—',
            'price' => $sellingPrice !== null ? number_format((float) $sellingPrice, 2) : '—',
            'amount' => $lineAmount !== null ? number_format((float) $lineAmount, 2) : '—',
            'production_type' => $spec?->production_type?->value
                ? str_replace('_', ' ', ucfirst($spec->production_type->value))
                : ($job?->production_type?->value ? str_replace('_', ' ', ucfirst($job->production_type->value)) : '—'),
            'vendor_name' => $job?->outsourceVendor?->vendor_name ?? '—',
            'vendor_cost' => $vendorCost !== null ? number_format((float) $vendorCost, 2) : '—',
            'selling_price' => $sellingPrice !== null ? number_format((float) $sellingPrice, 2) : '—',
            'margin' => ($sellingPrice !== null && $vendorCost !== null)
                ? number_format((float) $sellingPrice - (float) $vendorCost, 2)
                : '—',
            'date_sent' => $job?->outsource_issue_date?->format('Y-m-d') ?? $job?->outsourced_at?->format('Y-m-d') ?? '—',
            'expected_return' => $job?->outsource_expected_return?->format('Y-m-d') ?? '—',
            'returned_date' => $job?->returned_at?->format('Y-m-d') ?? '—',
            'invoice_status' => $invoice['label'] ?? ($financial['financial_status_label'] ?? '—'),
            'outsource_notes' => $job?->outsource_notes ?? '—',
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'square_metres' => $dimensions['square_metres'],
            'foiling' => $spec?->foiling ? __('Yes') : ($spec ? __('No') : '—'),
            'spot_uv' => $spec?->spot_uv ? __('Yes') : ($spec ? __('No') : '—'),
            'embossing' => $spec?->embossing ? __('Yes') : ($spec ? __('No') : '—'),
            'die_cutting' => $spec?->die_cutting ? __('Yes') : ($spec ? __('No') : '—'),
            'eyelets' => $spec?->eyelets ? __('Yes') : ($spec ? __('No') : '—'),
            'welding' => $spec?->finishing_type && str_contains(strtolower((string) $spec->finishing_type), 'weld')
                ? __('Yes')
                : ($spec ? __('No') : '—'),
            'packaging' => $spec?->printProductTemplate?->recommended_packaging ?? '—',
            'status_badges' => $this->statusBadges($row, $qc, $dispatch, $financial),
            'print_url' => $job ? JobCardPrintUrl::resolve($job, $department) : null,
            'print_label' => $job ? JobCardPrintUrl::actionLabel($job, $department) : null,
            'customer_360_url' => ($job?->customer_id && auth()->user()?->can('view', $job->customer))
                ? route('admin.crm.customers.show', $job->customer)
                : null,
        ]);
    }

    /**
     * @return Collection<int, ProductionQueue>
     */
    public function exportIndex(Request $request, ?string $department): Collection
    {
        return $this->queues->filteredQueryForExport($request, $department)->limit(5000)->get();
    }

    /**
     * @return list<string>
     */
    public function exportHeaders(?string $department): array
    {
        return array_column($this->columnsFor($department ?? 'all'), 'label');
    }

    /**
     * @return list<string>
     */
    public function exportRow(ProductionQueue $queue, ?string $department, ?User $user = null): array
    {
        $presented = $this->presentCommandRow($queue, $department, $user);
        $columns = $this->columnsFor($department ?? 'all');

        return array_map(
            fn (array $column) => (string) ($presented[$column['key']] ?? ''),
            $columns,
        );
    }

    /**
     * @return array<string, string>
     */
    protected function exportRoutes(?string $department): array
    {
        $params = $department ? ['department' => $department] : [];

        return [
            'csv' => route('admin.production.queue.export', array_merge($params, ['format' => 'csv'])),
            'excel' => route('admin.production.queue.export', array_merge($params, ['format' => 'excel'])),
            'pdf' => route('admin.production.queue.export', array_merge($params, ['format' => 'pdf'])),
        ];
    }

    protected function dispatchStatusLabel(?ProductionJobCard $job): string
    {
        if (! $job) {
            return '—';
        }

        if ($job->status === ProductionJobCardStatus::ReadyForDispatch) {
            return __('Ready for dispatch');
        }

        $delivered = $job->relationLoaded('deliveryNotes')
            ? $job->deliveryNotes->contains(fn ($n) => $n->status === DeliveryNoteStatus::Delivered)
            : $job->deliveryNotes()->where('status', DeliveryNoteStatus::Delivered)->exists();

        if ($delivered || $job->status === ProductionJobCardStatus::Completed) {
            return __('Delivered');
        }

        $eligibility = $this->controls->dispatchEligibility($job);

        return $eligibility['eligible'] ? __('Ready for dispatch') : __('Not ready');
    }

    protected function dispatchVariant(?ProductionJobCard $job): string
    {
        if (! $job) {
            return 'neutral';
        }

        return match ($job->status) {
            ProductionJobCardStatus::ReadyForDispatch => 'success',
            ProductionJobCardStatus::Completed => 'success',
            default => 'neutral',
        };
    }

    protected function qcVariant(string $status): string
    {
        return match ($status) {
            'passed' => 'success',
            'failed', 'rework_required' => 'danger',
            'conditional_pass' => 'warning',
            default => 'neutral',
        };
    }

    /**
     * @return array{width: string, height: string, square_metres: string}
     */
    protected function parseDimensions(?string $size): array
    {
        if (! $size || ! preg_match('/(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)/i', $size, $matches)) {
            return ['width' => '—', 'height' => '—', 'square_metres' => '—'];
        }

        $width = (float) $matches[1];
        $height = (float) $matches[2];
        $sqm = round(($width / 1000) * ($height / 1000), 2);

        return [
            'width' => (string) $width,
            'height' => (string) $height,
            'square_metres' => number_format($sqm, 2),
        ];
    }

    /**
     * @return list<array{label: string, variant: string}>
     */
    protected function statusBadges(array $row, array $qc, string $dispatch, ?array $financial): array
    {
        $badges = [
            ['label' => $row['operational_status'], 'variant' => $row['operational_variant'] ?? 'neutral'],
        ];

        if (($qc['status'] ?? 'none') !== 'none') {
            $badges[] = ['label' => $qc['label'], 'variant' => $this->qcVariant($qc['status'])];
        }

        if ($dispatch !== '—') {
            $badges[] = ['label' => $dispatch, 'variant' => 'info'];
        }

        if ($financial) {
            $badges[] = [
                'label' => $financial['financial_status_label'],
                'variant' => $financial['financial_status_variant'] ?? 'neutral',
            ];
        }

        foreach ($row['alerts'] ?? [] as $alert) {
            $badges[] = ['label' => $alert['label'], 'variant' => 'warning'];
        }

        return $badges;
    }

    protected function machineUtilisationForDepartment(?string $department): ?int
    {
        if (! $department) {
            return null;
        }

        $dept = $this->departments->department($department);
        if (! $dept || $dept['work_center_codes'] === []) {
            return null;
        }

        $centers = \App\Models\Production\WorkCenter::query()
            ->forTenant()
            ->whereIn('code', $dept['work_center_codes'])
            ->get();

        if ($centers->isEmpty()) {
            return null;
        }

        $utils = $centers->map(fn ($wc) => $this->scheduling->capacityMetrics($wc)['utilization_percent']);

        return (int) round($utils->avg(), 0);
    }

    /**
     * @return array{label: string, variant: string}
     */
    protected function legacyOrderStatus(?ProductionJobCard $job, string $dispatchLabel): array
    {
        if (! $job) {
            return ['label' => '—', 'variant' => 'neutral'];
        }

        if ($job->status === ProductionJobCardStatus::Cancelled) {
            return ['label' => __('Cancelled'), 'variant' => 'neutral'];
        }

        if (in_array($job->status, [ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch], true)
            || $dispatchLabel === __('Delivered')) {
            return ['label' => __('Order complete'), 'variant' => 'success'];
        }

        return ['label' => __('In progress'), 'variant' => 'warning'];
    }

    protected function resolveJobType(?\App\Models\Production\ProductionSpecification $spec, ?\App\Models\Sales\SalesOrderItem $lineItem): string
    {
        $template = $spec?->printProductTemplate;
        if ($template?->name) {
            return $template->name;
        }

        if ($template?->category) {
            return $template->category->label();
        }

        if ($lineItem?->item_name) {
            return $lineItem->item_name;
        }

        return '—';
    }

    protected function resolveInkColour(?\App\Models\Production\ProductionSpecification $spec): string
    {
        if (! $spec) {
            return '—';
        }

        $parts = array_filter([
            filled($spec->colour_mode) ? $spec->colour_mode : null,
            $spec->ink_type?->label(),
        ]);

        return $parts !== [] ? implode(' / ', $parts) : '—';
    }

    protected function resolveSerialStart(?ProductionJobCard $job, ?\App\Models\Production\ProductionSpecification $spec): string
    {
        $allocation = $job?->serialAllocation;
        if ($allocation) {
            return $allocation->formatSerial($allocation->serial_start);
        }

        if ($spec?->numbering_required) {
            return __('No number');
        }

        return '—';
    }

    public function columnLabel(string $key): string
    {
        return match ($key) {
            'date' => __('Date'),
            'completion_date' => __('Completion date'),
            'job_card_number' => __('Job card'),
            'customer_name' => __('Client'),
            'product' => __('Description'),
            'quantity' => __('Quantity'),
            'paper_type' => __('Paper type'),
            'paper_size' => __('Paper size'),
            'paper_material' => __('Paper'),
            'finished_size' => __('Finished size'),
            'colour_mode' => __('Colour'),
            'binding' => __('Binding'),
            'lamination' => __('Lamination'),
            'ups' => __('No. of ups'),
            'estimated_sheets' => __('No. of sheets'),
            'serial_start' => __('Starting num'),
            'printing_number' => __('Printing number'),
            'job_type' => __('Type'),
            'ink_colour' => __('Ink colour'),
            'order_status' => __('Status'),
            'machine_name' => __('Machine'),
            'operator_name' => __('Operator'),
            'due_date' => __('Due date'),
            'days_remaining' => __('Days remaining'),
            'unit_price' => __('Price'),
            'line_amount' => __('Amount'),
            'payment_status' => __('Payment status'),
            'production_status' => __('Production status'),
            'qc_status' => __('QC status'),
            'dispatch_status' => __('Dispatch status'),
            'finishing' => __('Finishing'),
            'production_type' => __('Type of printing'),
            'vendor_name' => __('Service provider'),
            'vendor_cost' => __('Cost'),
            'selling_price' => __('Selling price'),
            'margin' => __('Margin'),
            'date_sent' => __('Date sent'),
            'expected_return' => __('Expected return'),
            'returned_date' => __('Returned date'),
            'invoice_status' => __('Invoice status'),
            'outsource_notes' => __('Notes'),
            'material' => __('Material'),
            'width' => __('Width'),
            'height' => __('Height'),
            'square_metres' => __('Square metres'),
            'eyelets' => __('Eyelets'),
            'welding' => __('Welding'),
            'foiling' => __('Foiling'),
            'spot_uv' => __('Spot UV'),
            'embossing' => __('Embossing'),
            'die_cutting' => __('Cutting'),
            'packaging' => __('Packaging'),
            default => ucfirst(str_replace('_', ' ', $key)),
        };
    }

    protected function columnClass(string $key): string
    {
        return match ($key) {
            'quantity', 'ups', 'estimated_sheets', 'days_remaining', 'unit_price', 'line_amount',
            'vendor_cost', 'selling_price', 'margin', 'square_metres', 'width', 'height' => 'tabular-nums',
            'job_card_number', 'printing_number' => 'font-mono text-xs',
            default => '',
        };
    }
}
