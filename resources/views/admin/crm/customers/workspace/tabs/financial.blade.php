@php
    $profile = $tabData['profile'] ?? [];
    $aging = $tabData['aging']['buckets'] ?? [];
    $collection = $tabData['collection'] ?? [];
    $section = $tabData['section'] ?? 'overview';
    $invoices = $tabData['invoices'] ?? null;
    $customer = $customer ?? null;
@endphp

@if (! empty($tabData['restricted']))
    <x-admin.empty-state icon="lock-closed" :title="__('Access restricted')" />
@else
    <nav class="mb-4 flex gap-2 border-b border-slate-200">
        <a href="{{ route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'financial', 'financial_section' => 'overview']) }}"
           class="px-3 py-2 text-sm font-medium {{ $section === 'overview' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-slate-600' }}">
            {{ __('Intelligence') }}
        </a>
        @can('statements.view')
            <a href="{{ route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'financial', 'financial_section' => 'statement']) }}"
               class="px-3 py-2 text-sm font-medium {{ $section === 'statement' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-slate-600' }}">
                {{ __('Statement') }}
            </a>
        @endcan
    </nav>

    @if ($section === 'statement' && ! empty($tabData['statement']))
        @include('admin.crm.customers.workspace.partials.statement-ledger', [
            'statement' => $tabData['statement'],
            'customer' => $customer,
            'from' => $tabData['statement_from'],
            'to' => $tabData['statement_to'],
        ])
    @else
        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([
                ['label' => __('Total Invoiced'), 'value' => number_format($profile['total_invoiced'] ?? 0, 2)],
                ['label' => __('Total Paid'), 'value' => number_format($profile['total_paid'] ?? 0, 2)],
                ['label' => __('Outstanding'), 'value' => number_format($profile['outstanding'] ?? 0, 2)],
                ['label' => __('Customer Credit'), 'value' => number_format($profile['credit_balance'] ?? 0, 2)],
            ] as $kpi)
                <x-admin.kpi-widget :label="$kpi['label']" :value="$kpi['value']" icon="currency-dollar" />
            @endforeach
        </div>

        <div class="mb-6 grid gap-4 lg:grid-cols-2">
            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold">{{ __('Collection intelligence') }}</h3>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-slate-500">{{ __('Overdue amount') }}</dt><dd class="font-mono font-medium">{{ number_format($profile['overdue_amount'] ?? 0, 2) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Collection risk') }}</dt>
                        <dd>
                            @php $risk = strtoupper($profile['collection_risk'] ?? 'LOW'); @endphp
                            <span class="erp-badge {{ $risk === 'HIGH' ? 'erp-badge-warning' : ($risk === 'MEDIUM' ? 'erp-badge-muted' : 'erp-badge-success') }}">{{ $risk }}</span>
                        </dd>
                    </div>
                    <div><dt class="text-slate-500">{{ __('Avg. payment days') }}</dt><dd>{{ $profile['average_payment_days'] !== null ? $profile['average_payment_days'].' '.__('days') : '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Oldest outstanding') }}</dt>
                        <dd>{{ $profile['oldest_outstanding_invoice']['invoice_number'] ?? '—' }}</dd>
                    </div>
                    <div><dt class="text-slate-500">{{ __('Invoice count') }}</dt><dd>{{ $collection['invoice_count'] ?? 0 }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Payment count') }}</dt><dd>{{ $collection['payment_count'] ?? 0 }}</dd></div>
                </dl>
            </x-admin.card>

            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold">{{ __('AR aging (by due date)') }}</h3>
                <dl class="space-y-2 text-sm">
                    @foreach ([
                        'current' => __('Current'),
                        '1_30' => __('1–30 days'),
                        '31_60' => __('31–60 days'),
                        '61_90' => __('61–90 days'),
                        '90_plus' => __('90+ days'),
                    ] as $key => $label)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="font-mono">{{ number_format($aging[$key] ?? 0, 2) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-admin.card>
        </div>

        <x-admin.card>
            <div class="mb-4 flex justify-between">
                <h3 class="text-sm font-semibold">{{ __('Open invoices') }}</h3>
                @can('create', App\Models\Accounting\Invoice::class)
                    <a href="{{ route('admin.accounting.invoices.create', ['customer_id' => $customer->id]) }}" class="erp-btn-primary text-sm">{{ __('New invoice') }}</a>
                @endcan
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Invoice') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Due') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('Total') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td class="px-3 py-2"><a href="{{ route('admin.accounting.invoices.show', $invoice) }}" class="font-mono text-indigo-600">{{ $invoice->invoice_number }}</a></td>
                                <td class="px-3 py-2">{{ $invoice->due_date->format('M j, Y') }}</td>
                                <td class="px-3 py-2 text-right font-mono">{{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="px-3 py-2"><x-admin.enum-status-badge :status="$invoice->status->value" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">{{ __('No invoices') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($invoices && method_exists($invoices, 'links'))
                <div class="mt-4"><x-admin.table-pagination :paginator="$invoices" /></div>
            @endif
        </x-admin.card>
    @endif
@endif
