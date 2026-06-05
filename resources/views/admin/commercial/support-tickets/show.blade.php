<x-admin-layout :title="$ticket->ticket_number" :breadcrumbs="[['label' => __('Support Tickets'), 'url' => route('admin.commercial.support-tickets.index')], ['label' => $ticket->ticket_number]]">
    <x-admin.page-header :title="$ticket->subject" :description="$ticket->ticket_number">
        <x-slot name="actions">
            @can('update', $ticket)
                <a href="{{ route('admin.commercial.support-tickets.edit', $ticket) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card class="mb-4 p-4">
        <div class="grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
            <div><span class="text-slate-500">{{ __('Status') }}</span><div class="font-medium">{{ $ticket->status->label() }}</div></div>
            <div><span class="text-slate-500">{{ __('Priority') }}</span><div>{{ $ticket->priority->label() }}</div></div>
            <div><span class="text-slate-500">{{ __('Channel') }}</span><div>{{ $ticket->channel->label() }}</div></div>
            <div><span class="text-slate-500">{{ __('Due') }}</span><div>{{ $ticket->due_at?->format('d M Y H:i') ?? '—' }} @if($ticket->isOverdue()) <x-admin.status-badge variant="danger">{{ __('Overdue') }}</x-admin.status-badge>@endif</div></div>
        </div>
        <p class="mt-4 whitespace-pre-wrap text-sm text-slate-700">{{ $ticket->description }}</p>
    </x-admin.card>

    @can('assign', $ticket)
        <x-admin.card class="mb-4 p-4">
            <form method="POST" action="{{ route('admin.commercial.support-tickets.assign', $ticket) }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <select name="assigned_to" class="erp-input" required>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected($ticket->assigned_to == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="erp-btn-secondary">{{ __('Assign') }}</button>
            </form>
        </x-admin.card>
    @endcan

    <x-admin.card class="mb-4">
        <div class="border-b border-erp-border px-4 py-3 font-semibold">{{ __('Comments') }}</div>
        <ul class="divide-y divide-erp-border">
            @forelse ($ticket->comments as $comment)
                <li class="px-4 py-3 text-sm">
                    <div class="font-medium">{{ $comment->user?->name }} <span class="text-slate-400">· {{ $comment->visibility->label() }}</span></div>
                    <p class="mt-1 text-slate-700">{{ $comment->comment }}</p>
                </li>
            @empty
                <li class="px-4 py-6 text-center text-slate-500">{{ __('No comments yet.') }}</li>
            @endforelse
        </ul>
        @can('update', $ticket)
            <form method="POST" action="{{ route('admin.commercial.support-tickets.comment', $ticket) }}" class="border-t border-erp-border p-4 space-y-3">
                @csrf
                <textarea name="comment" class="erp-input w-full" rows="3" required placeholder="{{ __('Add comment...') }}"></textarea>
                <select name="visibility" class="erp-input w-full max-w-xs">
                    @foreach (App\Enums\CommercialTicketCommentVisibility::cases() as $visibility)
                        <option value="{{ $visibility->value }}">{{ $visibility->label() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="erp-btn-secondary">{{ __('Add comment') }}</button>
            </form>
        @endcan
    </x-admin.card>

    @can('resolve', $ticket)
        <div class="flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.commercial.support-tickets.resolve', $ticket) }}">@csrf<button class="erp-btn-primary">{{ __('Resolve') }}</button></form>
            <form method="POST" action="{{ route('admin.commercial.support-tickets.close', $ticket) }}">@csrf<button class="erp-btn-secondary">{{ __('Close') }}</button></form>
            <form method="POST" action="{{ route('admin.commercial.support-tickets.reopen', $ticket) }}">@csrf<button class="erp-btn-secondary">{{ __('Reopen') }}</button></form>
        </div>
    @endcan
</x-admin-layout>
