<x-admin-layout :title="$journal->journal_number" :breadcrumbs="[['label' => __('Journals'), 'url' => route('admin.accounting.journals.index')], ['label' => $journal->journal_number]]">
    <x-admin.page-header :title="$journal->journal_number" :description="$journal->description">
        <x-admin.status-badge :variant="match($journal->status) {
            App\Enums\JournalStatus::Draft => 'neutral',
            App\Enums\JournalStatus::Posted => 'success',
            App\Enums\JournalStatus::Reversed => 'warning',
        }">{{ $journal->status->label() }}</x-admin.status-badge>
        @can('update', $journal)
            <a href="{{ route('admin.accounting.journals.edit', $journal) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
        @endcan
    </x-admin.page-header>

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-admin.kpi-widget :label="__('Total debit')" :value="number_format($journal->total_debit, 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Total credit')" :value="number_format($journal->total_credit, 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Balanced')" :value="$journal->isBalanced() ? __('Yes') : __('No')" icon="scale" />
        <x-admin.kpi-widget :label="__('Period')" :value="$journal->accountingPeriod?->code ?? '—'" icon="calendar" />
    </div>

    <x-admin.card class="mb-4">
        <dl class="grid gap-2 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">{{ __('Date') }}</dt><dd>{{ $journal->journal_date->format('Y-m-d') }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Reference') }}</dt><dd>{{ $journal->reference ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Type') }}</dt><dd>{{ $journal->entry_type->label() }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Created by') }}</dt><dd>{{ $journal->creator?->name ?? '—' }}</dd></div>
            @if ($journal->posted_at)
                <div><dt class="text-slate-500">{{ __('Posted') }}</dt><dd>{{ $journal->posted_at->format('Y-m-d H:i') }} — {{ $journal->poster?->name }}</dd></div>
            @endif
            @if ($journal->reversalOf)
                <div><dt class="text-slate-500">{{ __('Reverses') }}</dt><dd><a href="{{ route('admin.accounting.journals.show', $journal->reversalOf) }}" class="text-erp-accent">{{ $journal->reversalOf->journal_number }}</a></dd></div>
            @endif
            @if ($journal->reversedBy)
                <div><dt class="text-slate-500">{{ __('Reversed by') }}</dt><dd><a href="{{ route('admin.accounting.journals.show', $journal->reversedBy) }}" class="text-erp-accent">{{ $journal->reversedBy->journal_number }}</a></dd></div>
            @endif
            @if ($journal->posting_event)
                <div><dt class="text-slate-500">{{ __('Posting event') }}</dt><dd class="font-mono text-xs">{{ $journal->posting_event }}</dd></div>
            @endif
            @if ($journal->source_type)
                <div><dt class="text-slate-500">{{ __('Source') }}</dt><dd class="font-mono text-xs">{{ $journal->source_type }} #{{ $journal->source_id }}</dd></div>
            @endif
            @if ($journal->postingTemplate)
                <div><dt class="text-slate-500">{{ __('Template') }}</dt><dd><a href="{{ route('admin.accounting.posting.templates.show', $journal->postingTemplate) }}" class="text-erp-accent">{{ $journal->postingTemplate->code }}</a></dd></div>
            @endif
        </dl>
    </x-admin.card>

    <x-admin.card class="mb-4">
        <h3 class="mb-3 font-medium">{{ __('Lines') }}</h3>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-[11px] uppercase text-slate-400"><th>{{ __('Account') }}</th><th>{{ __('Debit') }}</th><th>{{ __('Credit') }}</th><th>{{ __('Note') }}</th></tr></thead>
            <tbody>
                @foreach ($journal->lines as $line)
                    <tr class="border-t border-erp-border">
                        <td class="py-2 font-mono text-xs">{{ $line->glAccount->code }} — {{ $line->glAccount->name }}</td>
                        <td class="py-2">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
                        <td class="py-2">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
                        <td class="py-2 text-slate-500">{{ $line->description ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>

    <div class="flex flex-wrap gap-2">
        @can('post', $journal)
            @if ($journal->status === App\Enums\JournalStatus::Draft)
                <form method="POST" action="{{ route('admin.accounting.journals.post', $journal) }}">@csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Post journal') }}</button>
                </form>
            @endif
        @endcan
        @can('reverse', $journal)
            @if ($journal->status === App\Enums\JournalStatus::Posted)
                <form method="POST" action="{{ route('admin.accounting.journals.reverse', $journal) }}" class="flex gap-2 items-end" onsubmit="return confirm(@js(__('Post a reversing journal entry?')))">
                    @csrf
                    <input type="text" name="description" placeholder="{{ __('Reversal note (optional)') }}" class="erp-input text-sm">
                    <button type="submit" class="erp-btn-secondary">{{ __('Reverse') }}</button>
                </form>
            @endif
        @endcan
        @can('delete', $journal)
            <form method="POST" action="{{ route('admin.accounting.journals.destroy', $journal) }}" onsubmit="return confirm(@js(__('Delete this draft?')))">
                @csrf @method('DELETE')
                <button type="submit" class="erp-btn-secondary text-red-600">{{ __('Delete') }}</button>
            </form>
        @endcan
    </div>
</x-admin-layout>
