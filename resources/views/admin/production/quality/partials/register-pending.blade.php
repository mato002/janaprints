<p class="mb-3 text-sm text-slate-600">{{ __('Jobs awaiting a quality inspection (not yet in the register).') }}</p>
<x-admin.data-table :searchable="false" :exportable="false">
    <x-slot:head>
        <tr>
            <th scope="col">{{ __('Job') }}</th>
            <th scope="col">{{ __('Customer') }}</th>
            <th scope="col">{{ __('Inspector') }}</th>
            <th scope="col">{{ __('Result') }}</th>
            <th scope="col">{{ __('Inspection Date') }}</th>
            <th scope="col">{{ __('Notes') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @forelse ($register as $job)
            <tr>
                <td class="font-mono text-sm font-medium">{{ $job->job_card_number }}</td>
                <td>{{ $job->customer?->company_name ?? '—' }}</td>
                <td class="text-slate-400">—</td>
                <td><span class="erp-badge bg-amber-100 text-amber-900">{{ __('Pending') }}</span></td>
                <td class="text-slate-400">—</td>
                <td class="text-slate-400">—</td>
                <td class="erp-table-actions-col">
                    @if (Route::has('admin.production.job-cards.show'))
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.production.job-cards.show', $job)">
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
        <x-slot:footer><x-admin.table-pagination :paginator="$register" /></x-slot:footer>
    @endif
</x-admin.data-table>
