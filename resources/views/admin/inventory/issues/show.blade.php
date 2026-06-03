<x-admin-layout :title="$issue->issue_number">
    <x-admin.page-header :title="$issue->issue_number">
        <span class="erp-badge">{{ $issue->status->value }}</span>
        @can('post', $issue)
            <form method="POST" action="{{ route('admin.inventory.issues.post', $issue) }}">@csrf
                <button class="erp-btn-primary">{{ __('Post issue') }}</button></form>
        @endcan
    </x-admin.page-header>
    <x-admin.card>
        @foreach ($issue->items as $line)
            <div class="text-sm py-1">{{ $line->inventoryItem?->item_name }}: {{ $line->quantity }}</div>
        @endforeach
    </x-admin.card>
</x-admin-layout>
