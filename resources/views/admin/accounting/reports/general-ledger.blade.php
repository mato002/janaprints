<x-admin-layout :title="__('General Ledger Report')">
    <x-admin.page-header :title="__('General Ledger Report')" :description="__('Posted journal lines with running balance')" />

    <x-admin.card class="mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="run" value="1">
            <div>
                <label class="erp-label">{{ __('Account') }}</label>
                <select name="account_id" class="erp-input min-w-[200px]">
                    <option value="">{{ __('Summary — all accounts') }}</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected(($filters['account_id'] ?? null) == $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Period') }}</label>
                <select name="period_id" class="erp-input">
                    <option value="">{{ __('Custom') }}</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}" @selected(($filters['period_id'] ?? null) == $period->id)>{{ $period->code }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="erp-label">{{ __('From') }}</label><input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="erp-input"></div>
            <div><label class="erp-label">{{ __('To') }}</label><input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="erp-input"></div>
            <button class="erp-btn-primary">{{ __('Run report') }}</button>
        </form>
    </x-admin.card>

    @if ($report)
        <div class="mb-4">
            <h2 class="font-semibold font-mono">{{ $report['account']->code }} — {{ $report['account']->name }}</h2>
            <p class="text-sm text-slate-500">{{ __('Opening') }}: {{ number_format($report['opening_balance'], 2) }} · {{ __('Closing') }}: {{ number_format($report['closing_balance'], 2) }}</p>
        </div>
        <x-admin.card>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase text-slate-400 border-b border-erp-border">
                        <th class="py-2">{{ __('Date') }}</th>
                        <th>{{ __('Journal') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th class="text-right">{{ __('Debit') }}</th>
                        <th class="text-right">{{ __('Credit') }}</th>
                        <th class="text-right">{{ __('Balance') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-erp-border bg-slate-50">
                        <td colspan="5" class="py-2 font-medium">{{ __('Opening balance') }}</td>
                        <td class="py-2 text-right font-mono">{{ number_format($report['opening_balance'], 2) }}</td>
                    </tr>
                    @foreach ($report['lines'] as $line)
                        <tr class="border-b border-erp-border/50">
                            <td class="py-2">{{ $line['journal_date'] }}</td>
                            <td class="font-mono text-xs">
                                <a href="{{ route('admin.accounting.journals.show', $line['journal_id']) }}" class="text-erp-accent">{{ $line['journal_number'] }}</a>
                            </td>
                            <td class="text-slate-600">{{ $line['description'] }}</td>
                            <td class="text-right">{{ $line['debit'] > 0 ? number_format($line['debit'], 2) : '—' }}</td>
                            <td class="text-right">{{ $line['credit'] > 0 ? number_format($line['credit'], 2) : '—' }}</td>
                            <td class="text-right font-mono">{{ number_format($line['running_balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.card>
    @elseif ($summary)
        <x-admin.card>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase text-slate-400">
                        <th>{{ __('Account') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th class="text-right">{{ __('Debit') }}</th>
                        <th class="text-right">{{ __('Credit') }}</th>
                        <th class="text-right">{{ __('Net') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summary['rows'] as $row)
                        <tr class="border-t border-erp-border">
                            <td class="py-2">
                                <a href="{{ route('admin.accounting.reports.general-ledger', array_merge($filters, ['account_id' => $row['account_id'], 'run' => 1])) }}" class="text-erp-accent font-mono text-xs">{{ $row['account_code'] }}</a>
                                — {{ $row['account_name'] }}
                            </td>
                            <td>{{ $row['account_type'] }}</td>
                            <td class="text-right font-mono">{{ number_format($row['period_debit'], 2) }}</td>
                            <td class="text-right font-mono">{{ number_format($row['period_credit'], 2) }}</td>
                            <td class="text-right font-mono">{{ number_format($row['signed_balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.card>
    @endif
</x-admin-layout>
