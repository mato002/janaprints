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
        @forelse ($register as $check)
            @php
                $job = $check->jobCard;
                $failed = in_array($check->result->value, ['failed', 'rework_required'], true);
            @endphp
            <tr class="{{ $failed ? 'bg-red-50/50' : '' }}">
                <td class="font-mono text-sm font-medium">{{ $job?->job_card_number ?? '—' }}</td>
                <td>{{ $job?->customer?->company_name ?? '—' }}</td>
                <td>{{ $check->checker?->name ?? '—' }}</td>
                <td><x-admin.enum-status-badge :status="$check->result->value" /></td>
                <td class="tabular-nums">{{ $check->checked_at?->format('M j, Y H:i') ?? '—' }}</td>
                <td class="max-w-xs truncate text-sm text-slate-600" title="{{ $check->comments }}">
                    {{ $check->comments ? Str::limit($check->comments, 60) : '—' }}
                </td>
                <td class="erp-table-actions-col">
                    @if ($job && Route::has('admin.production.job-cards.show'))
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.production.job-cards.show', $job)">
                                {{ __('View job') }}
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
                        :title="__('No inspections recorded')"
                        :description="__('QC checks from job cards will appear here.')"
                    />
                </td>
            </tr>
        @endforelse
    </x-slot:body>
    @if ($register->hasPages())
        <x-slot:footer><x-admin.table-pagination :paginator="$register" /></x-slot:footer>
    @endif
</x-admin.data-table>
