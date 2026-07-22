<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="$hubUrl . '?' . http_build_query(array_merge(request()->except('page'), ['tab' => 'assignments']))" :reset-url="$hubUrl . '?tab=assignments'">
        <input type="hidden" name="tab" value="assignments">
        <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
            <option value="">{{ __('All statuses') }}</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </x-admin.index-toolbar>
</x-admin.card>

<x-admin.data-table :search-placeholder="__('Search assignments…')" export-filename="asset-assignments">
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('Asset') }}</th>
            <th scope="col">{{ __('Type') }}</th>
            <th scope="col">{{ __('Assigned to') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col">{{ __('Expected return') }}</th>
            <th scope="col">{{ __('Assigned at') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($assignments as $assignment)
            @php
                $assignee = $assignment->assignedEmployee?->full_name
                    ?? $assignment->assignedDepartment?->name
                    ?? $assignment->assignedUser?->name
                    ?? $assignment->assignedBranch?->name
                    ?? '';
                $search = strtolower(($assignment->asset?->asset_number ?? '').' '.($assignment->asset?->asset_name ?? '').' '.$assignee.' '.$assignment->status->value);
            @endphp
            <tr x-show="rowVisible(@js($search))">
                <td>
                    <span class="font-medium">{{ $assignment->asset?->asset_number }}</span>
                    <span class="text-slate-500"> — {{ $assignment->asset?->asset_name }}</span>
                </td>
                <td>{{ ucfirst($assignment->assignment_type->value) }}</td>
                <td>{{ $assignee !== '' ? $assignee : '—' }}</td>
                <td><x-admin.status-badge :variant="$assignment->status->badgeVariant()">{{ $assignment->status->label() }}</x-admin.status-badge></td>
                <td>{{ $assignment->expected_return_date?->format('Y-m-d') ?? '—' }}</td>
                <td class="whitespace-nowrap">{{ $assignment->assigned_at?->format('Y-m-d H:i') }}</td>
                <td class="erp-table-actions-col">
                    <x-admin.table-row-actions>
                        @if ($assignment->asset)
                            <x-admin.table-row-action :href="route('admin.assets.show', $assignment->asset)">{{ __('View asset') }}</x-admin.table-row-action>
                        @endif
                    </x-admin.table-row-actions>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <x-admin.empty-state icon="clipboard-list" :title="__('No assignments yet')" :description="__('Assign an asset to an employee or department to start custody tracking.')">
                        @can('assets.assign')
                            <x-slot name="action">
                                <x-admin.form-modal-link :href="route('admin.assets.custody.assignments.create')">{{ __('New assignment') }}</x-admin.form-modal-link>
                            </x-slot>
                        @endcan
                    </x-admin.empty-state>
                </td>
            </tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$assignments" /></x-slot>
</x-admin.data-table>
