<?php

namespace App\Services\Crm;

use App\Enums\ArtworkRequestStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerActivity;
use App\Models\Crm\CustomerFile;
use App\Models\Crm\CustomerNote;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Dispatch\DeliveryNote;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Models\Sales\SalesOrder;
use App\Support\Sales\CustomerFinancialIntelligenceService;
use App\Support\Sales\CustomerFinancialWorkspaceService;
use App\Services\Accounting\DeliveryInvoiceEligibilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * @deprecated Superseded by customers/360/ views and Communications-integrated Customer 360.
 *             Retained for timeline tab constant references only. Pending safe removal.
 */
class Customer360WorkspaceService
{
    public const TAB_OVERVIEW = 'overview';

    public const TAB_QUOTATIONS = 'quotations';

    public const TAB_SALES_ORDERS = 'sales-orders';

    public const TAB_ARTWORK = 'artwork';

    public const TAB_PRODUCTION = 'production';

    public const TAB_DELIVERIES = 'deliveries';

    public const TAB_FILES = 'files';

    public const TAB_NOTES = 'notes';

    public const TAB_ACTIVITIES = 'activities';

    public const TAB_TIMELINE = 'timeline';

    public const TAB_FINANCIAL = 'financial';

    public const TAB_PAYMENTS = 'payments';

    /** @var list<string> */
    public const TABS = [
        self::TAB_OVERVIEW,
        self::TAB_TIMELINE,
        self::TAB_QUOTATIONS,
        self::TAB_SALES_ORDERS,
        self::TAB_FINANCIAL,
        self::TAB_PAYMENTS,
        self::TAB_ARTWORK,
        self::TAB_PRODUCTION,
        self::TAB_DELIVERIES,
        self::TAB_FILES,
        self::TAB_NOTES,
        self::TAB_ACTIVITIES,
    ];

    /**
     * @return array<string, mixed>
     */
    /**
     * @param  array<string, mixed>  $timelineQuery
     * @return array<string, mixed>
     */
    public function build(Customer $customer, ?string $tab = null, array $timelineQuery = [], array $financialQuery = []): array
    {
        $activeTab = $this->resolveTab($tab);

        $customer->loadMissing(['segments', 'branch', 'company', 'contacts']);

        return [
            'customer' => $customer,
            'header' => $this->header($customer),
            'kpis' => $this->kpis($customer),
            'quick_actions' => $this->quickActions($customer),
            'active_tab' => $activeTab,
            'tabs' => $this->tabNavigation($customer, $activeTab),
            'tab_data' => $this->tabData($customer, $activeTab, $timelineQuery, $financialQuery),
        ];
    }

    public function resolveTab(?string $tab): string
    {
        $tab = $tab ?? self::TAB_OVERVIEW;

        return in_array($tab, self::TABS, true) ? $tab : self::TAB_OVERVIEW;
    }

    /**
     * @return array<string, mixed>
     */
    protected function header(Customer $customer): array
    {
        $primaryContact = $customer->contacts->firstWhere('is_primary', true)
            ?? $customer->contacts->first();

        return [
            'name' => $customer->company_name,
            'code' => $customer->customer_code,
            'company' => $customer->company?->name,
            'branch' => $customer->branch?->name,
            'segments' => $customer->segments->pluck('name')->all(),
            'status' => $customer->status,
            'contact_person' => $customer->contact_person ?: $primaryContact?->name,
            'email' => $customer->email ?: $primaryContact?->email,
            'phone' => $customer->phone ?: $primaryContact?->phone,
            'credit_limit' => $customer->credit_limit,
            'payment_terms' => $customer->payment_terms,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function kpis(Customer $customer): array
    {
        return [
            $this->quotationKpis($customer),
            $this->salesOrderKpis($customer),
            $this->artworkKpis($customer),
            $this->productionKpis($customer),
            $this->deliveryKpis($customer),
            $this->financialKpis($customer),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function quotationKpis(Customer $customer): array
    {
        $base = $this->customerScoped(Quotation::query(), $customer);

        $counts = (clone $base)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $pending = $this->sumStatuses($counts, [
            QuotationStatus::Draft,
            QuotationStatus::PendingApproval,
            QuotationStatus::Sent,
            QuotationStatus::Viewed,
        ]);

        $approved = $this->sumStatuses($counts, [
            QuotationStatus::Accepted,
            QuotationStatus::Converted,
        ]);

        $rejected = $this->sumStatuses($counts, [
            QuotationStatus::Rejected,
            QuotationStatus::Expired,
        ]);

        return [
            'key' => 'quotations',
            'label' => __('Quotations'),
            'icon' => 'document-text',
            'metrics' => [
                ['label' => __('Total'), 'value' => (int) $counts->sum()],
                ['label' => __('Pending'), 'value' => $pending],
                ['label' => __('Approved'), 'value' => $approved],
                ['label' => __('Rejected'), 'value' => $rejected],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function salesOrderKpis(Customer $customer): array
    {
        $base = $this->customerScoped(SalesOrder::query(), $customer);

        $counts = (clone $base)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $open = $this->sumStatuses($counts, [
            SalesOrderStatus::Draft,
            SalesOrderStatus::Confirmed,
            SalesOrderStatus::ReadyForProduction,
            SalesOrderStatus::OnHold,
        ]);

        $inProduction = (int) ($counts[SalesOrderStatus::InProduction->value] ?? 0);

        $completed = $this->sumStatuses($counts, [
            SalesOrderStatus::Completed,
            SalesOrderStatus::Delivered,
            SalesOrderStatus::Closed,
        ]);

        return [
            'key' => 'sales_orders',
            'label' => __('Sales Orders'),
            'icon' => 'shopping-cart',
            'metrics' => [
                ['label' => __('Total'), 'value' => (int) $counts->sum()],
                ['label' => __('Open'), 'value' => $open],
                ['label' => __('In production'), 'value' => $inProduction],
                ['label' => __('Completed'), 'value' => $completed],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function artworkKpis(Customer $customer): array
    {
        $base = $this->customerScoped(ArtworkRequest::query(), $customer);

        $counts = (clone $base)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $pendingApproval = (int) ($counts[ArtworkRequestStatus::Submitted->value] ?? 0);
        $approved = (int) ($counts[ArtworkRequestStatus::Approved->value] ?? 0);

        $revisions = (clone $base)
            ->where(function (Builder $query) {
                $query->where('status', ArtworkRequestStatus::RevisionRequested)
                    ->orWhere('current_version', '>', 1);
            })
            ->count();

        return [
            'key' => 'artwork',
            'label' => __('Artwork'),
            'icon' => 'color-swatch',
            'metrics' => [
                ['label' => __('Pending approval'), 'value' => $pendingApproval],
                ['label' => __('Approved'), 'value' => $approved],
                ['label' => __('Revisions'), 'value' => $revisions],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function productionKpis(Customer $customer): array
    {
        $base = $this->customerScoped(ProductionJobCard::query(), $customer);

        $counts = (clone $base)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $active = $this->sumStatuses($counts, [
            ProductionJobCardStatus::Queued,
            ProductionJobCardStatus::InProduction,
            ProductionJobCardStatus::QualityCheck,
            ProductionJobCardStatus::Rework,
            ProductionJobCardStatus::OnHold,
        ]);

        $completed = $this->sumStatuses($counts, [
            ProductionJobCardStatus::Completed,
            ProductionJobCardStatus::ReadyForDispatch,
        ]);

        return [
            'key' => 'production',
            'label' => __('Production'),
            'icon' => 'cog',
            'metrics' => [
                ['label' => __('Active jobs'), 'value' => $active],
                ['label' => __('Completed jobs'), 'value' => $completed],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function deliveryKpis(Customer $customer): array
    {
        if (Schema::hasTable('delivery_notes') && $this->userCanViewDeliveries()) {
            $base = $this->customerScoped(DeliveryNote::query(), $customer);

            $dispatched = (clone $base)
                ->whereIn('status', [DeliveryNoteStatus::Dispatched, DeliveryNoteStatus::Delivered])
                ->count();

            $billing = app(DeliveryInvoiceEligibilityService::class)
                ->customerBillingCounts($customer->id, $customer->company_id);

            return [
                'key' => 'delivery',
                'label' => __('Delivery'),
                'icon' => 'truck',
                'metrics' => [
                    ['label' => __('Delivered not invoiced'), 'value' => $billing['delivered_not_invoiced']],
                    ['label' => __('Delivered invoiced'), 'value' => $billing['delivered_invoiced']],
                    ['label' => __('Dispatched'), 'value' => $dispatched],
                ],
            ];
        }

        return [
            'key' => 'delivery',
            'label' => __('Delivery'),
            'icon' => 'truck',
            'metrics' => [
                ['label' => __('Dispatched'), 'value' => 0],
                ['label' => __('Delivered jobs'), 'value' => 0],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function financialKpis(Customer $customer): array
    {
        if (! Schema::hasTable('customer_invoices') || ! $this->userCanViewInvoices()) {
            return [
                'key' => 'financial',
                'label' => __('Financial Summary'),
                'icon' => 'currency-dollar',
                'placeholder' => __('Available after Accounting Activation'),
                'metrics' => [],
            ];
        }

        $profile = app(CustomerFinancialIntelligenceService::class)->profile($customer);
        $risk = strtoupper($profile['collection_risk']);

        return [
            'key' => 'financial',
            'label' => __('Financial Summary'),
            'icon' => 'currency-dollar',
            'metrics' => [
                ['label' => __('Total Invoiced'), 'value' => number_format($profile['total_invoiced'], 2)],
                ['label' => __('Total Paid'), 'value' => number_format($profile['total_paid'], 2)],
                ['label' => __('Outstanding'), 'value' => number_format($profile['outstanding'], 2)],
                ['label' => __('Customer Credit'), 'value' => number_format($profile['credit_balance'], 2)],
                ['label' => __('Overdue'), 'value' => number_format($profile['overdue_amount'] ?? 0, 2)],
                ['label' => __('Collection Risk'), 'value' => $risk],
            ],
            'warning' => $profile['collection_risk'] === 'high'
                ? __('High collection risk — review overdue balances.')
                : ($profile['collection_risk'] === 'medium' ? __('Medium collection risk — follow up on overdue invoices.') : null),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function quickActions(Customer $customer): array
    {
        $user = auth()->user();
        $actions = [];

        if ($user?->can('quotations.create')) {
            $actions[] = [
                'label' => __('New Quote'),
                'url' => route('admin.quotations.create', ['customer_id' => $customer->id]),
            ];
        }

        if ($user?->can('sales_orders.create')) {
            $actions[] = [
                'label' => __('New Sales Order'),
                'url' => route('admin.sales-orders.create'),
            ];
        }

        if ($user?->can('artwork.create')) {
            $actions[] = [
                'label' => __('New Artwork Request'),
                'url' => route('admin.artwork.create', ['customer_id' => $customer->id]),
            ];
        }

        if ($user?->can('invoices.create')) {
            $actions[] = [
                'label' => __('New Invoice'),
                'url' => route('admin.accounting.invoices.create', ['customer_id' => $customer->id]),
            ];
        }

        if ($user?->can('payments.create')) {
            $actions[] = [
                'label' => __('Record Payment'),
                'url' => route('admin.accounting.payments.create', ['customer_id' => $customer->id]),
            ];
        }

        if ($user?->can('crm.customers.edit')) {
            $actions[] = [
                'label' => __('Upload File'),
                'url' => route('admin.crm.customers.show', ['customer' => $customer, 'tab' => self::TAB_FILES]),
                'scroll' => 'upload-file',
            ];
            $actions[] = [
                'label' => __('Add Note'),
                'url' => route('admin.crm.customers.show', ['customer' => $customer, 'tab' => self::TAB_NOTES]),
                'scroll' => 'add-note',
            ];
        }

        if ($user?->can('commercial.activities.create')) {
            $actions[] = [
                'label' => __('Log Activity'),
                'url' => route('admin.crm.customers.show', ['customer' => $customer, 'tab' => self::TAB_ACTIVITIES]),
                'scroll' => 'log-activity',
            ];
        }

        return $actions;
    }

    /**
     * @return list<array<string, string>>
     */
    protected function tabNavigation(Customer $customer, string $activeTab): array
    {
        return collect(self::TABS)
            ->map(fn (string $tab) => [
                'id' => $tab,
                'label' => $this->tabLabel($tab),
                'url' => route('admin.crm.customers.show', ['customer' => $customer, 'tab' => $tab]),
                'active' => $tab === $activeTab,
            ])
            ->all();
    }

    protected function tabLabel(string $tab): string
    {
        return match ($tab) {
            self::TAB_OVERVIEW => __('Overview'),
            self::TAB_QUOTATIONS => __('Quotations'),
            self::TAB_SALES_ORDERS => __('Sales Orders'),
            self::TAB_FINANCIAL => __('Financial Summary'),
            self::TAB_PAYMENTS => __('Payments'),
            self::TAB_ARTWORK => __('Artwork'),
            self::TAB_PRODUCTION => __('Production'),
            self::TAB_DELIVERIES => __('Delivery history'),
            self::TAB_FILES => __('Files'),
            self::TAB_NOTES => __('Notes'),
            self::TAB_ACTIVITIES => __('Activities'),
            self::TAB_TIMELINE => __('Timeline'),
            default => ucfirst($tab),
        };
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @param  array<string, mixed>  $timelineQuery
     * @return array<string, mixed>
     */
    protected function tabData(Customer $customer, string $tab, array $timelineQuery = [], array $financialQuery = []): array
    {
        return match ($tab) {
            self::TAB_OVERVIEW => $this->overviewTab($customer),
            self::TAB_QUOTATIONS => $this->quotationsTab($customer),
            self::TAB_SALES_ORDERS => $this->salesOrdersTab($customer),
            self::TAB_FINANCIAL => $this->financialTab($customer, $financialQuery),
            self::TAB_PAYMENTS => $this->paymentsTab($customer),
            self::TAB_ARTWORK => $this->artworkTab($customer),
            self::TAB_PRODUCTION => $this->productionTab($customer),
            self::TAB_DELIVERIES => $this->deliveriesTab($customer),
            self::TAB_FILES => $this->filesTab($customer),
            self::TAB_NOTES => $this->notesTab($customer),
            self::TAB_ACTIVITIES => $this->activitiesTab($customer),
            self::TAB_TIMELINE => $this->timelineTab($customer, $timelineQuery),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function overviewTab(Customer $customer): array
    {
        return [
            'profile' => [
                'type' => $customer->customer_type,
                'address' => $customer->physical_address,
                'city' => $customer->city,
                'website' => $customer->website,
                'kra_pin' => $customer->kra_pin,
            ],
            'recent_quotations' => $this->recentQuotations($customer),
            'recent_orders' => $this->recentSalesOrders($customer),
            'recent_artwork' => $this->recentArtwork($customer),
            'recent_jobs' => $this->recentJobCards($customer),
        ];
    }

    /**
     * @return Collection<int, Quotation>
     */
    protected function recentQuotations(Customer $customer): Collection
    {
        if (! $this->userCanViewQuotations()) {
            return collect();
        }

        return $this->customerScoped(Quotation::query(), $customer)
            ->select(['id', 'quotation_number', 'quotation_date', 'status', 'total_amount', 'currency'])
            ->latest('quotation_date')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, SalesOrder>
     */
    protected function recentSalesOrders(Customer $customer): Collection
    {
        if (! $this->userCanViewSalesOrders()) {
            return collect();
        }

        return $this->customerScoped(SalesOrder::query(), $customer)
            ->select(['id', 'order_number', 'order_date', 'status', 'total_amount'])
            ->latest('order_date')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, ArtworkRequest>
     */
    protected function recentArtwork(Customer $customer): Collection
    {
        if (! $this->userCanViewArtwork()) {
            return collect();
        }

        return $this->customerScoped(ArtworkRequest::query(), $customer)
            ->select(['id', 'request_number', 'title', 'status', 'current_version', 'due_date'])
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, ProductionJobCard>
     */
    protected function recentJobCards(Customer $customer): Collection
    {
        if (! $this->userCanViewProduction()) {
            return collect();
        }

        return $this->customerScoped(ProductionJobCard::query(), $customer)
            ->select(['id', 'job_card_number', 'status', 'priority', 'planned_end_date'])
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function quotationsTab(Customer $customer): array
    {
        if (! $this->userCanViewQuotations()) {
            return ['restricted' => true];
        }

        $quotations = $this->customerScoped(
            Quotation::query()
                ->select([
                    'id', 'quotation_number', 'quotation_date', 'status',
                    'total_amount', 'currency', 'customer_id',
                ])
                ->withExists('salesOrder'),
            $customer,
        )
            ->latest('quotation_date')
            ->paginate(25, pageName: 'quotations_page');

        return ['quotations' => $quotations];
    }

    /**
     * @return array<string, mixed>
     */
    protected function salesOrdersTab(Customer $customer): array
    {
        if (! $this->userCanViewSalesOrders()) {
            return ['restricted' => true];
        }

        $orders = $this->customerScoped(
            SalesOrder::query()
                ->select([
                    'id', 'order_number', 'order_date', 'status', 'total_amount', 'customer_id',
                ])
                ->with(['jobCard:id,sales_order_id,status']),
            $customer,
        )
            ->latest('order_date')
            ->paginate(25, pageName: 'orders_page');

        return ['orders' => $orders];
    }

    /**
     * @return array<string, mixed>
     */
    protected function financialTab(Customer $customer, array $query = []): array
    {
        $user = auth()->user();

        if (! $user) {
            return ['restricted' => true];
        }

        return app(CustomerFinancialWorkspaceService::class)->build($customer, $user, $query);
    }

    /**
     * @return array<string, mixed>
     */
    protected function paymentsTab(Customer $customer): array
    {
        if (! $this->userCanViewPayments() || ! Schema::hasTable('customer_payments')) {
            return ['restricted' => true];
        }

        $payments = $this->customerScoped(
            CustomerPayment::query()
                ->select([
                    'id', 'payment_number', 'payment_date', 'amount', 'unallocated_amount',
                    'status', 'customer_id',
                ]),
            $customer,
        )
            ->withSum('allocations as allocated_sum', 'allocated_amount')
            ->latest('payment_date')
            ->paginate(25, pageName: 'payments_page');

        return ['payments' => $payments];
    }

    /**
     * @return array<string, mixed>
     */
    protected function artworkTab(Customer $customer): array
    {
        if (! $this->userCanViewArtwork()) {
            return ['restricted' => true];
        }

        $requests = $this->customerScoped(
            ArtworkRequest::query()
                ->select([
                    'id', 'request_number', 'title', 'status', 'current_version',
                    'due_date', 'customer_id',
                ])
                ->withCount('files')
                ->with(['approvals' => fn ($q) => $q->latest('created_at')->limit(1)]),
            $customer,
        )
            ->latest()
            ->paginate(25, pageName: 'artwork_page');

        return ['requests' => $requests];
    }

    /**
     * @return array<string, mixed>
     */
    protected function deliveriesTab(Customer $customer): array
    {
        if (! $this->userCanViewDeliveries() || ! Schema::hasTable('delivery_notes')) {
            return ['restricted' => true];
        }

        $notes = $this->customerScoped(
            DeliveryNote::query()
                ->with([
                    'productionJobCard:id,job_card_number',
                    'salesOrder:id,order_number',
                ])
                ->select([
                    'id', 'delivery_note_number', 'delivery_date', 'status',
                    'customer_id', 'production_job_card_id', 'sales_order_id',
                    'dispatched_at', 'delivered_at',
                ]),
            $customer,
        )
            ->latest('delivery_date')
            ->paginate(25, pageName: 'deliveries_page');

        return ['notes' => $notes];
    }

    protected function productionTab(Customer $customer): array
    {
        if (! $this->userCanViewProduction()) {
            return ['restricted' => true];
        }

        $jobs = $this->customerScoped(
            ProductionJobCard::query()
                ->select([
                    'id', 'job_card_number', 'priority', 'status', 'customer_id',
                    'planned_end_date',
                ])
                ->with([
                    'queues' => fn ($q) => $q->select(['id', 'production_job_card_id', 'work_center_id', 'status'])
                        ->with('workCenter:id,name')
                        ->orderBy('queue_position')
                        ->limit(1),
                    'operations' => fn ($q) => $q->select(['id', 'production_job_card_id', 'ended_at']),
                ])
                ->withCount([
                    'operations',
                    'operations as completed_operations_count' => fn ($q) => $q->whereNotNull('ended_at'),
                ]),
            $customer,
        )
            ->latest()
            ->paginate(25, pageName: 'production_page');

        $jobs->getCollection()->transform(function (ProductionJobCard $job) {
            $job->setAttribute('progress_percent', $this->productionProgress($job));

            return $job;
        });

        return ['jobs' => $jobs];
    }

    /**
     * @return array<string, mixed>
     */
    protected function filesTab(Customer $customer): array
    {
        $files = CustomerFile::query()
            ->where('customer_id', $customer->id)
            ->where('company_id', $customer->company_id)
            ->with('uploader:id,name')
            ->latest()
            ->paginate(25, pageName: 'files_page');

        return ['files' => $files];
    }

    /**
     * @return array<string, mixed>
     */
    protected function notesTab(Customer $customer): array
    {
        $notes = CustomerNote::query()
            ->where('customer_id', $customer->id)
            ->where('company_id', $customer->company_id)
            ->with('user:id,name')
            ->latest()
            ->paginate(25, pageName: 'notes_page');

        return ['notes' => $notes];
    }

    /**
     * @return array<string, mixed>
     */
    protected function activitiesTab(Customer $customer): array
    {
        $activities = CustomerActivity::query()
            ->where('customer_id', $customer->id)
            ->where('company_id', $customer->company_id)
            ->with('user:id,name')
            ->latest('activity_at')
            ->paginate(25, pageName: 'activities_page');

        return ['activities' => $activities];
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @param  array<string, mixed>  $timelineQuery
     * @return array<string, mixed>
     */
    protected function timelineTab(Customer $customer, array $timelineQuery = []): array
    {
        return array_merge(
            app(CustomerTimelineService::class)->paginate(
                $customer,
                $timelineQuery['timeline_filter'] ?? null,
                $timelineQuery['timeline_search'] ?? null,
                isset($timelineQuery['timeline_page']) ? (int) $timelineQuery['timeline_page'] : null,
            ),
            ['ready' => true],
        );
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function customerScoped(Builder $query, Customer $customer): Builder
    {
        return $query
            ->where('customer_id', $customer->id)
            ->where('company_id', $customer->company_id)
            ->when(
                $customer->branch_id,
                fn (Builder $q) => $q->where('branch_id', $customer->branch_id),
            );
    }

    /**
     * @param  Collection<string|int, mixed>  $counts
     * @param  list<\BackedEnum>  $statuses
     */
    protected function sumStatuses(Collection $counts, array $statuses): int
    {
        return collect($statuses)->sum(fn ($status) => (int) ($counts[$status->value] ?? 0));
    }

    protected function userCanViewQuotations(): bool
    {
        return auth()->user()?->can('quotations.view') ?? false;
    }

    protected function userCanViewSalesOrders(): bool
    {
        return auth()->user()?->can('sales_orders.view') ?? false;
    }

    protected function userCanViewArtwork(): bool
    {
        return auth()->user()?->can('artwork.view') ?? false;
    }

    protected function userCanViewProduction(): bool
    {
        return auth()->user()?->can('production.view') ?? false;
    }

    protected function userCanViewInvoices(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->can('invoices.view') || $user->can('accounting.view'));
    }

    protected function userCanViewPayments(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->can('payments.view') || $user->can('accounting.view'));
    }

    protected function userCanViewDeliveries(): bool
    {
        return auth()->user()?->can('dispatch.view') ?? false;
    }

    public function productionProgress(ProductionJobCard $job): int
    {
        $total = (int) ($job->operations_count ?? 0);

        if ($total === 0) {
            return match ($job->status) {
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch => 100,
                ProductionJobCardStatus::Draft => 0,
                default => 25,
            };
        }

        $completed = (int) ($job->completed_operations_count ?? 0);

        return (int) min(100, round(($completed / $total) * 100));
    }
}
