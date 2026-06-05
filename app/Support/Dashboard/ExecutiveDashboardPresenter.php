<?php

namespace App\Support\Dashboard;

use App\Enums\ArtworkRequestStatus;
use App\Enums\EmploymentStatus;
use App\Enums\LeadStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\ActivityLog;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Employee;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\InventoryMovement;
use App\Models\Procurement\PurchaseRequest;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Enums\PublicContactMessageStatus;
use App\Enums\PublicQuoteRequestStatus;
use App\Models\PublicContactMessage;
use App\Models\PublicQuoteRequest;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Support\Integrations\IntegrationHealthPresenter;
use App\Support\InventoryStockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExecutiveDashboardPresenter
{
    public function build(): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        return [
            'generated_at' => now()->toIso8601String(),
            'context' => $this->context(),
            'kpi_strip' => $this->kpiStrip($today, $monthStart),
            'pipeline' => $this->pipeline(),
            'attention' => $this->attentionCenter($today),
            'today_ops' => $this->todayOperations($today),
            'branches' => $this->branchPerformance($monthStart),
            'top_customers' => $this->topCustomers($monthStart),
            'sales' => $this->salesPerformance($today),
            'production' => $this->productionPerformance($today),
            'inventory' => $this->inventoryHealth(),
            'finance' => $this->financeSnapshot($monthStart),
            'crm' => $this->crmPulse($monthStart),
            'hr' => $this->hrPulse(),
            'activity' => $this->recentActivity(),
            'quick_actions' => $this->quickActions(),
            'insights' => $this->smartInsights($monthStart, $today),
            'integrations' => app(IntegrationHealthPresenter::class)->build(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function context(): array
    {
        return [
            'company' => tenant()->company?->name ?? config('app.name'),
            'branch' => tenant()->branch?->name ?? __('All branches'),
            'role' => auth()->user()?->getRoleNames()->first() ?? __('None'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function kpiStrip(string $today, string $monthStart): array
    {
        $salesToday = (float) SalesOrder::query()->forTenant()
            ->whereDate('order_date', $today)
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->sum('total_amount');

        $salesMtd = (float) SalesOrder::query()->forTenant()
            ->whereDate('order_date', '>=', $monthStart)
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->sum('total_amount');

        $openQuotes = Quotation::query()->forTenant()->whereIn('status', [
            QuotationStatus::Draft,
            QuotationStatus::PendingApproval,
            QuotationStatus::Sent,
            QuotationStatus::Viewed,
        ])->count();

        $activeJobs = ProductionJobCard::query()->forTenant()->whereIn('status', [
            ProductionJobCardStatus::Queued,
            ProductionJobCardStatus::InProduction,
            ProductionJobCardStatus::QualityCheck,
            ProductionJobCardStatus::Rework,
            ProductionJobCardStatus::OnHold,
        ])->count();

        $completedJobs = ProductionJobCard::query()->forTenant()
            ->where('status', ProductionJobCardStatus::Completed)
            ->whereDate('updated_at', '>=', $monthStart)
            ->count();

        $inventoryValue = $this->inventoryValue();

        return [
            ['key' => 'sales_today', 'label' => __('Today Sales'), 'value' => $this->money($salesToday), 'route' => 'admin.sales-orders.index'],
            ['key' => 'sales_mtd', 'label' => __('MTD Sales'), 'value' => $this->money($salesMtd), 'route' => 'admin.sales-orders.index'],
            ['key' => 'open_quotes', 'label' => __('Open Quotes'), 'value' => (string) $openQuotes, 'route' => 'admin.quotations.index'],
            ['key' => 'active_jobs', 'label' => __('Active Jobs'), 'value' => (string) $activeJobs, 'route' => 'admin.production.job-cards.index'],
            ['key' => 'completed_jobs', 'label' => __('Completed Jobs'), 'value' => (string) $completedJobs, 'route' => 'admin.production.job-cards.index'],
            ['key' => 'receivables', 'label' => __('Receivables'), 'value' => '—', 'hint' => __('Finance module'), 'route' => null],
            ['key' => 'payables', 'label' => __('Payables'), 'value' => '—', 'hint' => __('Finance module'), 'route' => null],
            ['key' => 'inventory_value', 'label' => __('Inventory Value'), 'value' => $this->money($inventoryValue), 'route' => 'admin.inventory.dashboard'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function pipeline(): array
    {
        $stages = [
            [
                'key' => 'quotes',
                'label' => __('Quotes'),
                'count' => Quotation::query()->forTenant()->whereIn('status', [
                    QuotationStatus::Draft,
                    QuotationStatus::PendingApproval,
                    QuotationStatus::Sent,
                    QuotationStatus::Viewed,
                ])->count(),
                'route' => 'admin.quotations.index',
            ],
            [
                'key' => 'approved',
                'label' => __('Approved'),
                'count' => Quotation::query()->forTenant()->where('status', QuotationStatus::Accepted)->count()
                    + SalesOrder::query()->forTenant()->where('status', SalesOrderStatus::Confirmed)->count(),
                'route' => 'admin.sales-orders.index',
            ],
            [
                'key' => 'artwork',
                'label' => __('Artwork'),
                'count' => ArtworkRequest::query()->forTenant()->whereIn('status', [
                    ArtworkRequestStatus::Submitted,
                    ArtworkRequestStatus::RevisionRequested,
                    ArtworkRequestStatus::InDesign,
                ])->count(),
                'route' => 'admin.artwork.requests.index',
            ],
            [
                'key' => 'printing',
                'label' => __('Printing'),
                'count' => ProductionJobCard::query()->forTenant()
                    ->where('status', ProductionJobCardStatus::InProduction)->count(),
                'route' => 'admin.production.job-cards.index',
            ],
            [
                'key' => 'finishing',
                'label' => __('Finishing'),
                'count' => ProductionJobCard::query()->forTenant()
                    ->where('status', ProductionJobCardStatus::QualityCheck)->count(),
                'route' => 'admin.production.job-cards.index',
            ],
            [
                'key' => 'dispatch',
                'label' => __('Dispatch'),
                'count' => ProductionJobCard::query()->forTenant()
                    ->where('status', ProductionJobCardStatus::ReadyForDispatch)->count(),
                'route' => 'admin.production.job-cards.index',
            ],
            [
                'key' => 'delivered',
                'label' => __('Delivered'),
                'count' => SalesOrder::query()->forTenant()
                    ->whereIn('status', [SalesOrderStatus::Delivered, SalesOrderStatus::Closed])->count(),
                'route' => 'admin.sales-orders.index',
            ],
        ];

        $max = max(1, ...array_column($stages, 'count'));

        return array_map(function (array $stage) use ($max) {
            $stage['percent'] = (int) round(($stage['count'] / $max) * 100);

            return $stage;
        }, $stages);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function attentionCenter(string $today): array
    {
        return [
            [
                'key' => 'overdue_jobs',
                'label' => __('Overdue Jobs'),
                'count' => ProductionJobCard::query()->forTenant()
                    ->whereNotIn('status', [
                        ProductionJobCardStatus::Completed,
                        ProductionJobCardStatus::ReadyForDispatch,
                        ProductionJobCardStatus::Cancelled,
                    ])
                    ->whereDate('planned_end_date', '<', $today)
                    ->count(),
                'route' => 'admin.production.job-cards.index',
                'severity' => 'danger',
            ],
            [
                'key' => 'artwork_approvals',
                'label' => __('Pending Artwork Approvals'),
                'count' => ArtworkRequest::query()->forTenant()
                    ->where('status', ArtworkRequestStatus::Submitted)->count(),
                'route' => 'admin.artwork.requests.index',
                'severity' => 'danger',
            ],
            [
                'key' => 'pending_quotes',
                'label' => __('Pending Quotations'),
                'count' => Quotation::query()->forTenant()
                    ->where('status', QuotationStatus::PendingApproval)->count(),
                'route' => 'admin.quotations.index',
                'severity' => 'warning',
            ],
            [
                'key' => 'stock_alerts',
                'label' => __('Stock Alerts'),
                'count' => InventoryReorderAlert::query()->forTenant()->where('is_resolved', false)->count(),
                'route' => 'admin.inventory.dashboard',
                'severity' => 'danger',
            ],
            [
                'key' => 'critical_maintenance',
                'label' => __('Critical Maintenance'),
                'count' => MaintenanceWorkOrder::query()
                    ->forTenant()
                    ->where('priority', MaintenancePriority::Critical->value)
                    ->whereIn('status', [
                        MaintenanceWorkOrderStatus::Open->value,
                        MaintenanceWorkOrderStatus::Assigned->value,
                        MaintenanceWorkOrderStatus::InProgress->value,
                        MaintenanceWorkOrderStatus::WaitingParts->value,
                        MaintenanceWorkOrderStatus::WaitingVendor->value,
                    ])
                    ->count(),
                'route' => 'admin.assets.maintenance.dashboard',
                'severity' => 'danger',
            ],
            [
                'key' => 'public_quote_requests',
                'label' => __('Public Quote Requests'),
                'count' => PublicQuoteRequest::query()
                    ->where('status', PublicQuoteRequestStatus::Pending)
                    ->count(),
                'route' => 'admin.public-quote-requests.index',
                'severity' => 'warning',
            ],
            [
                'key' => 'public_contact_messages',
                'label' => __('Unread Contact Messages'),
                'count' => PublicContactMessage::query()
                    ->where('status', PublicContactMessageStatus::Unread)
                    ->count(),
                'route' => 'admin.public-contact-messages.index',
                'severity' => 'danger',
            ],
            [
                'key' => 'invoices',
                'label' => __('Outstanding Invoices'),
                'count' => null,
                'display' => '—',
                'route' => null,
                'severity' => 'muted',
                'hint' => __('Finance module'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function todayOperations(string $today): array
    {
        $queueBase = ProductionQueue::query()->forTenant();
        $activeQueues = (clone $queueBase)->whereIn('status', [
            ProductionQueueStatus::Assigned,
            ProductionQueueStatus::InProgress,
        ])->count();
        $totalQueues = max(1, (clone $queueBase)->count());
        $utilization = (int) round(($activeQueues / $totalQueues) * 100);

        return [
            'jobs_today' => ProductionJobCard::query()->forTenant()
                ->whereDate('planned_start_date', $today)->count(),
            'machine_utilization' => $utilization,
            'deliveries_today' => SalesOrder::query()->forTenant()
                ->where(function ($q) use ($today) {
                    $q->whereDate('required_date', $today)
                        ->orWhere(function ($q2) use ($today) {
                            $q2->where('status', SalesOrderStatus::Delivered)
                                ->whereDate('updated_at', $today);
                        });
                })->count(),
            'collections_expected' => null,
            'collections_display' => '—',
            'purchases_pending' => PurchaseRequest::query()->forTenant()
                ->where('status', PurchaseRequestStatus::Submitted)->count(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function branchPerformance(string $monthStart): array
    {
        $companyId = tenant()->companyId() ?? auth()->user()?->company_id;

        if (! $companyId) {
            return [];
        }

        $branches = Branch::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $salesByBranch = SalesOrder::query()
            ->where('company_id', $companyId)
            ->whereDate('order_date', '>=', $monthStart)
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->select('branch_id', DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        $jobsByBranch = ProductionJobCard::query()
            ->where('company_id', $companyId)
            ->whereDate('created_at', '>=', $monthStart)
            ->select('branch_id', DB::raw('COUNT(*) as jobs'))
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        $rows = $branches->map(function (Branch $branch) use ($salesByBranch, $jobsByBranch) {
            $sales = $salesByBranch->get($branch->id);

            return [
                'branch_id' => $branch->id,
                'name' => $branch->name,
                'sales' => $this->money((float) ($sales->revenue ?? 0)),
                'sales_raw' => (float) ($sales->revenue ?? 0),
                'jobs' => (int) ($jobsByBranch->get($branch->id)?->jobs ?? 0),
                'receivables' => '—',
                'profit' => '—',
            ];
        })->sortByDesc('sales_raw')->values()->all();

        if ($rows !== []) {
            $rows[0]['top'] = true;
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function topCustomers(string $monthStart): array
    {
        return SalesOrder::query()->forTenant()
            ->whereDate('order_date', '>=', $monthStart)
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('COUNT(*) as orders'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('customer_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $customer = Customer::query()->find($row->customer_id);

                return [
                    'name' => $customer?->company_name ?? __('Unknown'),
                    'orders' => (int) $row->orders,
                    'revenue' => $this->money((float) $row->revenue),
                    'outstanding' => '—',
                    'route' => $customer ? route('admin.crm.customers.show', $customer) : null,
                ];
            })
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function salesPerformance(string $today): array
    {
        $start = now()->subDays(29)->startOfDay();
        $orders = SalesOrder::query()->forTenant()
            ->whereDate('order_date', '>=', $start->toDateString())
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->select(DB::raw('DATE(order_date) as day'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('day')
            ->pluck('total', 'day');

        $chart = collect(range(0, 29))->map(function (int $offset) use ($start, $orders) {
            $day = $start->copy()->addDays($offset)->toDateString();

            return [
                'label' => Carbon::parse($day)->format('M j'),
                'value' => (float) ($orders[$day] ?? 0),
            ];
        })->all();

        $max = max(1.0, ...array_column($chart, 'value'));

        foreach ($chart as &$point) {
            $point['percent'] = (int) round(($point['value'] / $max) * 100);
        }
        unset($point);

        $quotesMtd = Quotation::query()->forTenant()
            ->whereDate('quotation_date', '>=', now()->startOfMonth()->toDateString())
            ->count();

        $ordersMtd = SalesOrder::query()->forTenant()
            ->whereDate('order_date', '>=', now()->startOfMonth()->toDateString())
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->count();

        $converted = Quotation::query()->forTenant()
            ->where('status', QuotationStatus::Converted)
            ->whereDate('updated_at', '>=', now()->startOfMonth()->toDateString())
            ->count();

        $conversion = $quotesMtd > 0 ? round(($converted / $quotesMtd) * 100) : 0;

        return [
            'chart' => $chart,
            'quotes_mtd' => $quotesMtd,
            'orders_mtd' => $ordersMtd,
            'conversion_rate' => $conversion,
            'revenue_trend' => $this->money((float) array_sum(array_column($chart, 'value'))),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function productionPerformance(string $today): array
    {
        $base = ProductionJobCard::query()->forTenant();

        $completedMtd = (clone $base)->where('status', ProductionJobCardStatus::Completed)
            ->whereDate('updated_at', '>=', now()->startOfMonth()->toDateString())
            ->count();

        $delayed = (clone $base)->whereNotIn('status', [
            ProductionJobCardStatus::Completed,
            ProductionJobCardStatus::ReadyForDispatch,
            ProductionJobCardStatus::Cancelled,
        ])->whereDate('planned_end_date', '<', $today)->count();

        $inProgress = (clone $base)->whereIn('status', [
            ProductionJobCardStatus::Queued,
            ProductionJobCardStatus::InProduction,
            ProductionJobCardStatus::QualityCheck,
            ProductionJobCardStatus::Rework,
        ])->count();

        $completedWithDates = (clone $base)
            ->where('status', ProductionJobCardStatus::Completed)
            ->whereNotNull('actual_start_date')
            ->whereNotNull('actual_end_date')
            ->whereDate('actual_end_date', '>=', now()->subDays(30)->toDateString())
            ->get(['actual_start_date', 'actual_end_date']);

        $avgTurnaround = '—';
        if ($completedWithDates->isNotEmpty()) {
            $hours = $completedWithDates->avg(fn ($job) => $job->actual_start_date->diffInHours($job->actual_end_date));
            $avgTurnaround = round($hours / 24, 1).' '.__('days');
        }

        $queueBase = ProductionQueue::query()->forTenant();
        $active = (clone $queueBase)->where('status', ProductionQueueStatus::InProgress)->count();
        $total = max(1, (clone $queueBase)->count());

        return [
            'completed_mtd' => $completedMtd,
            'avg_turnaround' => $avgTurnaround,
            'delayed' => $delayed,
            'in_progress' => $inProgress,
            'machine_utilization' => (int) round(($active / $total) * 100),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function inventoryHealth(): array
    {
        $companyId = tenant()->companyId();
        $branchId = tenant()->branchId();

        $items = InventoryItem::query()->forTenant()->where('is_active', true)->get([
            'id', 'company_id', 'branch_id', 'item_name', 'sku', 'reorder_level', 'standard_cost',
        ]);

        $lowStock = [];
        $outOfStock = [];
        $inventoryValue = 0.0;

        if ($companyId && $branchId) {
            $balances = InventoryStockService::branchBalancesMap((int) $companyId, (int) $branchId);

            foreach ($items as $item) {
                $balance = (float) ($balances[$item->id] ?? 0);
                $inventoryValue += $balance * (float) $item->standard_cost;

                if ($balance <= 0) {
                    $outOfStock[] = ['name' => $item->item_name, 'sku' => $item->sku];
                } elseif ($balance <= (float) $item->reorder_level) {
                    $lowStock[] = ['name' => $item->item_name, 'sku' => $item->sku, 'qty' => $balance];
                }
            }
        }

        $fastMoving = InventoryMovement::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->where('quantity', '<', 0)
            ->whereDate('created_at', '>=', now()->subDays(30)->toDateString())
            ->select('inventory_item_id', DB::raw('SUM(ABS(quantity)) as issued'))
            ->groupBy('inventory_item_id')
            ->orderByDesc('issued')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $item = InventoryItem::query()->find($row->inventory_item_id);

                return [
                    'name' => $item?->item_name ?? __('Item'),
                    'sku' => $item?->sku,
                    'issued' => (float) $row->issued,
                ];
            })
            ->all();

        return [
            'low_stock' => array_slice($lowStock, 0, 5),
            'out_of_stock' => array_slice($outOfStock, 0, 5),
            'dead_stock' => [],
            'fast_moving' => $fastMoving,
            'inventory_value' => $this->money($inventoryValue),
            'reorder_alerts' => InventoryReorderAlert::query()->forTenant()->where('is_resolved', false)->count(),
        ];
    }

    protected function inventoryValue(): float
    {
        $companyId = tenant()->companyId();
        $branchId = tenant()->branchId();

        if (! $companyId || ! $branchId) {
            return 0.0;
        }

        $items = InventoryItem::query()->forTenant()->where('is_active', true)->get(['id', 'standard_cost']);
        $balances = InventoryStockService::branchBalancesMap((int) $companyId, (int) $branchId);
        $total = 0.0;

        foreach ($items as $item) {
            $total += ((float) ($balances[$item->id] ?? 0)) * (float) $item->standard_cost;
        }

        return round($total, 2);
    }

    /**
     * @return array<string, mixed>
     */
    protected function financeSnapshot(string $monthStart): array
    {
        $revenueMtd = (float) SalesOrder::query()->forTenant()
            ->whereDate('order_date', '>=', $monthStart)
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->sum('total_amount');

        return [
            'revenue_mtd' => $this->money($revenueMtd),
            'revenue_raw' => $revenueMtd,
            'expenses_mtd' => '—',
            'profit_mtd' => '—',
            'receivables' => '—',
            'payables' => '—',
            'cash_position' => '—',
            'finance_module' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function crmPulse(string $monthStart): array
    {
        $quotesSent = Quotation::query()->forTenant()
            ->whereIn('status', [QuotationStatus::Sent, QuotationStatus::Viewed, QuotationStatus::Accepted, QuotationStatus::Converted])
            ->whereDate('quotation_date', '>=', $monthStart)
            ->count();

        $quotesTotal = Quotation::query()->forTenant()
            ->whereDate('quotation_date', '>=', $monthStart)
            ->count();

        $converted = Quotation::query()->forTenant()
            ->where('status', QuotationStatus::Converted)
            ->whereDate('updated_at', '>=', $monthStart)
            ->count();

        return [
            'open_leads' => Lead::query()->forTenant()->where('status', LeadStatus::Open)->count(),
            'customers_added' => Customer::query()->forTenant()->whereDate('created_at', '>=', $monthStart)->count(),
            'quotes_sent' => $quotesSent,
            'conversion_rate' => $quotesTotal > 0 ? round(($converted / $quotesTotal) * 100).'%' : '0%',
            'lost_opportunities' => Lead::query()->forTenant()->where('status', LeadStatus::Lost)->whereDate('updated_at', '>=', $monthStart)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function hrPulse(): array
    {
        $companyId = tenant()->companyId() ?? auth()->user()?->company_id;

        if (! $companyId) {
            return [
                'present' => 0,
                'on_leave' => 0,
                'contract_expiry' => 0,
                'performance_alerts' => 0,
            ];
        }

        $base = Employee::query()->where('company_id', $companyId)->where('is_active', true);

        return [
            'present' => (clone $base)->where('employment_status', EmploymentStatus::Active)->count(),
            'on_leave' => (clone $base)->where('employment_status', EmploymentStatus::OnLeave)->count(),
            'contract_expiry' => 0,
            'performance_alerts' => 0,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function recentActivity(): array
    {
        return ActivityLog::query()
            ->forTenant()
            ->with('user')
            ->latest('created_at')
            ->limit(20)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'user_name' => $log->user?->name,
                'action' => $log->action,
                'model_type' => $log->model_type,
                'model_id' => $log->model_id,
                'message' => $this->activityMessage($log),
                'created_at' => $log->created_at?->toIso8601String(),
                'ip_address' => $log->ip_address,
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function quickActions(): array
    {
        return [
            ['label' => __('Create Quote'), 'route' => 'admin.quotations.create', 'permission' => 'quotations.create'],
            ['label' => __('Create Customer'), 'route' => 'admin.crm.customers.create', 'permission' => 'crm.customers.create'],
            ['label' => __('Create Job'), 'route' => 'admin.production.job-cards.create', 'permission' => 'production.create'],
            ['label' => __('Receive Stock'), 'route' => 'admin.inventory.receipts.create', 'permission' => 'inventory.receive'],
            ['label' => __('Create Invoice'), 'route' => null, 'coming_soon' => true],
            ['label' => __('Record Payment'), 'route' => null, 'coming_soon' => true],
            ['label' => __('Create Purchase Order'), 'route' => 'admin.procurement.orders.create', 'permission' => 'procurement.orders.create'],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    protected function smartInsights(string $monthStart, string $today): array
    {
        $insights = [];

        $revenueMtd = (float) SalesOrder::query()->forTenant()
            ->whereDate('order_date', '>=', $monthStart)
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->sum('total_amount');

        $prevStart = now()->subMonth()->startOfMonth()->toDateString();
        $prevEnd = now()->subMonth()->endOfMonth()->toDateString();
        $revenuePrev = (float) SalesOrder::query()->forTenant()
            ->whereBetween('order_date', [$prevStart, $prevEnd])
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->sum('total_amount');

        if ($revenuePrev > 0) {
            $delta = round((($revenueMtd - $revenuePrev) / $revenuePrev) * 100);
            $insights[] = [
                'tone' => $delta >= 0 ? 'success' : 'warning',
                'text' => $delta >= 0
                    ? __('Revenue up :percent% this month (order value proxy).', ['percent' => abs($delta)])
                    : __('Revenue down :percent% vs last month (order value proxy).', ['percent' => abs($delta)]),
            ];
        }

        $delayed = ProductionJobCard::query()->forTenant()
            ->whereNotIn('status', [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Cancelled,
            ])
            ->whereDate('planned_end_date', '<', now()->subHours(48)->toDateString())
            ->count();

        if ($delayed > 0) {
            $insights[] = [
                'tone' => 'danger',
                'text' => __(':count jobs delayed more than 48 hours.', ['count' => $delayed]),
            ];
        }

        $topCustomer = $this->topCustomers($monthStart)[0] ?? null;
        if ($topCustomer && $revenueMtd > 0) {
            $share = SalesOrder::query()->forTenant()
                ->whereDate('order_date', '>=', $monthStart)
                ->whereNotNull('customer_id')
                ->select('customer_id', DB::raw('SUM(total_amount) as revenue'))
                ->groupBy('customer_id')
                ->orderByDesc('revenue')
                ->first();

            if ($share && $share->revenue > 0) {
                $pct = (int) round(((float) $share->revenue / $revenueMtd) * 100);
                if ($pct >= 15) {
                    $name = Customer::query()->find($share->customer_id)?->company_name ?? __('A customer');
                    $insights[] = [
                        'tone' => 'info',
                        'text' => __(':customer contributes :percent% of MTD sales.', ['customer' => $name, 'percent' => $pct]),
                    ];
                }
            }
        }

        $alerts = InventoryReorderAlert::query()->forTenant()->where('is_resolved', false)->count();
        if ($alerts > 0) {
            $insights[] = [
                'tone' => 'warning',
                'text' => __(':count inventory items below reorder level.', ['count' => $alerts]),
            ];
        }

        if ($insights === []) {
            $insights[] = [
                'tone' => 'muted',
                'text' => __('Operations stable. No critical alerts detected today.'),
            ];
        }

        return $insights;
    }

    protected function activityMessage(ActivityLog $log): string
    {
        $user = $log->user?->name ?? __('System');
        $subject = $log->model_type ? class_basename($log->model_type) : __('record');
        $ref = $log->properties['number'] ?? $log->properties['name'] ?? ($log->model_id ? '#'.$log->model_id : '');

        return trim("{$user} {$log->action} {$subject} {$ref}");
    }

    protected function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }
}
