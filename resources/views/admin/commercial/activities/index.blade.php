<x-admin-layout :title="__('Activities')" :breadcrumbs="[['label' => __('Commercial')], ['label' => __('Activities')]]">
    <x-admin.page-header :title="__('Customer activities')" :description="__('Calls, meetings, emails, and touchpoints.')">
        <x-slot name="actions">
            @can('create', App\Models\Crm\CustomerActivity::class)
                <a href="{{ route('admin.commercial.activities.create') }}" class="erp-btn-primary">{{ __('Log activity') }}</a>
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
<<<<<<< Updated upstream
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
=======
            </select>
            <x-admin.form-status-select
                form-key="activity.create"
                name="status"
                :field="['label' => __('Status'), 'required' => false]"
                :value="$filters['status'] ?? null"
                select-class="erp-input text-sm"
                :allow-empty="true"
                :empty-label="__('All statuses')"
                :show-label="false"
            />
            <input type="date" name="date_from" class="erp-input text-sm" value="{{ $filters['date_from'] ?? '' }}" placeholder="{{ __('From') }}">
            <input type="date" name="date_to" class="erp-input text-sm" value="{{ $filters['date_to'] ?? '' }}" placeholder="{{ __('To') }}">
            <div class="flex gap-2 md:col-span-4 lg:col-span-7">
                <button type="submit" class="erp-btn-secondary">{{ __('Filter') }}</button>
                <a href="{{ route('admin.commercial.activities.index') }}" class="erp-btn-secondary">{{ __('Reset') }}</a>
            </div>
        </form>
>>>>>>> Stashed changes
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('When') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Subject') }}</th>
                        <th>{{ __('Customer / Lead') }}</th>
                        <th>{{ __('Assigned') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
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
                        <tr><td colspan="7" class="py-8 text-center text-slate-500">{{ __('No activities found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-erp-border px-4 py-3">
            <x-admin.table-pagination :paginator="$activities" />
        </div>
    </x-admin.card>
</x-admin-layout>
