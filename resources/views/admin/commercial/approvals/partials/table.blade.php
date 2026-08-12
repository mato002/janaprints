@props(['rows', 'title'])

<x-admin.card class="mb-4">
    <div class="border-b border-erp-border px-4 py-3 font-semibold">{{ $title }}</div>
    <div class="overflow-x-auto">
        <table class="erp-table w-full">
            <thead>
                <tr>
                    <th>{{ __('Document') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Branch') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Requested By') }}</th>
                    <th>{{ __('Submitted') }}</th>
                    <th>{{ __('Age') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="font-medium">{{ $row['document'] }}</td>
                        <td>{{ $row['customer'] }}</td>
                        <td>{{ $row['branch'] }}</td>
                        <td>{{ $row['amount'] }}</td>
                        <td>{{ $row['requested_by'] }}</td>
                        <td>{{ $row['submitted_at']?->format('d M Y') }}</td>
                        <td>{{ $row['age_label'] ?? (((int) ($row['age_days'] ?? 0)).'d') }}</td>
                        <td>{{ $row['status_label'] }}</td>
                        <td class="erp-table-actions-col">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ $row['view_url'] }}" class="erp-btn-secondary text-xs">{{ __('View') }}</a>
                                @if ($canAction && $row['approve_url'])
                                    @if (($row['type'] === 'quotation' && $canApproveQuotations) || ($row['type'] === 'sales_order' && $canConfirmOrders) || ($row['type'] === 'artwork' && $canApproveArtwork))
                                        <form method="POST" action="{{ $row['approve_url'] }}" class="inline">
                                            @csrf
                                            <button type="submit" class="erp-btn-primary text-xs">
                                                {{ $row['type'] === 'sales_order' ? __('Confirm') : __('Approve') }}
                                            </button>
                                        </form>
                                    @endif
                                @endif
                                @if ($canAction && $row['reject_url'] && $row['type'] === 'quotation' && $canRejectQuotations)
                                    <form method="POST" action="{{ $row['reject_url'] }}" class="inline">
                                        @csrf
                                        <button type="submit" class="erp-btn-secondary text-xs text-red-600">{{ __('Reject') }}</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-6 text-center text-slate-500">{{ __('No records.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
