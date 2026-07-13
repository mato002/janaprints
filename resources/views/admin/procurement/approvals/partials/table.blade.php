<x-admin.card class="mb-6">
    <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ $title }}</h3>
    @if ($rows->isEmpty())
        <x-admin.empty-state icon="clipboard-list" :title="__('No items in this queue.')" />
    @else
        <div class="overflow-x-auto">
            <table class="erp-table text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Document') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Submitted') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>
                                <div class="font-medium">{{ $row['document_label'] }}</div>
                                <div class="font-mono text-xs text-slate-500">{{ $row['document'] }}</div>
                            </td>
                            <td>{{ $row['rule_label'] }}</td>
                            <td>{{ number_format($row['amount'] ?? 0, 2) }}</td>
                            <td>{{ optional($row['submitted_at'])->format('Y-m-d H:i') ?: '—' }}</td>
                            <td><x-admin.enum-status-badge :status="$row['status']" /></td>
                            <td class="erp-table-actions-col">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($row['route'])
                                        <a href="{{ $row['route'] }}" class="text-erp-primary hover:underline">{{ __('Open') }}</a>
                                    @endif
                                    @if (! empty($row['can_act']) && ! empty($row['run_id']))
                                        <form method="POST" action="{{ route('admin.procurement.approvals.approve', $row['run_id']) }}" class="inline-flex items-center gap-1">
                                            @csrf
                                            <input type="hidden" name="notes" value="">
                                            <button type="submit" class="text-emerald-700 hover:underline">{{ __('Approve') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.procurement.approvals.reject', $row['run_id']) }}" class="inline-flex items-center gap-1" onsubmit="return confirm(@js(__('Reject this approval?')));">
                                            @csrf
                                            <input type="text" name="reason" class="erp-toolbar-input w-28" placeholder="{{ __('Reason') }}" required>
                                            <button type="submit" class="text-rose-700 hover:underline">{{ __('Reject') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-admin.card>
