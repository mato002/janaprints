@php
    use App\Support\Navigation\WorkspaceEmbed;

    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<x-admin.card>
    <x-admin.index-toolbar :action="$hubUrl" :reset-url="$hubUrl . '?tab=queue'" class="mb-4">
        <input type="hidden" name="tab" value="queue">
        <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
            <option value="">{{ __('All statuses') }}</option>
            @foreach (\App\Enums\CapitalizationCandidateStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </x-admin.index-toolbar>

    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Candidate') }}</th>
                    <th>{{ __('GRN') }}</th>
                    <th>{{ __('Vendor') }}</th>
                    <th>{{ __('Item') }}</th>
                    <th>{{ __('Qty') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Branch') }}</th>
                    <th>{{ __('Received') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($candidates as $candidate)
                    <tr>
                        <td>{{ $candidate->candidate_number }}</td>
                        <td>{{ $candidate->goodsReceipt?->receipt_number }}</td>
                        <td>{{ $candidate->vendor?->vendor_name ?? '—' }}</td>
                        <td>{{ $candidate->purchaseOrderItem?->description ?? '—' }}</td>
                        <td>{{ number_format($candidate->remainingQuantity(), 0) }} / {{ number_format($candidate->quantity, 0) }}</td>
                        <td>{{ number_format($candidate->line_amount, 2) }}</td>
                        <td>{{ $candidate->branch?->name ?? '—' }}</td>
                        <td>{{ $candidate->received_date?->format('Y-m-d') }}</td>
                        <td><x-admin.status-badge :status="$candidate->status->value" :label="$candidate->status->label()" /></td>
                        <td>
                            @can('capitalize', $candidate)
                                @if (in_array($candidate->status, [\App\Enums\CapitalizationCandidateStatus::Pending, \App\Enums\CapitalizationCandidateStatus::Ready], true))
                                    <a href="{{ route('admin.assets.acquisitions.workbench', $candidate) }}" class="erp-link" data-turbo-frame="erp-main" data-turbo-action="advance">{{ __('Capitalize') }}</a>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-slate-500">{{ __('No capitalization candidates.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4 border-t border-erp-border pt-3">
        <x-admin.table-pagination :paginator="$candidates" />
    </div>
</x-admin.card>
