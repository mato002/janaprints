<x-admin-layout :title="__('Bank Statement')" :breadcrumbs="[['label' => __('Bank Reconciliation'), 'url' => route('admin.accounting.bank.reconciliation.index')], ['label' => $statement->bankAccount?->name]]">
    <x-admin.page-header
        :title="__('Statement :date', ['date' => $statement->statement_date->format('Y-m-d')])"
        :description="$statement->bankAccount?->name.' · '.$statement->status->label()"
    >
        <x-slot name="actions">
            @can('accounting.bank.manage')
                @if ($statement->status !== App\Enums\BankStatementStatus::Reconciled)
                    <form method="POST" action="{{ route('admin.accounting.bank.reconciliation.reconcile', $statement) }}">
                        @csrf
                        <button type="submit" class="erp-btn-primary">{{ __('Mark reconciled') }}</button>
                    </form>
                @endif
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="mb-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
        <x-admin.kpi-widget :label="__('Opening')" :value="number_format((float) $statement->opening_balance, 2)" />
        <x-admin.kpi-widget :label="__('Closing')" :value="number_format((float) $statement->closing_balance, 2)" />
        <x-admin.kpi-widget :label="__('GL as of date')" :value="number_format((float) $glBalance, 2)" />
        <x-admin.kpi-widget :label="__('Difference')" :value="number_format((float) $statement->closing_balance - (float) $glBalance, 2)" />
    </div>

    @if (!empty($suggestions))
        <x-admin.card class="mb-4">
            <h3 class="font-medium mb-2">{{ __('Suggested matches') }}</h3>
            <table class="w-full text-sm">
                @foreach ($suggestions as $suggestion)
                    <tr class="border-t border-erp-border">
                        <td class="py-2">{{ __('Line #:id', ['id' => $suggestion['statement_line_id']]) }}</td>
                        <td class="py-2">{{ __('Journal line #:id · :date', ['id' => $suggestion['journal_line_id'], 'date' => $suggestion['journal_date']]) }}</td>
                        <td class="py-2 text-right">{{ number_format($suggestion['amount'], 2) }}</td>
                        <td class="py-2 text-right">
                            @can('accounting.bank.manage')
                                <form method="POST" action="{{ route('admin.accounting.bank.reconciliation.match', $statement) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="statement_line_id" value="{{ $suggestion['statement_line_id'] }}">
                                    <input type="hidden" name="journal_line_id" value="{{ $suggestion['journal_line_id'] }}">
                                    <button type="submit" class="erp-btn-secondary text-xs">{{ __('Match') }}</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </table>
        </x-admin.card>
    @endif

    <x-admin.card>
        <h3 class="font-medium mb-2">{{ __('Statement lines') }}</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-500 border-b border-erp-border">
                    <th class="py-2">{{ __('Date') }}</th>
                    <th class="py-2">{{ __('Description') }}</th>
                    <th class="py-2 text-right">{{ __('Amount') }}</th>
                    <th class="py-2">{{ __('Match') }}</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($statement->lines as $line)
                    <tr class="border-t border-erp-border">
                        <td class="py-2">{{ $line->line_date->format('Y-m-d') }}</td>
                        <td class="py-2">
                            {{ $line->description }}
                            @if ($line->reference)
                                <span class="text-xs text-slate-500">· {{ $line->reference }}</span>
                            @endif
                        </td>
                        <td class="py-2 text-right font-mono">{{ number_format((float) $line->amount, 2) }}</td>
                        <td class="py-2 text-xs">
                            @if ($line->is_matched)
                                {{ __('JL #:id', ['id' => $line->matched_journal_line_id]) }}
                            @else
                                <span class="text-slate-500">{{ __('Unmatched') }}</span>
                            @endif
                        </td>
                        <td class="py-2 text-right">
                            @can('accounting.bank.manage')
                                @if ($line->is_matched && $statement->status !== App\Enums\BankStatementStatus::Reconciled)
                                    <form method="POST" action="{{ route('admin.accounting.bank.statement-lines.unmatch', $line) }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-erp-accent">{{ __('Unmatch') }}</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-slate-500">{{ __('No lines on this statement.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
