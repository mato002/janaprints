<x-admin-layout :title="__('Activities')" :breadcrumbs="[['label' => __('Commercial')], ['label' => __('Activities')]]">
    @include('admin.crm.partials.desk-mode-nav', ['activeCrmView' => \App\Support\Crm\CrmDeskViews::ACTIVITIES])

    <x-admin.page-header :title="__('Customer activities')" :description="__('Calls, meetings, emails, and touchpoints.')">
        <x-slot name="actions">
            @can('create', App\Models\Crm\CustomerActivity::class)
                <a href="{{ route('admin.commercial.activities.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Log activity') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.commercial.activities.index')" :reset-url="route('admin.commercial.activities.index')">
            <x-admin.filter-pill-select name="customer_id" :label="__('Customer')" :selected="$filters['customer_id'] ?? ''">
                <option value="">{{ __('All customers') }}</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(($filters['customer_id'] ?? '') == $customer->id)>{{ $customer->company_name }}</option>
                @endforeach
            </x-admin.filter-pill-select>
            <x-admin.filter-pill-select name="lead_id" :label="__('Lead')" :selected="$filters['lead_id'] ?? ''">
                <option value="">{{ __('All leads') }}</option>
                @foreach ($leads as $lead)
                    <option value="{{ $lead->id }}" @selected(($filters['lead_id'] ?? '') == $lead->id)>{{ $lead->lead_name }}</option>
                @endforeach
            </x-admin.filter-pill-select>
            <x-admin.filter-pill-select name="activity_type" :label="__('Activity type')" :selected="$filters['activity_type'] ?? ''">
                <option value="">{{ __('All types') }}</option>
                @foreach (App\Enums\ActivityType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(($filters['activity_type'] ?? '') === $type->value)>{{ ucfirst(str_replace('_', ' ', $type->value)) }}</option>
                @endforeach
            </x-admin.filter-pill-select>
            <x-admin.filter-pill-select name="user_id" :label="__('Assignee')" :selected="$filters['user_id'] ?? ''">
                <option value="">{{ __('All assignees') }}</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </x-admin.filter-pill-select>
            <x-admin.filter-pill-date name="date_from" :label="__('From date')" :value="$filters['date_from'] ?? ''" />
            <x-admin.filter-pill-date name="date_to" :label="__('To date')" :value="$filters['date_to'] ?? ''" />
            <x-admin.filter-pill-select name="status" :label="__('Status')" :selected="$filters['status'] ?? ''">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (App\Enums\ActivityStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst($status->value) }}</option>
                @endforeach
            </x-admin.filter-pill-select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table
        :search-placeholder="__('Search activities…')"
        export-filename="customer-activities"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('When') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('Subject') }}</th>
                <th scope="col">{{ __('Customer / Lead') }}</th>
                <th scope="col">{{ __('Assigned') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($activities as $activity)
                @php
                    $party = $activity->customer?->company_name ?? $activity->lead?->lead_name ?? '';
                    $search = strtolower($activity->subject.' '.$activity->activity_type->value.' '.$party.' '.($activity->user?->name ?? '').' '.$activity->status->value);
                @endphp
                <tr x-show="rowVisible(@js($search))">
                    <td class="whitespace-nowrap">{{ $activity->activity_at->format('Y-m-d H:i') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $activity->activity_type->value)) }}</td>
                    <td class="font-medium">{{ $activity->subject }}</td>
                    <td>
                        @if ($activity->customer)
                            {{ $activity->customer->company_name }}
                        @elseif ($activity->lead)
                            {{ $activity->lead->lead_name }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $activity->user?->name ?? '—' }}</td>
                    <td><x-admin.enum-status-badge :status="$activity->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.commercial.activities.show', $activity)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $activity)
                                <x-admin.table-row-action :href="route('admin.commercial.activities.edit', $activity)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-admin.empty-state icon="clipboard-list" :title="__('No activities found')" :description="__('Log a call, meeting, or email to start the trail.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$activities" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
