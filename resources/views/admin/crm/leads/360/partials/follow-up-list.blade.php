<ul class="crm-360__feed" role="list">
    @forelse ($items as $followUp)
        <li class="crm-360__feed-item">
            <div class="crm-360__feed-head">
                <span class="crm-360__feed-title">{{ $followUp['scheduled_at']?->format('d M Y H:i') }}</span>
                <span class="crm-360__pill">{{ str_replace('_', ' ', $followUp['status']) }}</span>
            </div>
            @if ($followUp['notes'])
                <p class="crm-360__feed-meta">{{ $followUp['notes'] }}</p>
            @endif
            @if ($followUp['assignee'])
                <p class="crm-360__feed-meta">{{ __('Assigned to') }} {{ $followUp['assignee'] }}</p>
            @endif
            @can('update', $lead)
                @if (($showComplete ?? true) && $followUp['status'] === 'pending')
                    <form method="POST" action="{{ route('admin.crm.leads.follow-ups.update', [$lead, $followUp['id']]) }}" class="mt-2">@csrf @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <x-admin.crm-btn type="submit" variant="outline" size="sm">{{ __('Mark complete') }}</x-admin.crm-btn>
                    </form>
                @endif
            @endcan
        </li>
    @empty
        <li class="crm-360__empty-inline">{{ $empty }}</li>
    @endforelse
</ul>
