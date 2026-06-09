<x-admin-layout :title="__('General Ledger')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('General Ledger')]]">
    <x-admin.page-header :title="__('General Ledger')" :description="__('Posted journal lines')" />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.accounting.ledger.index')" :reset-url="route('admin.accounting.ledger.index')">
            <select name="period_id" class="erp-toolbar-select" aria-label="{{ __('Period') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($periods as $period)
                    <option value="{{ $period->id }}" @selected(($filters['period_id'] ?? null) == $period->id)>{{ $period->code }}</option>
                @endforeach
            </select>
            <select name="account_id" class="erp-toolbar-select" aria-label="{{ __('Account') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" @selected(($filters['account_id'] ?? null) == $account->id)>{{ $account->code }}</option>
                @endforeach
            </select>
            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Date') }}</th>
                <th scope="col">{{ __('Journal') }}</th>
                <th scope="col">{{ __('Account') }}</th>
                <th scope="col">{{ __('Debit') }}</th>
                <th scope="col">{{ __('Credit') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($entries as $entry)
                <tr>
                    <td class="text-sm">{{ $entry->journal_date }}</td>
                    <td class="font-mono text-xs">
                        <a href="{{ route('admin.accounting.journals.show', $entry->journal_id) }}" class="text-erp-accent">{{ $entry->journal_number }}</a>
                    </td>
                    <td class="text-sm">{{ $entry->account_code }} — {{ $entry->account_name }}</td>
                    <td class="font-mono text-sm">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '—' }}</td>
                    <td class="font-mono text-sm">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="book-open" :title="__('No posted entries')" :description="__('Post a journal to see general ledger activity.')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
