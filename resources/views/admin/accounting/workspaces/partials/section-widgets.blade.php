@php
    $widgets = $insights['widgets'] ?? [];
@endphp

@if ($section === 'general-ledger')
    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Period Status') }}</h3>
            @php $period = $widgets['period_status'] ?? []; @endphp
            <dl class="grid grid-cols-2 gap-2 text-sm">
                <dt class="text-slate-500">{{ __('Period') }}</dt>
                <dd class="font-mono">{{ $period['code'] ?? '—' }} — {{ $period['name'] ?? '' }}</dd>
                <dt class="text-slate-500">{{ __('Status') }}</dt>
                <dd>{{ $period['status_label'] ?? __('Open') }}</dd>
                <dt class="text-slate-500">{{ __('Posting') }}</dt>
                <dd>{{ ! empty($period['can_post']) ? __('Allowed') : __('Restricted') }}</dd>
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Recent Journals') }}</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase text-slate-400 border-b border-erp-border">
                        <th class="py-1">{{ __('Journal') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($widgets['recent_journals'] ?? [] as $journal)
                        <tr class="border-t border-erp-border">
                            <td class="py-2">
                                @can('viewAny', App\Models\Accounting\Journal::class)
                                    <a href="{{ route('admin.accounting.journals.show', $journal['id']) }}" class="text-erp-accent font-mono text-xs">{{ $journal['journal_number'] }}</a>
                                @else
                                    <span class="font-mono text-xs">{{ $journal['journal_number'] }}</span>
                                @endcan
                            </td>
                            <td>{{ $journal['journal_date'] }}</td>
                            <td>{{ $journal['status_label'] }}</td>
                            <td class="text-right">{{ number_format($journal['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-slate-500">{{ __('No journals yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.card>
    </div>
@endif

@if ($section === 'receivables')
    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Recent Invoices') }}</h3>
            <ul class="divide-y divide-erp-border text-sm">
                @forelse ($widgets['recent_invoices'] ?? [] as $row)
                    <li class="py-2 flex justify-between gap-2">
                        <div>
                            <a href="{{ $row['route'] }}" class="text-erp-accent font-medium">{{ $row['label'] }}</a>
                            <p class="text-[11px] text-slate-500">{{ $row['customer'] }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <div>{{ number_format($row['amount'], 2) }}</div>
                            <div class="text-[11px] text-slate-400">{{ $row['date'] }}</div>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-slate-500">{{ __('No invoices yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Recent Payments') }}</h3>
            <ul class="divide-y divide-erp-border text-sm">
                @forelse ($widgets['recent_payments'] ?? [] as $row)
                    <li class="py-2 flex justify-between gap-2">
                        <div>
                            <a href="{{ $row['route'] }}" class="text-erp-accent font-medium">{{ $row['label'] }}</a>
                            <p class="text-[11px] text-slate-500">{{ $row['customer'] }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <div>{{ number_format($row['amount'], 2) }}</div>
                            <div class="text-[11px] text-slate-400">{{ $row['date'] }}</div>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-slate-500">{{ __('No payments yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
@endif

@if ($section === 'payables')
    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Upcoming Supplier Payments') }}</h3>
            <ul class="divide-y divide-erp-border text-sm">
                @forelse ($widgets['upcoming_payments'] ?? [] as $row)
                    <li class="py-2 flex justify-between gap-2">
                        <a href="{{ $row['route'] }}" class="text-erp-accent font-medium">{{ $row['label'] }}</a>
                        <div class="text-right shrink-0">
                            <div>{{ number_format($row['amount'], 2) }}</div>
                            <div class="text-[11px] text-slate-400">{{ $row['date'] }}</div>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-slate-500">{{ __('No draft payments scheduled.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Recent Supplier Transactions') }}</h3>
            <ul class="divide-y divide-erp-border text-sm">
                @forelse ($widgets['recent_transactions'] ?? [] as $row)
                    <li class="py-2 flex justify-between gap-2">
                        <div>
                            <span class="text-[11px] uppercase text-slate-400">{{ $row['type'] }}</span>
                            <a href="{{ $row['route'] }}" class="block text-erp-accent font-medium">{{ $row['label'] }}</a>
                        </div>
                        <div class="text-right shrink-0">
                            <div>{{ number_format($row['amount'], 2) }}</div>
                            <div class="text-[11px] text-slate-400">{{ $row['date'] }}</div>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-slate-500">{{ __('No supplier activity yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
@endif

@if ($section === 'tax')
    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Recent Tax Activity') }}</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase text-slate-400 border-b border-erp-border">
                        <th class="py-1">{{ __('Document') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Direction') }}</th>
                        <th class="text-right">{{ __('Tax') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($widgets['recent_activity'] ?? [] as $row)
                        <tr class="border-t border-erp-border">
                            <td class="py-2 font-mono text-xs">{{ $row['label'] }}</td>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['direction'] }}</td>
                            <td class="text-right">{{ number_format($row['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-slate-500">{{ __('No tax transactions yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Upcoming Filing Deadlines') }}</h3>
            <ul class="divide-y divide-erp-border text-sm">
                @forelse ($widgets['upcoming_filings'] ?? [] as $row)
                    <li class="py-2 flex justify-between gap-2">
                        <div>
                            <a href="{{ $row['route'] }}" class="text-erp-accent font-medium">{{ $row['label'] }}</a>
                            <p class="text-[11px] text-slate-500">{{ $row['status'] }}</p>
                        </div>
                        <div class="text-right shrink-0 font-mono">{{ number_format($row['amount'], 2) }}</div>
                    </li>
                @empty
                    <li class="py-4 text-slate-500">{{ __('No draft returns pending.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
@endif

@if ($section === 'setup')
    <x-admin.card class="mb-4">
        <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('System Configuration Overview') }}</h3>
        @php $chart = $widgets['chart_status'] ?? []; @endphp
        <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-3">
            <div>
                <dt class="text-slate-500">{{ __('Chart of Accounts') }}</dt>
                <dd>{{ ($chart['active'] ?? 0).' / '.($chart['total'] ?? 0).' '.__('active') }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Posting Templates') }}</dt>
                <dd>{{ $chart['templates'] ?? 0 }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Active Posting Rules') }}</dt>
                <dd>{{ $widgets['posting_rule_count'] ?? 0 }}</dd>
            </div>
        </dl>
        @if (! empty($widgets['period']))
            <p class="mt-3 text-[11px] text-slate-500">
                {{ __('Current period') }}:
                <a href="{{ $widgets['period']['route'] }}" class="text-erp-accent">{{ $widgets['period']['code'] }} — {{ $widgets['period']['name'] }}</a>
                ({{ $widgets['period']['status'] }})
            </p>
        @endif
    </x-admin.card>
@endif
