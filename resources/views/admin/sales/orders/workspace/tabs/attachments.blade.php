<article class="so-360__card">
    <h2 class="so-360__card-title">{{ __('Attachments') }}</h2>

    @forelse ($salesOrder->attachments as $attachment)
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 py-2 text-sm last:border-0">
            <span>{{ $attachment->original_name }}</span>
            @if ($attachment->uploader?->name)
                <span class="text-xs text-slate-400">{{ $attachment->uploader->name }}</span>
            @endif
        </div>
    @empty
        <p class="text-sm text-slate-500">{{ __('No attachments yet.') }}</p>
    @endforelse

    @can('view', $salesOrder)
        <form method="POST" action="{{ route('admin.sales-orders.attachments.store', $salesOrder) }}" enctype="multipart/form-data" data-turbo-frame="erp-main" class="mt-4 space-y-2">
            @csrf
            <input type="file" name="file" class="erp-input w-full" required>
            <button class="erp-btn-secondary">{{ __('Upload') }}</button>
        </form>
    @endcan
</article>
