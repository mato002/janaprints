<div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-600" id="inbox-assign">
    <span><strong>{{ __('Owner') }}:</strong> {{ $active->owner?->name ?? __('—') }}</span>
    <span><strong>{{ __('Assigned') }}:</strong> {{ $active->assignee?->name ?? __('Unassigned') }}</span>
    @if ($active->assignedDepartment)<span><strong>{{ __('Team') }}:</strong> {{ $active->assignedDepartment->name }}</span>@endif
    @if ($watchers->isNotEmpty())
        <span><strong>{{ __('Watchers') }}:</strong> {{ $watchers->pluck('name')->join(', ') }}</span>
    @endif
</div>
@can('assign', App\Models\Communications\Inbox\CommunicationConversation::class)
    <div class="mt-2 flex flex-wrap gap-2">
        <form method="POST" action="{{ route('admin.communications.inbox.assign', $active) }}" class="flex flex-wrap gap-1 items-center" data-turbo-frame="{{ $inboxTurboFrame }}">
            @csrf
            <input type="hidden" name="action" value="assign">
            <select name="assigned_user_id" class="erp-input text-xs">
                <option value="">{{ __('Assign user…') }}</option>
                @foreach ($staff as $user)
                    <option value="{{ $user->id }}" @selected($active->assigned_user_id == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs">{{ __('Assign') }}</button>
        </form>
        <form method="POST" action="{{ route('admin.communications.inbox.assign', $active) }}" class="inline" data-turbo-frame="{{ $inboxTurboFrame }}">@csrf<input type="hidden" name="action" value="take"><button class="erp-btn erp-btn--secondary erp-btn--xs">{{ __('Take') }}</button></form>
        <form method="POST" action="{{ route('admin.communications.inbox.assign', $active) }}" class="inline" data-turbo-frame="{{ $inboxTurboFrame }}">@csrf<input type="hidden" name="action" value="release"><button class="erp-btn erp-btn--secondary erp-btn--xs">{{ __('Release') }}</button></form>
        @can('escalate', App\Models\Communications\Inbox\CommunicationConversation::class)
            <form method="POST" action="{{ route('admin.communications.inbox.assign', $active) }}" class="inline" id="inbox-escalate" data-turbo-frame="{{ $inboxTurboFrame }}">@csrf<input type="hidden" name="action" value="escalate"><button class="erp-btn erp-btn--secondary erp-btn--xs">{{ __('Escalate') }}</button></form>
        @endcan
        <form method="POST" action="{{ route('admin.communications.inbox.assign', $active) }}" class="flex gap-1" data-turbo-frame="{{ $inboxTurboFrame }}">
            @csrf<input type="hidden" name="action" value="assign_department">
            <select name="assigned_department_id" class="erp-input text-xs">
                <option value="">{{ __('Department…') }}</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" @selected($active->assigned_department_id == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs">{{ __('Set') }}</button>
        </form>
        <form method="POST" action="{{ route('admin.communications.inbox.status', $active) }}" class="flex gap-1" data-turbo-frame="{{ $inboxTurboFrame }}">
            @csrf
            <select name="status" class="erp-input text-xs" onchange="this.form.submit()">
                @foreach (\App\Enums\InboxConversationStatus::cases() as $st)
                    <option value="{{ $st->value }}" @selected($active->status === $st)>{{ $st->label() }}</option>
                @endforeach
            </select>
        </form>
    </div>
@endcan
