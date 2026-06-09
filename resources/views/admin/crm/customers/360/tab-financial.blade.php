@php
    $financial = $financial ?? ['restricted' => true];
    $profile = $financial['profile'] ?? [];
    $section = $financial['section'] ?? 'overview';
    $aging = $profile['aging']['buckets'] ?? [];
    $collection = $profile['collection'] ?? [];
@endphp

@if (! empty($financial['restricted']))
    <x-admin.empty-state icon="lock-closed" :title="__('Access restricted')" :description="__('You do not have permission to view customer financial data.')" />
@else
    <div class="crm-360__tab-toolbar">
        @can('payments.create')
            <x-admin.crm-btn
                variant="primary"
                size="sm"
                :href="route('admin.payments.create', ['customer_id' => $customer->id])"
                data-turbo-frame="erp-main"
            >{{ __('Record payment') }}</x-admin.crm-btn>
        @endcan
        @can('receivables.statement.view')
            <x-admin.crm-btn
                variant="outline"
                size="sm"
                :href="route('admin.receivables.statement', ['customer_id' => $customer->id])"
                data-turbo-frame="erp-main"
            >{{ __('Full statement') }}</x-admin.crm-btn>
        @endcan
        @can('receivables.aging.view')
            <x-admin.crm-btn
                variant="ghost"
                size="sm"
                :href="route('admin.receivables.aging', ['customer_id' => $customer->id])"
                data-turbo-frame="erp-main"
            >{{ __('AR aging') }}</x-admin.crm-btn>
        @endcan
    </div>

    <nav class="mb-4 flex flex-wrap gap-1 border-b border-erp-border">
        @foreach ([
            'overview' => __('Overview'),
            'invoices' => __('Invoices'),
            'payments' => __('Payments'),
            'credit-notes' => __('Credit notes'),
            'deposits' => __('Deposits'),
            'aging' => __('Aging'),
            'statement' => __('Statement'),
            'receipts' => __('Receipt history'),
        ] as $key => $label)
            @if ($key === 'statement' && empty($financial['can_statement']))
                @continue
            @endif
            @if ($key === 'receipts' && empty($financial['can_receipts']))
                @continue
            @endif
            @if (in_array($key, ['invoices', 'credit-notes'], true) && empty($financial['can_invoices']))
                @continue
            @endif
            @if (in_array($key, ['payments', 'deposits', 'receipts'], true) && empty($financial['can_payments']))
                @continue
            @endif
            <a
                href="{{ route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'financial', 'financial_section' => $key]) }}"
                class="px-3 py-2 text-sm font-medium {{ $section === $key ? 'border-b-2 border-erp-accent text-erp-accent' : 'text-slate-600 hover:text-slate-900' }}"
                data-turbo-frame="erp-main"
            >{{ $label }}</a>
        @endforeach
    </nav>

    @if ($section === 'statement' && ! empty($financial['statement']))
        @include('admin.crm.customers.360.partials.financial-statement', [
            'statement' => $financial['statement'],
            'from' => $financial['statement_from'],
            'to' => $financial['statement_to'],
        ])
    @elseif ($section === 'invoices' && ! empty($financial['invoices']))
        @include('admin.crm.customers.360.partials.financial-invoices', ['invoices' => $financial['invoices']])
    @elseif ($section === 'payments' && ! empty($financial['payments']))
        @include('admin.crm.customers.360.partials.financial-payments', ['payments' => $financial['payments']])
    @elseif ($section === 'credit-notes' && ! empty($financial['credit_notes']))
        @include('admin.crm.customers.360.partials.financial-credit-notes', ['creditNotes' => $financial['credit_notes']])
    @elseif ($section === 'deposits')
        @include('admin.crm.customers.360.partials.financial-deposits', [
            'deposits' => $financial['deposits'] ?? [],
            'wallet' => $profile['credit_wallet'] ?? [],
        ])
    @elseif ($section === 'aging')
        @include('admin.crm.customers.360.partials.financial-aging', [
            'aging' => $aging,
            'profile' => $profile,
        ])
    @elseif ($section === 'receipts' && ! empty($financial['receipts']))
        @include('admin.crm.customers.360.partials.financial-receipts', ['receipts' => $financial['receipts']])
    @else
        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-admin.kpi-widget :label="__('Outstanding balance')" :value="number_format($profile['outstanding'] ?? 0, 2)" icon="scale" />
            <x-admin.kpi-widget :label="__('Total invoiced')" :value="number_format($profile['total_invoiced'] ?? 0, 2)" icon="document-text" />
            <x-admin.kpi-widget :label="__('Total paid')" :value="number_format($profile['total_paid'] ?? 0, 2)" icon="currency-dollar" />
            <x-admin.kpi-widget :label="__('Deposit credit')" :value="number_format($profile['credit_balance'] ?? 0, 2)" icon="cash" />
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
                    <div><dt class="text-slate-500">{{ __('Avg. payment days') }}</dt><dd>{{ isset($profile['average_payment_days']) ? $profile['average_payment_days'].' '.__('days') : '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Oldest outstanding') }}</dt>
                        <dd class="font-mono">{{ $profile['oldest_outstanding_invoice']['invoice_number'] ?? '—' }}</dd>
                    </div>
                    <div><dt class="text-slate-500">{{ __('Invoices') }}</dt><dd>{{ $collection['invoice_count'] ?? 0 }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Payments') }}</dt><dd>{{ $collection['payment_count'] ?? 0 }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Credit notes') }}</dt><dd>{{ $collection['credit_note_count'] ?? 0 }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Receipts issued') }}</dt><dd>{{ $collection['receipt_count'] ?? 0 }}</dd></div>
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
                    <div class="flex justify-between border-t border-erp-border pt-2 font-semibold">
                        <dt>{{ __('Total open AR') }}</dt>
                        <dd class="font-mono">{{ number_format($profile['aging']['total'] ?? 0, 2) }}</dd>
                    </div>
                </dl>
            </x-admin.card>
        </div>

        @if (! empty($financial['invoices']))
            <x-admin.card>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold">{{ __('Recent invoices') }}</h3>
                    <a href="{{ route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'financial', 'financial_section' => 'invoices']) }}" class="text-sm text-erp-accent">{{ __('View all') }}</a>
                </div>
                @include('admin.crm.customers.360.partials.financial-invoices', ['invoices' => $financial['invoices'], 'compact' => true])
            </x-admin.card>
        @endif

        @if (! empty($financial['payments']))
            <x-admin.card class="mt-4">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold">{{ __('Recent payments') }}</h3>
                    <a href="{{ route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'financial', 'financial_section' => 'payments']) }}" class="text-sm text-erp-accent">{{ __('View all') }}</a>
                </div>
                @include('admin.crm.customers.360.partials.financial-payments', ['payments' => $financial['payments'], 'compact' => true])
            </x-admin.card>
        @endif
    @endif
@endif
