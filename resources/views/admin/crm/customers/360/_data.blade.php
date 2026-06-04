@php
    use App\Enums\CustomerInvoiceStatus;
    use App\Enums\CustomerInvoiceType;
    use App\Enums\ProductionJobCardStatus;
    use App\Enums\QuotationStatus;
    use App\Enums\SalesOrderStatus;
    use App\Models\Artwork\ArtworkRequest;
    use App\Models\Communications\CommunicationLog;
    use App\Models\Production\ProductionJobCard;
    use App\Models\Sales\CustomerInvoice;
    use App\Models\Sales\CustomerPayment;
    use App\Models\Sales\Quotation;
    use App\Models\Sales\SalesOrder;
    use App\Support\Communications\CommunicationLogService;

    $logService = app(CommunicationLogService::class);
    $user = auth()->user();

    $canQuotes = $user->can('quotations.view');
    $canOrders = $user->can('sales_orders.view');
    $canInvoices = $user->can('invoices.view');
    $canPayments = $user->can('payments.view');
    $canArtwork = $user->can('artwork.view');
    $canJobs = $user->can('production.view');
    $canCommLogs = $user->can('communications.logs.view');

    $quotesTotal = $canQuotes ? Quotation::query()->where('customer_id', $customer->id)->count() : null;
    $ordersTotal = $canOrders ? SalesOrder::query()->where('customer_id', $customer->id)->count() : null;
    $invoicesTotal = $canInvoices ? CustomerInvoice::query()->where('customer_id', $customer->id)->count() : null;
    $paymentsTotal = $canPayments ? CustomerPayment::query()->where('customer_id', $customer->id)->count() : null;
    $artworkTotal = $canArtwork ? ArtworkRequest::query()->where('customer_id', $customer->id)->count() : null;

    $revenueTotal = $canInvoices
        ? (float) CustomerInvoice::query()
            ->where('customer_id', $customer->id)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->where('invoice_type', '!=', CustomerInvoiceType::CreditNote->value)
            ->sum('total_amount')
        : null;

    $outstandingBalance = $canInvoices
        ? (float) CustomerInvoice::query()
            ->where('customer_id', $customer->id)
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
            ->sum('balance_due')
        : null;

    $openConversations = ($inboxConversations->count() ?? 0) + ($whatsappConversations->count() ?? 0);

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
        'status' => is_object($row->status) ? $row->status->label() : (string) $row->status,
        'date' => $row->created_at,
        'url' => Route::has($routeName) ? route($routeName, $row) : null,
    ]);

    $commercial = [
        'quotations' => $canQuotes
            ? $mapRecord(Quotation::query()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get(), 'admin.quotations.show', 'quotation_number')
            : collect(),
        'orders' => $canOrders
            ? $mapRecord(SalesOrder::query()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get(), 'admin.sales-orders.show', 'order_number')
            : collect(),
        'artwork' => $canArtwork
            ? $mapRecord(ArtworkRequest::query()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get(), 'admin.artwork.show', 'request_number')
            : collect(),
        'invoices' => $canInvoices
            ? $mapRecord(CustomerInvoice::query()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get(), 'admin.invoices.show', 'invoice_number')
            : collect(),
        'payments' => $canPayments
            ? CustomerPayment::query()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get()->map(fn ($row) => [
                'id' => $row->id,
                'number' => $row->payment_number,
                'status' => $row->status->label(),
                'date' => $row->payment_date ?? $row->created_at,
                'url' => route('admin.payments.show', $row),
            ])
            : collect(),
        'counts' => [
            'quotations' => $quotesTotal,
            'orders' => $ordersTotal,
            'artwork' => $artworkTotal,
            'invoices' => $invoicesTotal,
            'payments' => $paymentsTotal,
        ],
    ];

    $openJobs = $canJobs
        ? ProductionJobCard::query()
            ->where('customer_id', $customer->id)
            ->whereNotIn('status', [ProductionJobCardStatus::Completed, ProductionJobCardStatus::Cancelled])
            ->orderByDesc('id')
            ->limit(5)
            ->get()
        : collect();

    $openInvoices = $canInvoices
        ? CustomerInvoice::query()
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
