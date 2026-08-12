<x-admin.card>
    <h2 class="mb-4 font-medium text-slate-900">{{ __('Quote activity') }}</h2>

    <div class="space-y-3">
        @forelse ($quotation->revisions as $revision)
            <div class="border-b border-erp-border pb-3 last:border-0 last:pb-0">
                <p class="text-sm font-medium text-slate-900">
                    {{ __('Rev :n', ['n' => $revision->revision_number]) }}
                </p>
                <p class="mt-0.5 text-xs text-slate-500">
                    {{ $revision->created_at?->format('M j, Y, g:i A') }}
                    @if ($revision->creator)
                        · {{ $revision->creator->name }}
                    @endif
                </p>
            </div>
        @empty
            <p class="text-sm text-slate-500">{{ __('No revision history yet.') }}</p>
        @endforelse
    </div>

    @if ($quotation->approved_at)
        <div class="mt-4 border-t border-erp-border pt-3">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Approved') }}</p>
            <p class="mt-1 text-sm text-slate-700">
                {{ $quotation->approved_at->format('M j, Y, g:i A') }}
                @if ($quotation->approver)
                    · {{ $quotation->approver->name }}
                @endif
            </p>
        </div>
    @endif
</x-admin.card>
