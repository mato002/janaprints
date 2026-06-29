@php
    use App\Enums\CustomerInvoiceStatus;
    use App\Enums\CustomerInvoiceType;
    use App\Enums\ProductionJobCardStatus;
    use App\Enums\QuotationStatus;
    use App\Enums\SalesOrderStatus;
    use App\Models\Artwork\ArtworkRequest;
    use App\Models\Communications\CommunicationLog;
    use App\Models\Production\ProductionJobCard;
    use App\Models\Crm\CustomerArtwork;
    use App\Models\Sales\CustomerInvoice;
    use App\Models\Sales\CustomerPayment;
    use App\Models\Sales\Quotation;
    use App\Models\Sales\SalesOrder;
    use App\Support\Commercial\Intelligence\CommercialCustomerProfitabilityService;
    use App\Support\Sales\CustomerFinancialIntelligenceService;
    use App\Support\Commercial\ComplaintService;
    use App\Support\Communications\Email\EmailVisibilityService;
    use App\Support\Communications\CommunicationLogService;
    use App\Support\EnumLabel;

    $user = auth()->user();

    $canQuotes = $user->can('quotations.view');
    $canComplaints = $user->can('commercial.complaints.view');
    $canOrders = $user->can('sales_orders.view');
    $canInvoices = $user->can('invoices.view');
    $canPayments = $user->can('payments.view');
    $canArtwork = $user->can('artwork.view');
    $canJobs = $user->can('production.view');
    $canCommLogs = $user->can('communications.logs.view');
    $canEmailView = $user->can('communications.email.view');

    $commercialIntelligence = ($canInvoices || $canOrders)
        ? app(CommercialCustomerProfitabilityService::class)->profile($customer)
        : null;

    $commercialRecentJobs = $canJobs
        ? app(CommercialCustomerProfitabilityService::class)->recentJobs($customer, 5)
        : [];

    $financialSummary = $canInvoices
        ? app(CustomerFinancialIntelligenceService::class)->profile($customer)
        : null;

    $logService = app(CommunicationLogService::class);

    $quotesTotal = $canQuotes ? Quotation::query()->forTenant()->where('customer_id', $customer->id)->count() : null;
    $ordersTotal = $canOrders ? SalesOrder::query()->forTenant()->where('customer_id', $customer->id)->count() : null;
    $invoicesTotal = $canInvoices ? CustomerInvoice::query()->forTenant()->where('customer_id', $customer->id)->count() : null;
    $paymentsTotal = $canPayments ? CustomerPayment::query()->forTenant()->where('customer_id', $customer->id)->count() : null;
    $libraryArtworkTotal = null;

    $printSpecificationsTotal = $user->can('crm.customers.view')
        ? \App\Models\Crm\CustomerPrintSpecification::query()
            ->forTenant()
            ->where('customer_id', $customer->id)
            ->where('status', \App\Enums\CustomerPrintSpecificationStatus::Active)
            ->count()
        : null;

    $receiptsTotal = $canPayments
        ? CustomerPayment::query()->forTenant()->where('customer_id', $customer->id)->whereNotNull('receipt_number')->count()
        : null;

    $artworkTotal = $canArtwork ? ArtworkRequest::query()->forTenant()->where('customer_id', $customer->id)->count() : null;

    $revenueTotal = $canInvoices
        ? (float) CustomerInvoice::query()
            ->forTenant()
            ->where('customer_id', $customer->id)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->where('invoice_type', '!=', CustomerInvoiceType::CreditNote->value)
            ->sum('total_amount')
        : null;

    $outstandingBalance = $canInvoices
        ? (float) CustomerInvoice::query()
            ->forTenant()
            ->where('customer_id', $customer->id)
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
            ->sum('balance_due')
        : null;

    $openConversations = ($inboxConversations->count() ?? 0) + ($whatsappConversations->count() ?? 0);

    $openComplaints = $canComplaints
        ? app(ComplaintService::class)->openComplaintCountForCustomer($customer->company_id, $customer->id)
        : null;

    $lastActivityAt = collect([
        $customer->updated_at,
        $customer->customerNotes->max('created_at'),
        $customer->activities->max('activity_at'),
        $canCommLogs ? CommunicationLog::query()
            ->where('company_id', $customer->company_id)
            ->whereHas('recipients', fn ($q) => $q->where('recipient_type', 'customer')->where('recipient_id', $customer->id))
            ->orderByDesc('created_at')
            ->value('created_at') : null,
    ])->filter()->max();

    $kpis = [
        [
            'key' => 'revenue',
            'priority' => 'high',
            'icon' => 'revenue',
            'label' => __('Revenue'),
            'value' => $revenueTotal,
            'hint' => __('Posted invoices'),
            'format' => 'money',
            'trend' => $revenueTotal > 0 ? 'up' : null,
        ],
        [
            'key' => 'balance',
            'priority' => 'high',
            'icon' => 'balance',
            'label' => __('Outstanding balance'),
            'value' => $outstandingBalance,
            'hint' => $outstandingBalance > 0 ? __('Requires attention') : __('All clear'),
            'format' => 'money',
            'trend' => $outstandingBalance > 0 ? 'alert' : 'neutral',
        ],
        [
            'key' => 'conversations',
            'priority' => 'high',
            'icon' => 'chat',
            'label' => __('Open conversations'),
            'value' => $openConversations > 0 ? $openConversations : null,
            'hint' => __('Inbox & WhatsApp'),
            'format' => null,
            'trend' => $openConversations > 0 ? 'up' : null,
        ],
        ...($canComplaints ? [[
            'key' => 'complaints',
            'priority' => 'high',
            'icon' => 'activity',
            'label' => __('Open complaints'),
            'value' => $openComplaints > 0 ? $openComplaints : null,
            'hint' => Route::has('admin.commercial.complaints.index')
                ? __('View complaints workspace')
                : __('Complaints module'),
            'format' => null,
            'trend' => $openComplaints > 0 ? 'alert' : null,
        ]] : []),
        [
            'key' => 'quotes',
            'priority' => 'medium',
            'icon' => 'quote',
            'label' => __('Quotes'),
            'value' => $quotesTotal,
            'hint' => __('Total quotations'),
            'format' => null,
            'trend' => null,
        ],
        [
            'key' => 'orders',
            'priority' => 'medium',
            'icon' => 'order',
            'label' => __('Sales orders'),
            'value' => $ordersTotal,
            'hint' => __('Order history'),
            'format' => null,
            'trend' => null,
        ],
        [
            'key' => 'activity',
            'priority' => 'low',
            'icon' => 'activity',
            'label' => __('Last activity'),
            'value' => $lastActivityAt,
            'hint' => null,
            'format' => 'date',
            'trend' => null,
        ],
    ];

    $mapRecord = fn ($items, string $routeName, string $numberKey) => $items->map(fn ($row) => [
        'id' => $row->id,
        'number' => $row->{$numberKey},
        'status' => EnumLabel::of($row->status),
        'status_value' => $row->status->value,
        'date' => $row->created_at,
        'url' => Route::has($routeName) ? route($routeName, $row) : null,
    ]);

    $commercial = [
        'quotations' => $canQuotes
            ? $mapRecord(Quotation::query()->forTenant()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get(), 'admin.quotations.show', 'quotation_number')
            : collect(),
        'orders' => $canOrders
            ? $mapRecord(SalesOrder::query()->forTenant()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get(), 'admin.sales-orders.show', 'order_number')
            : collect(),
        'artwork' => $canArtwork
            ? $mapRecord(ArtworkRequest::query()->forTenant()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get(), 'admin.artwork.show', 'request_number')
            : collect(),
        'receipts' => $canPayments
            ? CustomerPayment::query()
                ->forTenant()
                ->where('customer_id', $customer->id)
                ->whereNotNull('receipt_number')
                ->orderByDesc('payment_date')
                ->limit(8)
                ->get()
                ->map(fn ($row) => [
                    'id' => $row->id,
                    'number' => $row->receipt_number,
                    'status' => EnumLabel::of($row->status),
                    'status_value' => $row->status->value,
                    'date' => $row->payment_date ?? $row->created_at,
                    'amount' => (float) $row->amount,
                    'payment_number' => $row->payment_number,
                    'payment_url' => route('admin.payments.show', $row),
                    'url' => auth()->user()->can('viewReceipt', $row) ? route('admin.payments.receipt', $row) : null,
                ])
            : collect(),
        'invoices' => $canInvoices
            ? $mapRecord(CustomerInvoice::query()->forTenant()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get(), 'admin.invoices.show', 'invoice_number')
            : collect(),
        'payments' => $canPayments
            ? CustomerPayment::query()->forTenant()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get()->map(fn ($row) => [
                'id' => $row->id,
                'number' => $row->payment_number,
                'status' => EnumLabel::of($row->status),
                'status_value' => $row->status->value,
                'date' => $row->payment_date ?? $row->created_at,
                'url' => route('admin.payments.show', $row),
            ])
            : collect(),
        'counts' => [
            'quotations' => $quotesTotal,
            'orders' => $ordersTotal,
            'artwork' => $artworkTotal,
            'print_specifications' => $printSpecificationsTotal,
            'library_artwork' => null,
            'invoices' => $invoicesTotal,
            'payments' => $paymentsTotal,
            'receipts' => $receiptsTotal,
        ],
        'intelligence' => $commercialIntelligence,
        'recent_jobs' => $commercialRecentJobs,
        'financial_summary' => $financialSummary,
    ];

    $latestOrderForRepeat = ($canOrders && $user->can('create', SalesOrder::class))
        ? SalesOrder::query()
            ->forTenant()
            ->where('customer_id', $customer->id)
            ->where('status', '!=', SalesOrderStatus::Cancelled)
            ->latest('id')
            ->first(['id', 'order_number'])
        : null;

    $openJobs = $canJobs
        ? ProductionJobCard::query()
            ->forTenant()
            ->where('customer_id', $customer->id)
            ->whereNotIn('status', [ProductionJobCardStatus::Completed, ProductionJobCardStatus::Cancelled])
            ->orderByDesc('id')
            ->limit(5)
            ->get()
        : collect();

    $openInvoices = $canInvoices
        ? CustomerInvoice::query()
            ->forTenant()
            ->where('customer_id', $customer->id)
            ->where('balance_due', '>', 0)
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
            ->orderByDesc('id')
            ->limit(5)
            ->get()
        : collect();

    $unifiedTimeline = collect();

    if ($canCommLogs && $communicationTimeline->isNotEmpty()) {
        foreach ($logService->timelinePayload($communicationTimeline) as $item) {
            $unifiedTimeline->push([
                'at' => \Illuminate\Support\Carbon::parse($item['created_at']),
                'title' => $item['subject'] ?: \Illuminate\Support\Str::limit($item['message'], 60),
                'body' => $item['channel_label'].' · '.$item['type_label'],
                'badge' => $item['channel_label'],
                'url' => $item['url'] ?? null,
                'kind' => 'communication',
            ]);
        }
    }

    if ($canEmailView) {
        $emailVisibility = app(EmailVisibilityService::class);
        foreach ($emailVisibility->forCustomer($customer, null, 20) as $message) {
            $unifiedTimeline->push($emailVisibility->presentTimelineEvent($message));
        }
    }

    foreach ($customer->activities->sortByDesc('activity_at')->take(15) as $activity) {
        $unifiedTimeline->push([
            'at' => $activity->activity_at,
            'title' => $activity->subject,
            'body' => ucfirst(str_replace('_', ' ', $activity->activity_type->value)).($activity->user ? ' · '.$activity->user->name : ''),
            'badge' => __('Activity'),
            'url' => auth()->user()->can('view', $activity) ? route('admin.commercial.activities.show', $activity) : null,
            'kind' => 'activity',
        ]);
    }

    if ($canQuotes) {
        foreach (Quotation::query()->where('customer_id', $customer->id)->orderByDesc('created_at')->limit(10)->get() as $row) {
            $unifiedTimeline->push([
                'at' => $row->created_at,
                'title' => __('Quote created'),
                'body' => $row->quotation_number,
                'badge' => __('Quote'),
                'url' => route('admin.quotations.show', $row),
                'kind' => 'quote',
            ]);
        }
    }

    if ($canOrders) {
        foreach (SalesOrder::query()->where('customer_id', $customer->id)->orderByDesc('created_at')->limit(10)->get() as $row) {
            $unifiedTimeline->push([
                'at' => $row->created_at,
                'title' => __('Sales order created'),
                'body' => $row->order_number,
                'badge' => __('Order'),
                'url' => route('admin.sales-orders.show', $row),
                'kind' => 'order',
            ]);
        }
    }

    if ($canArtwork) {
        foreach (ArtworkRequest::query()->where('customer_id', $customer->id)->orderByDesc('created_at')->limit(8)->get() as $row) {
            $unifiedTimeline->push([
                'at' => $row->created_at,
                'title' => __('Artwork request'),
                'body' => $row->request_number,
                'badge' => __('Artwork'),
                'url' => route('admin.artwork.show', $row),
                'kind' => 'artwork',
            ]);
        }
    }

    if ($canPayments) {
        foreach (CustomerPayment::query()->where('customer_id', $customer->id)->orderByDesc('payment_date')->limit(8)->get() as $row) {
            $unifiedTimeline->push([
                'at' => $row->payment_date ?? $row->created_at,
                'title' => __('Payment received'),
                'body' => $row->payment_number.' · '.number_format((float) $row->amount, 2),
                'badge' => __('Payment'),
                'url' => route('admin.payments.show', $row),
                'kind' => 'payment',
            ]);
        }
    }

    $unifiedTimeline = $unifiedTimeline->sortByDesc(fn ($e) => $e['at']?->timestamp ?? 0)->values();

    view()->share([
        'kpis' => $kpis,
        'commercial' => $commercial,
        'openJobs' => $openJobs,
        'openInvoices' => $openInvoices,
        'unifiedTimeline' => $unifiedTimeline,
        'canQuotes' => $canQuotes,
        'canOrders' => $canOrders,
        'canInvoices' => $canInvoices,
        'canPayments' => $canPayments,
        'canArtwork' => $canArtwork,
        'canJobs' => $canJobs,
        'canCommLogs' => $canCommLogs,
    ]);
@endphp
