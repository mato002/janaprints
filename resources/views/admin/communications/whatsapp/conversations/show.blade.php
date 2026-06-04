<x-admin-layout :title="$conversation->conversation_code" :breadcrumbs="[['label' => __('WhatsApp'), 'url' => route('admin.communications.whatsapp.inbox')], ['label' => $conversation->conversation_code]]">
    @include('admin.communications.whatsapp.partials.nav')

    <x-admin.page-header :title="$conversation->customer?->name ?? $conversation->phone_number" :description="$conversation->conversation_code">
        <x-slot:actions>
            <span class="rounded px-2 py-1 text-xs font-semibold uppercase {{ $conversation->status->badgeClass() }}">{{ $conversation->status->label() }}</span>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 erp-card flex flex-col min-h-[24rem]">
            <div class="flex-1 space-y-3 overflow-y-auto max-h-[32rem] p-4">
                @foreach ($conversation->messages as $msg)
                    <div class="flex {{ $msg->direction->value === 'outgoing' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-lg px-3 py-2 text-sm {{ $msg->direction->value === 'outgoing' ? 'bg-erp-accent text-white' : 'bg-slate-100 text-slate-800' }}">
                            <p class="whitespace-pre-wrap">{{ $msg->body }}</p>
                            <p class="mt-1 text-[10px] opacity-75">
                                {{ $msg->message_type->label() }} · {{ $msg->status->label() }} · {{ $msg->created_at->format('H:i') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
            @can('send', App\Models\Communications\WhatsappConversation::class)
                <form method="POST" action="{{ route('admin.communications.whatsapp.conversations.messages.store', $conversation) }}" class="border-t p-3 space-y-2">
                    @csrf
                    <textarea name="body" rows="2" class="erp-input w-full" placeholder="{{ __('Type a message…') }}"></textarea>
                    @if ($templates->isNotEmpty())
                        <select name="whatsapp_template_id" class="erp-input w-full text-sm">
                            <option value="">{{ __('Or send COM-1 template…') }}</option>
                            @foreach ($templates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->communicationTemplate->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button type="submit" class="erp-btn erp-btn--primary erp-btn--sm">{{ __('Send') }}</button>
                </form>
            @endcan
        </div>
        <div class="space-y-4">
            <div class="erp-card text-sm space-y-2">
                <h3 class="erp-card-title">{{ __('Details') }}</h3>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Phone') }}</span><span>{{ $conversation->phone_number }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Account') }}</span><span>{{ $conversation->account?->name }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Assignee') }}</span><span>{{ $conversation->assignee?->name ?? '—' }}</span></div>
                @if ($conversation->tags)
                    <div class="flex flex-wrap gap-1 pt-1">
                        @foreach ($conversation->tags as $tag)
                            <span class="rounded bg-slate-100 px-2 py-0.5 text-xs">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            @can('viewAny', App\Models\Communications\CommunicationLog::class)
                @php
                    $waLogs = app(\App\Support\Communications\CommunicationLogService::class)
                        ->forEntity('customer', $conversation->customer_id ?? 0, $conversation->company_id, 10, \App\Enums\CommunicationLogChannel::WhatsApp);
                @endphp
                @if ($conversation->customer_id)
                    <div class="erp-card">
                        <h3 class="erp-card-title">{{ __('COM-4 WhatsApp log') }}</h3>
                        <x-admin.communication-timeline :logs="$waLogs" :compact="true" />
                    </div>
                @endif
            @endcan
        </div>
    </div>
</x-admin-layout>
