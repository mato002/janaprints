<x-admin-layout :title="__('WhatsApp Inbox')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('WhatsApp')]]">
    @include('admin.communications.whatsapp.partials.nav')

    <x-admin.page-header :title="__('WhatsApp inbox')" :description="__('Enterprise conversation center — provider-agnostic foundation.')" />

    <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-6">
        <x-admin.stat-card :label="__('Open conversations')" :value="$stats['open_conversations']" />
        <x-admin.stat-card :label="__('Unread')" :value="$stats['unread_total']" />
        <x-admin.stat-card :label="__('Queued')" :value="$stats['queued_messages']" />
        <x-admin.stat-card :label="__('Delivery rate')" :value="$stats['delivery_rate'].'%'" />
        <x-admin.stat-card :label="__('Sent today')" :value="$stats['sent_today']" />
        <x-admin.stat-card :label="__('Failed')" :value="$stats['failed_messages']" />
    </div>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Search phone, preview, code…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (\App\Enums\WhatsappConversationStatus::cases() as $st)
                    <option value="{{ $st->value }}" @selected(($filters['status'] ?? '') === $st->value)>{{ $st->label() }}</option>
                @endforeach
            </select>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="unread_only" value="1" @checked(($filters['unread_only'] ?? '') === '1')>
                {{ __('Unread only') }}
            </label>
        </x-admin.index-toolbar>
    </x-admin.card>

    <div class="erp-card divide-y">
        @forelse ($conversations as $conversation)
            <a href="{{ route('admin.communications.whatsapp.conversations.show', $conversation) }}" data-turbo-frame="erp-main" class="flex items-start gap-4 px-4 py-3 hover:bg-slate-50 transition-colors">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">WA</div>
                <div class="min-w-0 flex-1">
                    <div class="flex justify-between gap-2">
                        <p class="font-semibold text-erp-primary truncate">
                            {{ $conversation->customer?->name ?? $conversation->participants->first()?->display_name ?? $conversation->phone_number }}
                        </p>
                        <span class="text-xs text-slate-400 shrink-0">{{ $conversation->last_activity_at?->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-slate-600 truncate">{{ $conversation->last_message_preview ?? __('No messages yet') }}</p>
                    <p class="text-xs text-slate-400">{{ $conversation->phone_number }} · {{ $conversation->conversation_code }}</p>
                </div>
                @if ($conversation->unread_count > 0)
                    <span class="rounded-full bg-erp-accent px-2 py-0.5 text-xs font-semibold text-white">{{ $conversation->unread_count }}</span>
                @endif
                <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $conversation->status->badgeClass() }}">{{ $conversation->status->label() }}</span>
            </a>
        @empty
            <p class="py-8 text-center text-sm text-slate-500">{{ __('No conversations yet. Start by messaging a customer from Customer 360 or send a template.') }}</p>
        @endforelse
        @if ($conversations->hasPages())<div class="p-3">{{ $conversations->links() }}</div>@endif
    </div>
</x-admin-layout>
