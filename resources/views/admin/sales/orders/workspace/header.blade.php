@php
    use App\Enums\SalesOrderStatus;
    use App\Models\Sales\CustomerInvoice;

    $canConfirm = ($workflow['can_confirm'] ?? false) && auth()->user()?->can('confirm', $salesOrder);
    $canRelease = ($workflow['can_release'] ?? false) && auth()->user()?->can('production', $salesOrder);
    $canClose = ($workflow['can_close'] ?? false) && auth()->user()?->can('close', $salesOrder);
    $canTransition = auth()->user()?->can('transition', $salesOrder);
    $canInvoice = auth()->user()?->can('create', CustomerInvoice::class)
        && $salesOrder->remainingInvoiceTotal() > 0
        && ! in_array($salesOrder->status, [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled], true);
    $canUpdate = auth()->user()?->can('update', $salesOrder);

    $onHold = $salesOrder->status === SalesOrderStatus::OnHold;
    $canHold = $canTransition && $salesOrder->status->canTransitionTo(SalesOrderStatus::OnHold);
    $canCancel = $canTransition && $salesOrder->status->canTransitionTo(SalesOrderStatus::Cancelled);

    $primary = null;
    if ($canConfirm) {
        $primary = ['type' => 'form', 'action' => route('admin.sales-orders.confirm', $salesOrder), 'label' => __('Confirm order'), 'method' => 'POST'];
    } elseif ($canRelease) {
        $primary = ['type' => 'form', 'action' => route('admin.sales-orders.release-to-production', $salesOrder), 'label' => __('Send to production'), 'method' => 'POST'];
    } elseif ($onHold && $canTransition) {
        $primary = ['type' => 'form', 'action' => route('admin.sales-orders.resume', $salesOrder), 'label' => __('Resume'), 'method' => 'POST'];
    } elseif ($canInvoice) {
        $primary = ['type' => 'link', 'url' => route('admin.invoices.from-sales-order', $salesOrder), 'label' => __('Generate invoice'), 'modal' => true];
    } elseif ($canClose) {
        $primary = ['type' => 'form', 'action' => route('admin.sales-orders.close', $salesOrder), 'label' => __('Close order'), 'method' => 'POST'];
    }

    $currentStage = collect($workflow['pipeline'] ?? [])->firstWhere('state', 'current')
        ?? collect($workflow['pipeline'] ?? [])->firstWhere('state', 'paused');
    $currentStageLabel = $currentStage['label'] ?? $statusLabel;
    $stageTone = match (true) {
        $salesOrder->status === SalesOrderStatus::Cancelled => 'danger',
        $salesOrder->status === SalesOrderStatus::OnHold => 'warning',
        $salesOrder->status === SalesOrderStatus::Closed => 'success',
        $salesOrder->status === SalesOrderStatus::Delivered => 'success',
        default => 'info',
    };
@endphp

<header class="so-360__header">
    <div class="so-360__header-main">
        <div class="so-360__identity">
            <p class="so-360__eyebrow">
                <span>{{ __('Sales order') }}</span>
                @if ($salesOrder->branch?->name)
                    <span class="so-360__dot" aria-hidden="true">·</span>
                    <span>{{ $salesOrder->branch->name }}</span>
                @endif
            </p>
            <h1 class="so-360__title font-mono">{{ $salesOrder->order_number }}</h1>
            <p class="so-360__subtitle">
                @if ($salesOrder->customer)
                    <a href="{{ route('admin.crm.customers.show', $salesOrder->customer) }}" class="so-360__link" data-turbo-frame="erp-main">
                        {{ $salesOrder->customer->company_name }}
                    </a>
                @else
                    {{ __('No customer') }}
                @endif
            </p>

            @if ($salesOrder->jobCard)
                <p class="so-360__linked-doc mt-1.5 text-sm text-slate-600">
                    <span class="text-slate-500">{{ __('Linked job card') }}:</span>
                    <a
                        href="{{ route('admin.production.job-cards.show', $salesOrder->jobCard) }}"
                        class="so-360__link font-mono underline decoration-erp-accent/40 underline-offset-2"
                        data-turbo-frame="erp-main"
                        data-turbo-action="advance"
                    >{{ $salesOrder->jobCard->job_card_number }}</a>
                </p>
            @endif

            <div class="so-360__badge-row">
                <span @class(['so-360__badge', 'so-360__badge--'.$stageTone])>{{ $statusLabel }}</span>
                @if ($financialLabel)
                    <span @class([
                        'so-360__badge',
                        'so-360__badge--success' => $financialVariant === 'success',
                        'so-360__badge--warning' => $financialVariant === 'warning',
                        'so-360__badge--neutral' => ! in_array($financialVariant, ['success', 'warning'], true),
                    ])>{{ $financialLabel }}</span>
                @endif
                <span class="so-360__total-chip">
                    <span class="so-360__total-label">{{ __('Total') }}</span>
                    <span class="so-360__total-value font-mono">{{ number_format($salesOrder->total_amount, 2) }}</span>
                </span>
            </div>
        </div>

        <div class="so-360__stage-panel">
            <p class="so-360__stage-label">{{ __('Current stage') }}</p>
            <div @class(['so-360__stage', 'so-360__stage--'.$stageTone])>
                <span class="so-360__stage-dot" aria-hidden="true"></span>
                <span>{{ $currentStageLabel }}</span>
            </div>
            @if ($workflow['hint'] ?? null)
                <p class="so-360__hint">{{ $workflow['hint'] }}</p>
            @endif
        </div>

        <div class="so-360__actions so-360__actions--desktop">
            @include('admin.sales.orders.workspace.partials.primary-action', ['primary' => $primary])

            @if ($canInvoice && (! $primary || ($primary['label'] ?? null) !== __('Generate invoice')))
                <a href="{{ route('admin.invoices.from-sales-order', $salesOrder) }}" class="erp-btn-secondary" data-erp-modal-open>
                    {{ __('Generate invoice') }}
                </a>
            @endif

            @if ($salesOrder->jobCard)
                <a href="{{ route('admin.production.job-cards.show', $salesOrder->jobCard) }}" class="erp-btn-secondary" data-turbo-frame="erp-main">
                    {{ __('Open job card') }}
                </a>
            @elseif ($canRelease)
                {{-- Primary already covers release; keep secondary space clean --}}
            @endif

            <button type="button" class="erp-btn-secondary" onclick="window.print()">{{ __('Print') }}</button>

            @if ($canUpdate)
                <a href="{{ route('admin.sales-orders.edit', $salesOrder) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
            @endif

            <details class="so-360__more">
                <summary class="erp-btn-secondary so-360__more-summary">{{ __('More') }}</summary>
                <div class="so-360__more-menu">
                    @if ($canHold)
                        <form method="POST" action="{{ route('admin.sales-orders.hold', $salesOrder) }}">
                            @csrf
                            <button type="submit" class="so-360__more-item">{{ __('On hold') }}</button>
                        </form>
                    @endif
                    @if ($onHold && $canTransition && (! $primary || ($primary['label'] ?? null) !== __('Resume')))
                        <form method="POST" action="{{ route('admin.sales-orders.resume', $salesOrder) }}">
                            @csrf
                            <button type="submit" class="so-360__more-item">{{ __('Resume') }}</button>
                        </form>
                    @endif
                    @if ($canClose && (! $primary || ($primary['label'] ?? null) !== __('Close order')))
                        <form method="POST" action="{{ route('admin.sales-orders.close', $salesOrder) }}">
                            @csrf
                            <button type="submit" class="so-360__more-item">{{ __('Close order') }}</button>
                        </form>
                    @endif
                    @if ($canCancel)
                        <form method="POST" action="{{ route('admin.sales-orders.cancel', $salesOrder) }}">
                            @csrf
                            <button type="submit" class="so-360__more-item so-360__more-item--danger">{{ __('Cancel') }}</button>
                        </form>
                    @endif
                    @if ($salesOrder->jobCard)
                        <a href="{{ route('admin.production.job-cards.show', $salesOrder->jobCard) }}?tab=dispatch" class="so-360__more-item" data-turbo-frame="erp-main">
                            {{ __('Delivery note') }}
                        </a>
                    @endif
                </div>
            </details>
        </div>
    </div>

    <x-admin.workflow-error />
</header>
