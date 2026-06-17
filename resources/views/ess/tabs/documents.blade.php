<section class="space-y-3">
    @forelse ($documents as $document)
        <article class="ess-card flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-semibold">{{ $document->title }}</p>
                <p class="text-sm text-erp-muted">{{ $document->category->label() }}</p>
                <p class="text-xs text-erp-muted">{{ $document->created_at?->format('d M Y') }}</p>
            </div>
            @can('ess.documents.download')
                <a href="{{ route('ess.documents.download', $document) }}" class="ess-btn ess-btn--primary w-full sm:w-auto">{{ __('Download') }}</a>
            @endcan
        </article>
    @empty
        <div class="ess-card text-sm text-erp-muted">{{ __('No documents available.') }}</div>
    @endforelse
</section>
