@if (! empty($tabData['restricted']))
    <x-admin.empty-state :title="__('Access restricted')" :description="__('You need artwork view permission to see this tab.')" />
@else
    @php($requests = $tabData['requests'])
    <x-admin.data-table :searchable="false" :exportable="false" :filterable="false">
        <x-slot:head>
            <tr>
                <th>{{ __('Request') }}</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Approval status') }}</th>
                <th>{{ __('Revisions') }}</th>
                <th>{{ __('Files') }}</th>
            </tr>
        </x-slot:head>
        <x-slot:body>
            @forelse ($requests as $request)
                @php($latestApproval = $request->approvals->first())
                <tr>
                    <td>
                        <a href="{{ route('admin.artwork.show', $request) }}" class="font-medium text-erp-accent hover:text-erp-accent-hover" data-turbo-frame="erp-main">{{ $request->request_number }}</a>
                    </td>
                    <td>{{ $request->title }}</td>
                    <td>
                        <x-admin.enum-status-badge :status="$request->status->value" />
                        @if ($latestApproval)
                            <span class="ms-1 text-xs text-slate-500">({{ $latestApproval->decision->value }})</span>
                        @endif
                    </td>
                    <td class="tabular-nums">{{ max(0, $request->current_version - 1) }}</td>
                    <td class="tabular-nums">{{ $request->files_count }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-500 py-6">{{ __('No artwork requests for this customer.') }}</td></tr>
            @endforelse
        </x-slot:body>
        @if ($requests->hasPages())
            <x-slot:footer>
                <x-admin.table-pagination :paginator="$requests" />
            </x-slot:footer>
        @endif
    </x-admin.data-table>
@endif
