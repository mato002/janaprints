<x-admin-layout :title="$vendor->vendor_name" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Vendor Management')], ['label' => __('Vendors'), 'url' => route('admin.procurement.vendors.index')], ['label' => $vendor->vendor_name]]">
    <x-admin.page-header :title="$vendor->vendor_name" :description="$vendor->vendor_code">
        <x-slot name="actions">
            @can('update', $vendor)
                <a href="{{ route('admin.procurement.vendors.edit', $vendor) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-admin.card>
            <dl class="space-y-3 text-sm">
                <div><dt class="text-slate-500">{{ __('Type') }}</dt><dd>{{ str($vendor->vendor_type->value)->headline() }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Phone') }}</dt><dd>{{ $vendor->phone ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Email') }}</dt><dd>{{ $vendor->email ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Payment terms') }}</dt><dd>{{ $vendor->payment_terms ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd><x-admin.status-badge>{{ str($vendor->status->value)->headline() }}</x-admin.status-badge></dd></div>
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Contacts') }}</h3>
            @forelse ($vendor->contacts as $contact)
                <div class="mb-2 rounded border border-erp-border p-2 text-sm">
                    <div class="font-medium">{{ $contact->name }}</div>
                    <div class="text-slate-500">{{ $contact->job_title }} · {{ $contact->phone }} · {{ $contact->email }}</div>
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('No contacts yet.') }}</p>
            @endforelse
            @can('update', $vendor)
                <form method="POST" action="{{ route('admin.procurement.vendors.contacts.store', $vendor) }}" class="mt-4 space-y-2 border-t border-erp-border pt-4">
                    @csrf
                    <x-text-input name="name" placeholder="{{ __('Contact name') }}" class="w-full" required />
                    <x-text-input name="phone" placeholder="{{ __('Phone') }}" class="w-full" />
                    <x-text-input name="email" placeholder="{{ __('Email') }}" class="w-full" />
                    <x-primary-button>{{ __('Add contact') }}</x-primary-button>
                </form>
            @endcan
        </x-admin.card>
    </div>
</x-admin-layout>
