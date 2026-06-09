<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-3 py-2 text-left">{{ __('Credit note') }}</th>
                <th class="px-3 py-2 text-left">{{ __('Date') }}</th>
                <th class="px-3 py-2 text-right">{{ __('Amount') }}</th>
                <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($creditNotes as $creditNote)
                <tr>
                    <td class="px-3 py-2"><a href="{{ route('admin.invoices.show', $creditNote) }}" class="font-mono text-erp-accent">{{ $creditNote->invoice_number }}</a></td>
                    <td class="px-3 py-2">{{ $creditNote->invoice_date->format('M j, Y') }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format($creditNote->total_amount, 2) }}</td>
                    <td class="px-3 py-2"><x-admin.status-badge :variant="$creditNote->status->value === 'posted' ? 'success' : 'neutral'">{{ $creditNote->status->label() }}</x-admin.status-badge></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">{{ __('No credit notes') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if (method_exists($creditNotes, 'links'))
    <div class="mt-4"><x-admin.table-pagination :paginator="$creditNotes" /></div>
@endif
