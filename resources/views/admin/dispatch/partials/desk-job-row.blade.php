@php
    /** @var array<string, mixed> $row */
    $job = $row['job'];
    $workflow = $row['workflow'];
    $eligible = (bool) ($row['eligible_for_delivery_note'] ?? false);
@endphp
<tr>
    <td class="font-mono text-xs whitespace-nowrap">
        <a href="{{ \App\Support\Navigation\WorkspaceEmbed::mainUrl(route('admin.production.job-cards.show', ['jobCard' => $job, 'tab' => 'dispatch'])) }}" class="text-erp-accent hover:underline" data-turbo-frame="erp-main" data-turbo-action="advance">
            {{ $job->job_card_number }}
        </a>
    </td>
    <td>{{ $job->customer?->company_name ?? '—' }}</td>
    <td>
        {{ $job->inventoryItem?->item_name ?? '—' }}
        @if ($job->inventoryItem?->sku)
            <span class="block text-[11px] text-slate-500">{{ $job->inventoryItem->sku }}</span>
        @endif
    </td>
    <td class="whitespace-nowrap text-xs">
        {{ $job->required_date?->format('Y-m-d') ?? $job->salesOrder?->required_date?->format('Y-m-d') ?? '—' }}
    </td>
    <td>
        @php
            $variant = $workflow['status_variant'] ?? 'warning';
            $badgeClass = match ($variant) {
                'success' => 'bg-emerald-100 text-emerald-800',
                default => 'bg-amber-100 text-amber-900',
            };
        @endphp
        <span class="erp-badge {{ $badgeClass }}">{{ $workflow['status_label'] ?? __('Blocked') }}</span>
        @if (! $eligible && ! empty($workflow['blockers']))
            <p class="mt-1 max-w-xs text-[11px] leading-snug text-slate-500">{{ $workflow['blockers'][0] }}</p>
        @endif
    </td>
    <td class="erp-table-actions-col">
        @if ($eligible && ($canCreateNote ?? false))
            <form method="POST" action="{{ route('admin.dispatch.delivery-notes.store-from-job', $job) }}" class="inline">
                @csrf
                <button type="submit" class="erp-btn-primary text-xs py-1">{{ __('Create delivery note') }}</button>
            </form>
        @elseif (! empty($workflow['next_step']['url']))
            <a
                href="{{ \App\Support\Navigation\WorkspaceEmbed::mainUrl($workflow['next_step']['url']) }}"
                class="erp-btn-secondary text-xs py-1"
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
            >{{ $workflow['next_step']['label'] }}</a>
        @else
            <a href="{{ \App\Support\Navigation\WorkspaceEmbed::mainUrl(route('admin.production.job-cards.show', ['jobCard' => $job, 'tab' => 'dispatch'])) }}" class="text-sm text-erp-accent hover:underline" data-turbo-frame="erp-main" data-turbo-action="advance">{{ __('Open job') }}</a>
        @endif
    </td>
</tr>
