<p class="mb-3 text-sm text-slate-600">{{ __('Jobs awaiting a quality inspection (not yet in the register).') }}</p>
<x-admin.data-table :searchable="false" :exportable="false">
    <x-slot:head>
        <tr>
            <th scope="col">{{ __('Job') }}</th>
            <th scope="col">{{ __('Customer') }}</th>
            <th scope="col" class="hidden md:table-cell">{{ __('Product') }}</th>
            <th scope="col" class="hidden lg:table-cell">{{ __('Due Date') }}</th>
            <th scope="col" class="hidden sm:table-cell">{{ __('Inspector') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @forelse ($register as $job)
            @php $row = $workspace->presentPendingRow($job); @endphp
            <tr>
                <td class="font-mono text-sm font-medium">{{ $row['job_card_number'] }}</td>
                <td>{{ $row['customer_name'] }}</td>
                <td class="hidden max-w-xs truncate md:table-cell" title="{{ $row['product'] }}">{{ $row['product'] }}</td>
                <td class="hidden tabular-nums lg:table-cell">{{ $row['due_date'] }}</td>
                <td class="hidden text-slate-400 sm:table-cell">{{ $row['inspector_name'] }}</td>
                <td><span class="erp-badge bg-amber-100 text-amber-900">{{ $row['status_label'] }}</span></td>
                <td class="erp-table-actions-col">
                    @if (! empty($row['job_url']) && Route::has('admin.production.job-cards.show'))
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="$row['job_url']">
                                {{ __('Inspect') }}
                            </x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <x-admin.empty-state
                        icon="badge-check"
                        :title="__('No pending inspections')"
                        :description="__('All jobs have moved past the QC stage or none are queued for inspection.')"
                    />
                </td>
            </tr>
        @endforelse
    </x-slot:body>
    @if ($register->hasPages())
        <x-slot:footer>{{ $register->links() }}</x-slot:footer>
    @endif
</x-admin.data-table>
