<x-admin-layout
    :title="__('Contact Message')"
    :breadcrumbs="[
        ['label' => __('Commercial'), 'url' => route('admin.workspaces.commercial')],
        ['label' => __('Customer Service'), 'url' => route('admin.workspaces.commercial.section', 'customer-service')],
        ['label' => __('Contact Messages'), 'url' => route('admin.public-contact-messages.index')],
        ['label' => \Illuminate\Support\Str::limit($contactMessage->subject, 40)],
    ]"
>
    <x-admin.page-header :title="$contactMessage->subject" :description="$contactMessage->name">
        <x-slot name="actions">
            <x-admin.status-badge :variant="$contactMessage->status->badgeVariant()">
                {{ $contactMessage->status->label() }}
            </x-admin.status-badge>
        </x-slot>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <x-admin.card>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Message details') }}</h3>
                <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div><dt class="text-slate-500">{{ __('Name') }}</dt><dd class="font-medium">{{ $contactMessage->name }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Company') }}</dt><dd>{{ $contactMessage->company ?: '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Email') }}</dt><dd><a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></dd></div>
                    <div><dt class="text-slate-500">{{ __('Phone') }}</dt><dd>{{ $contactMessage->phone ?: '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Subject') }}</dt><dd>{{ $contactMessage->subject }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Received') }}</dt><dd>{{ $contactMessage->created_at->format('Y-m-d H:i') }}</dd></div>
                </dl>
                <div class="mt-6">
                    <dt class="mb-2 text-sm text-slate-500">{{ __('Message') }}</dt>
                    <dd class="rounded-lg bg-slate-50 p-4 text-sm leading-relaxed whitespace-pre-wrap">{{ $contactMessage->message }}</dd>
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            @can('update', $contactMessage)
                <x-admin.card>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Update status') }}</h3>
                    <form method="POST" action="{{ route('admin.public-contact-messages.update-status', $contactMessage) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="erp-input w-full text-sm">
                            @foreach (App\Enums\PublicContactMessageStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected($contactMessage->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="erp-btn-primary w-full">{{ __('Save status') }}</button>
                    </form>
                </x-admin.card>

                <x-admin.card>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Internal notes') }}</h3>
                    <form method="POST" action="{{ route('admin.public-contact-messages.update-notes', $contactMessage) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <textarea name="admin_notes" rows="5" class="erp-input w-full text-sm">{{ old('admin_notes', $contactMessage->admin_notes) }}</textarea>
                        <button type="submit" class="erp-btn-secondary w-full">{{ __('Save notes') }}</button>
                    </form>
                </x-admin.card>
            @endcan
        </div>
    </div>
</x-admin-layout>
