<x-admin-layout :title="$customer->company_name" :breadcrumbs="[['label' => __('Customers'), 'url' => route('admin.crm.customers.index')], ['label' => $customer->company_name]]">
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold">{{ $customer->company_name }}</h2>
            <p class="text-sm text-gray-500">{{ $customer->customer_code }} · {{ $customer->branch?->name }}</p>
        </div>
        <div class="flex items-center gap-3">
            <x-admin.customer-360-action :customer="$customer" />
            @can('update', $customer)<a href="{{ route('admin.crm.customers.edit', $customer) }}" class="text-erp-accent hover:text-erp-accent-hover text-sm">{{ __('Edit') }}</a>@endcan
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="font-medium mb-3">{{ __('Contacts') }}</h3>
            @foreach ($customer->contacts as $contact)
                <div class="text-sm border-b py-2 flex justify-between">
                    <span>{{ $contact->name }} @if($contact->is_primary)<span class="text-xs text-erp-accent hover:text-erp-accent-hover">({{ __('Primary') }})</span>@endif</span>
                    @can('update', $customer)
                        <form method="POST" action="{{ route('admin.crm.customers.contacts.destroy', [$customer, $contact]) }}">@csrf @method('DELETE')
                            <button class="text-red-600 text-xs">{{ __('Remove') }}</button></form>
                    @endcan
                </div>
            @endforeach
            @can('update', $customer)
                <form method="POST" action="{{ route('admin.crm.customers.contacts.store', $customer) }}" class="mt-4 space-y-2">@csrf
                    <x-text-input name="name" placeholder="{{ __('Name') }}" class="w-full" required />
                    <x-text-input name="phone" placeholder="{{ __('Phone') }}" class="w-full" />
                    <x-primary-button class="text-xs">{{ __('Add contact') }}</x-primary-button>
                </form>
            @endcan
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="font-medium mb-3">{{ __('Notes') }}</h3>
            @foreach ($customer->notes as $note)
                <div class="text-sm border-b py-2">
                    <p>{{ $note->note }}</p>
                    <p class="text-xs text-gray-400">{{ $note->user?->name }} · {{ $note->created_at }}</p>
                </div>
            @endforeach
            @can('update', $customer)
                <form method="POST" action="{{ route('admin.crm.customers.notes.store', $customer) }}" class="mt-4">@csrf
                    <textarea name="note" class="w-full rounded-md border-gray-300" rows="2" required></textarea>
                    <x-primary-button class="mt-2 text-xs">{{ __('Add note') }}</x-primary-button>
                </form>
            @endcan
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="font-medium mb-3">{{ __('Files') }}</h3>
            @foreach ($customer->files as $file)
                <div class="text-sm flex justify-between py-1">
                    <span>{{ $file->original_name }}</span>
                    @can('update', $customer)
                        <form method="POST" action="{{ route('admin.crm.customers.files.destroy', [$customer, $file]) }}">@csrf @method('DELETE')
                            <button class="text-red-600 text-xs">{{ __('Remove') }}</button></form>
                    @endcan
                </div>
            @endforeach
            @can('update', $customer)
                <form method="POST" action="{{ route('admin.crm.customers.files.store', $customer) }}" enctype="multipart/form-data" data-turbo-frame="_top" class="mt-4">@csrf
                    <input type="file" name="file" class="text-sm" required>
                    <x-primary-button class="mt-2 text-xs">{{ __('Upload') }}</x-primary-button>
                </form>
            @endcan
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-medium">{{ __('Activities') }}</h3>
                @can('viewAny', App\Models\Crm\CustomerActivity::class)
                    <a href="{{ route('admin.commercial.activities.index', ['customer_id' => $customer->id]) }}" class="text-xs text-erp-accent">{{ __('All activities') }}</a>
                @endcan
            </div>
            @foreach ($customer->activities->sortByDesc('activity_at')->take(10) as $activity)
                <div class="text-sm border-b py-2">
                    @can('view', $activity)
                        <a href="{{ route('admin.commercial.activities.show', $activity) }}" class="font-medium text-erp-accent">{{ $activity->subject }}</a>
                    @else
                        <strong>{{ $activity->subject }}</strong>
                    @endcan
                    <p>{{ ucfirst(str_replace('_', ' ', $activity->activity_type->value)) }} · {{ $activity->user?->name }}</p>
                    <p class="text-xs text-gray-400">{{ $activity->activity_at }}</p>
                </div>
            @endforeach
            @can('create', App\Models\Crm\CustomerActivity::class)
                <form method="POST" action="{{ route('admin.crm.customers.activities.store', $customer) }}" class="mt-4 space-y-2">@csrf
                    <select name="activity_type" class="w-full rounded-md border-gray-300 text-sm">
                        @foreach (App\Enums\ActivityType::cases() as $t)<option value="{{ $t->value }}">{{ $t->name }}</option>@endforeach
                    </select>
                    <x-text-input name="subject" placeholder="{{ __('Subject') }}" class="w-full" required />
                    <x-text-input name="activity_at" type="datetime-local" class="w-full" value="{{ now()->format('Y-m-d\TH:i') }}" required />
                    <x-primary-button class="text-xs">{{ __('Log activity') }}</x-primary-button>
                </form>
            @endcan
        </div>
    </div>
</x-admin-layout>
