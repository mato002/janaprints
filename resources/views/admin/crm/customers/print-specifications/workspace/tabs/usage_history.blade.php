<div class="space-y-6">
    <section>
        <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Sales orders') }}</h3>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Order') }}</th><th>{{ __('Date') }}</th><th>{{ __('Total') }}</th><th>{{ __('Status') }}</th></tr></thead>
                <tbody>
                    @forelse ($tabData['salesOrders'] as $order)
                        <tr>
                            <td><a class="text-erp-accent hover:underline" href="{{ route('admin.sales-orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td>{{ $order->order_date?->format('Y-m-d') }}</td>
                            <td class="tabular-nums">{{ number_format((float) $order->total_amount, 2) }}</td>
                            <td>{{ $order->status->label() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-slate-500">{{ __('No orders yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $tabData['salesOrders']->links() }}
    </section>

    <section>
        <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Job cards') }}</h3>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Job') }}</th><th>{{ __('Order') }}</th><th>{{ __('Status') }}</th><th>{{ __('Produced') }}</th></tr></thead>
                <tbody>
                    @forelse ($tabData['jobCards'] as $job)
                        <tr>
                            <td><a class="text-erp-accent hover:underline" href="{{ route('admin.production.job-cards.show', $job) }}">{{ $job->job_card_number }}</a></td>
                            <td>{{ $job->salesOrder?->order_number ?? '—' }}</td>
                            <td>{{ $job->status->label() }}</td>
                            <td>{{ $job->actual_end_date?->format('Y-m-d') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-slate-500">{{ __('No job cards yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $tabData['jobCards']->links() }}
    </section>

    <section>
        <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Invoices') }}</h3>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Invoice') }}</th><th>{{ __('Date') }}</th><th>{{ __('Total') }}</th><th>{{ __('Status') }}</th></tr></thead>
                <tbody>
                    @forelse ($tabData['invoices'] as $invoice)
                        <tr>
                            <td><a class="text-erp-accent hover:underline" href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                            <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                            <td class="tabular-nums">{{ number_format((float) $invoice->total_amount, 2) }}</td>
                            <td>{{ $invoice->status->label() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-slate-500">{{ __('No invoices yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $tabData['invoices']->links() }}
    </section>

    <section>
        <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Repeat orders') }}</h3>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Order') }}</th><th>{{ __('Source') }}</th><th>{{ __('Date') }}</th><th>{{ __('Total') }}</th></tr></thead>
                <tbody>
                    @forelse ($tabData['repeatOrders'] as $order)
                        <tr>
                            <td><a class="text-erp-accent hover:underline" href="{{ route('admin.sales-orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td>{{ $order->repeatSource?->order_number ?? '—' }}</td>
                            <td>{{ $order->order_date?->format('Y-m-d') }}</td>
                            <td class="tabular-nums">{{ number_format((float) $order->total_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-slate-500">{{ __('No repeat orders yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $tabData['repeatOrders']->links() }}
    </section>

    <section>
        <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Production sessions') }}</h3>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Job') }}</th><th>{{ __('Started') }}</th><th>{{ __('Good qty') }}</th><th>{{ __('Operator') }}</th></tr></thead>
                <tbody>
                    @forelse ($tabData['sessions'] as $session)
                        <tr>
                            <td>{{ $session->jobCard?->job_card_number ?? '—' }}</td>
                            <td>{{ $session->started_at?->format('Y-m-d H:i') }}</td>
                            <td class="tabular-nums">{{ $session->good_quantity }}</td>
                            <td>{{ $session->operator?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-slate-500">{{ __('No production sessions yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $tabData['sessions']->links() }}
    </section>
</div>
