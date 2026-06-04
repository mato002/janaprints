<div class="erp-card overflow-x-auto">
    <table class="erp-table w-full">
        <thead>
            <tr>
                <th>{{ __('Subject') }}</th>
                <th>{{ __('To') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Sent') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($messages as $message)
                <tr>
                    <td>{{ Str::limit($message->subject, 40) }}</td>
                    <td class="text-xs">{{ collect($message->to_emails)->pluck('email')->join(', ') }}</td>
                    <td><span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $message->status->badgeClass() }}">{{ $message->status->label() }}</span></td>
                    <td>{{ $message->sent_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td>@can('audit', App\Models\Communications\EmailCampaign::class)<a href="{{ route('admin.communications.email.delivery.show', $message) }}" class="text-erp-accent text-sm" data-turbo-frame="erp-main">{{ __('Track') }}</a>@endcan</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No messages.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    @if ($messages->hasPages())<div class="mt-3">{{ $messages->links() }}</div>@endif
</div>
