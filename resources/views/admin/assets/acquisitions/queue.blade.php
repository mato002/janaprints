<x-admin-layout :title="__('Capitalization Queue')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Capitalization Queue')]]">
    <x-admin.page-header :title="__('Capitalization Queue')" :description="__('Received asset purchases awaiting capitalization.')" />

    <x-admin.card>
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()" class="mb-4">
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
                                        <a href="{{ route('admin.assets.acquisitions.workbench', $candidate) }}" class="erp-link">{{ __('Capitalize') }}</a>
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
        <div class="mt-4">{{ $candidates->links() }}</div>
    </x-admin.card>
</x-admin-layout>
