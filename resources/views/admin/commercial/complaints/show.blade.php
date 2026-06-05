<x-admin-layout :title="$complaint->subject" :breadcrumbs="[['label' => __('Complaints'), 'url' => route('admin.commercial.complaints.index')], ['label' => $complaint->subject]]">
    <x-admin.page-header :title="$complaint->subject">
        <x-slot name="actions">
            @can('update', $complaint)
                <a href="{{ route('admin.commercial.complaints.edit', $complaint) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card class="mb-4 p-4">
        <div class="grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
            <div><span class="text-slate-500">{{ __('Status') }}</span><div class="font-medium">{{ $complaint->status->label() }}</div></div>
            <div><span class="text-slate-500">{{ __('Priority') }}</span><div>{{ $complaint->priority->label() }}</div></div>
            <div><span class="text-slate-500">{{ __('Source') }}</span><div>{{ $complaint->source->label() }}</div></div>
            <div><span class="text-slate-500">{{ __('Customer') }}</span><div>{{ $complaint->customer?->company_name ?? '—' }}</div></div>
        </div>
        <p class="mt-4 whitespace-pre-wrap text-sm text-slate-700">{{ $complaint->description }}</p>
        @if ($complaint->resolution_notes)
            <p class="mt-4 rounded bg-slate-50 p-3 text-sm"><strong>{{ __('Resolution') }}:</strong> {{ $complaint->resolution_notes }}</p>
        @endif
    </x-admin.card>

    @can('update', $complaint)
        <x-admin.card class="mb-4 p-4">
            <form method="POST" action="{{ route('admin.commercial.complaints.assign', $complaint) }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="erp-label">{{ __('Assign to') }}</label>
                    <select name="assigned_to" class="erp-input" required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected($complaint->assigned_to == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="erp-btn-secondary">{{ __('Assign') }}</button>
            </form>
        </x-admin.card>
    @endcan

    @can('resolve', $complaint)
        <div class="flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.commercial.complaints.resolve', $complaint) }}">@csrf<button class="erp-btn-primary">{{ __('Resolve') }}</button></form>
            <form method="POST" action="{{ route('admin.commercial.complaints.close', $complaint) }}">@csrf<button class="erp-btn-secondary">{{ __('Close') }}</button></form>
            <form method="POST" action="{{ route('admin.commercial.complaints.reopen', $complaint) }}">@csrf<button class="erp-btn-secondary">{{ __('Reopen') }}</button></form>
        </div>
    @endcan
</x-admin-layout>
