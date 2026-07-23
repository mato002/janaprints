@php($activities = $tabData['activities'])

<x-admin.card class="mb-4" id="log-activity">
    <h3 class="mb-3 font-medium">{{ __('Log activity') }}</h3>
    @can('create', App\Models\Crm\CustomerActivity::class)
        <form method="POST" action="{{ route('admin.crm.customers.activities.store', $customer) }}" class="grid grid-cols-1 gap-3 md:grid-cols-2" data-turbo-frame="erp-main">
            @csrf
            <div>
                <label class="erp-label">{{ __('Type') }}</label>
                <select name="activity_type" class="erp-input w-full">
                    @foreach (App\Enums\ActivityType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('When') }}</label>
                <input type="datetime-local" name="activity_at" class="erp-input w-full" value="{{ now()->format('Y-m-d\TH:i') }}" required>
            </div>
            <div class="md:col-span-2">
                <label class="erp-label">{{ __('Subject') }}</label>
                <input type="text" name="subject" class="erp-input w-full" required>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="erp-btn-primary text-sm">{{ __('Log activity') }}</button>
            </div>
        </form>
    @else
        <p class="text-sm text-slate-500">{{ __('You do not have permission to log activities.') }}</p>
    @endcan
</x-admin.card>

<x-admin.data-table :searchable="false" :exportable="false" :filterable="false">
    <x-slot:head>
        <tr>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Subject') }}</th>
            <th>{{ __('User') }}</th>
            <th>{{ __('When') }}</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @forelse ($activities as $activity)
            <tr>
                <td>{{ $activity->activity_type->value }}</td>
                <td>{{ $activity->subject }}</td>
                <td>{{ $activity->user?->name ?? '—' }}</td>
                <td>{{ $activity->activity_at?->format('Y-m-d H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="py-6 text-center text-slate-500">{{ __('No activities logged yet.') }}</td></tr>
        @endforelse
    </x-slot:body>
    @if ($activities->hasPages())
        <x-slot:footer>
            <x-admin.table-pagination :paginator="$activities" />
        </x-slot:footer>
    @endif
</x-admin.data-table>
