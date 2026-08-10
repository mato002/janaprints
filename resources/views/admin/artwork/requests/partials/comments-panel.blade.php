@php
    $fromDesk = ($fromDesk ?? false) || request('from') === 'designer-desk';
    $hasComments = $request->comments->isNotEmpty();
    $hasApprovals = $request->approvals->isNotEmpty();
@endphp

<div class="artwork-detail-card">
    <h2 class="artwork-detail-card__title">{{ __('Comments & approvals') }}</h2>

    @if ($hasComments)
        <div class="artwork-detail-comments__timeline">
            @foreach ($request->comments as $comment)
                <article class="artwork-detail-comment">
                    <div class="artwork-detail-comment__avatar" aria-hidden="true">
                        {{ strtoupper(substr($comment->user?->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="artwork-detail-comment__body">
                        <div class="artwork-detail-comment__meta">
                            <span class="artwork-detail-comment__author">{{ $comment->user?->name ?? __('Unknown') }}</span>
                            <span class="erp-badge">{{ str_replace('_', ' ', ucfirst($comment->comment_type->value)) }}</span>
                            @if ($comment->created_at)
                                <span class="artwork-detail-comment__time">{{ $comment->created_at->format('d M Y H:i') }}</span>
                            @endif
                        </div>
                        <p class="artwork-detail-comment__text">{{ $comment->comment }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    @foreach ($request->approvals as $approval)
        <div class="artwork-detail-approval">
            <strong>{{ str_replace('_', ' ', ucfirst($approval->decision->value)) }}</strong>
            — {{ $approval->approver?->name }}
            @if ($approval->comments)
                <span class="text-slate-500">({{ $approval->comments }})</span>
            @endif
        </div>
    @endforeach

    @can('view', $request)
        <form
            method="POST"
            action="{{ route('admin.artwork.comments.store', $request) }}"
            class="artwork-detail-comments__form @if(! $hasComments && ! $hasApprovals) !border-t-0 !pt-0 @endif"
        >
            @csrf
            @if ($fromDesk)
                <input type="hidden" name="from" value="designer-desk">
            @endif
            <div class="artwork-detail-comments__form-row">
                <div class="artwork-detail-comments__visibility">
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Visibility') }}</label>
                    <select name="comment_type" class="erp-input w-full text-sm">
                        <option value="internal">{{ __('Internal') }}</option>
                        <option value="customer">{{ __('Customer') }}</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Comment') }}</label>
                <textarea name="comment" class="erp-input min-h-[6rem] w-full text-sm" rows="3" required placeholder="{{ __('Notes for the team…') }}"></textarea>
            </div>
            <button type="submit" class="erp-btn-secondary text-sm">{{ __('Add comment') }}</button>
        </form>
    @endcan

    @if (! $hasComments && ! $hasApprovals && ! auth()->user()?->can('view', $request))
        <p class="text-sm text-slate-500">{{ __('No comments or approvals yet.') }}</p>
    @endif
</div>
