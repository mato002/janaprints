<x-admin.data-table :searchable="false" :exportable="false">
    <x-slot:head>
        <tr>
            <th scope="col">{{ __('Job') }}</th>
            <th scope="col" class="hidden sm:table-cell">{{ __('Customer') }}</th>
            <th scope="col" class="hidden md:table-cell">{{ __('Product') }}</th>
            <th scope="col" class="hidden lg:table-cell">{{ __('Inspector') }}</th>
            <th scope="col">{{ __('Result') }}</th>
            <th scope="col" class="hidden md:table-cell">{{ __('Rework Count') }}</th>
            <th scope="col" class="hidden xl:table-cell">{{ __('Hold Reason') }}</th>
            <th scope="col" class="hidden lg:table-cell">{{ __('Inspection Date') }}</th>
            <th scope="col" class="hidden sm:table-cell">{{ __('Status') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @forelse ($register as $check)
            @php $row = $workspace->presentRegisterRow($check); @endphp
            <tr class="{{ $row['is_failed_row'] ? 'bg-red-50/50' : '' }}">
                <td class="font-mono text-sm font-medium">{{ $row['job_card_number'] }}</td>
                <td class="hidden sm:table-cell">{{ $row['customer_name'] }}</td>
                <td class="hidden max-w-xs truncate md:table-cell" title="{{ $row['product'] }}">{{ $row['product'] }}</td>
                <td class="hidden lg:table-cell">{{ $row['inspector_name'] }}</td>
                <td><x-admin.enum-status-badge :status="$row['result']->value" /></td>
                <td class="hidden tabular-nums md:table-cell">{{ $row['rework_count'] }}</td>
                <td class="hidden max-w-xs truncate text-sm text-slate-600 xl:table-cell" title="{{ $row['hold_reason'] !== '—' ? $row['hold_reason'] : '' }}">
                    {{ $row['hold_reason'] !== '—' ? Str::limit($row['hold_reason'], 40) : '—' }}
                </td>
                <td class="hidden tabular-nums lg:table-cell">{{ $row['inspection_date'] }}</td>
                <td class="hidden sm:table-cell text-sm text-slate-600">{{ $row['status_label'] }}</td>
                <td class="erp-table-actions-col">
                    @if (! empty($row['job_url']) && Route::has('admin.production.job-cards.show'))
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="$row['job_url']">
                                {{ __('View job') }}
                            </x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10">
                    <x-admin.empty-state
                        icon="badge-check"
                        :title="__('No inspections recorded')"
                        :description="__('QC checks from job cards will appear here.')"
                    />
                </td>
            </tr>
        @endforelse
    </x-slot:body>
    @if ($register->hasPages())
        <x-slot:footer>{{ $register->links() }}</x-slot:footer>
    @endif
</x-admin.data-table>
