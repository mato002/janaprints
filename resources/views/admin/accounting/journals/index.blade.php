<x-admin-layout :title="__('Journals')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Journals')]]">
    <x-admin.page-header :title="__('Journal entries')">
        <x-slot name="actions">
            @can('create', App\Models\Accounting\Journal::class)
                <a href="{{ route('admin.accounting.journals.create') }}" class="erp-btn-primary">{{ __('New journal') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table :search-placeholder="__('Search journals…')">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Number') }}</th>
                <th scope="col">{{ __('Date') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Period') }}</th>
                <th scope="col">{{ __('Debit') }}</th>
                <th scope="col">{{ __('Credit') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($journals as $journal)
                <tr x-show="rowVisible(@js(strtolower($journal->journal_number.' '.$journal->reference.' '.$journal->status->value)))">
                    <td>
                        <a href="{{ route('admin.accounting.journals.show', $journal) }}" class="font-mono text-sm text-erp-accent">{{ $journal->journal_number }}</a>
                        @if ($journal->entry_type === App\Enums\JournalEntryType::System)
                            <span class="erp-badge text-[10px]">{{ __('System') }}</span>
                        @elseif ($journal->entry_type === App\Enums\JournalEntryType::Reversal)
                            <span class="erp-badge text-[10px]">{{ __('Reversal') }}</span>
                        @endif
                    </td>
                    <td class="text-sm">{{ $journal->journal_date->format('Y-m-d') }}</td>
                    <td class="hidden md:table-cell text-sm text-slate-600">{{ $journal->accountingPeriod?->code }}</td>
                    <td class="text-sm font-mono">{{ number_format($journal->total_debit, 2) }}</td>
                    <td class="text-sm font-mono">{{ number_format($journal->total_credit, 2) }}</td>
                    <td>
                        <x-admin.status-badge :variant="match($journal->status) {
                            App\Enums\JournalStatus::Draft => 'neutral',
                            App\Enums\JournalStatus::Posted => 'success',
                            App\Enums\JournalStatus::Reversed => 'warning',
                        }">{{ $journal->status->label() }}</x-admin.status-badge>
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.accounting.journals.show', $journal)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-admin.empty-state icon="document-text" :title="__('No journals yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$journals" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
