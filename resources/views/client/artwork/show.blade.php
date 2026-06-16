<x-layouts.client :title="$artwork->request_number" :heading="$artwork->title" :subtitle="$artwork->request_number">
    <div class="client-detail">
        <div class="client-detail__meta">
            <p><strong>{{ __('Status') }}:</strong> @include('client.partials.status-badge', ['status' => $artwork->status])</p>
            <p><strong>{{ __('Due date') }}:</strong> {{ $artwork->due_date?->format('F j, Y') ?: '—' }}</p>
            @if ($artwork->description)
                <p><strong>{{ __('Brief') }}:</strong> {{ $artwork->description }}</p>
            @endif
        </div>

        @if ($canReview)
            <div class="client-review-box">
                <h3 class="client-panel__title">{{ __('Review artwork') }}</h3>
                <form method="POST" action="{{ route('client.artwork.review', $artwork) }}" class="client-review-form">
                    @csrf
                    <fieldset class="client-radio-group">
                        <label><input type="radio" name="decision" value="approved" required> {{ __('Approve') }}</label>
                        <label><input type="radio" name="decision" value="revision_requested"> {{ __('Request revisions') }}</label>
                        <label><input type="radio" name="decision" value="rejected"> {{ __('Reject') }}</label>
                    </fieldset>
                    <label for="comments" class="client-label">{{ __('Comments') }}</label>
                    <textarea id="comments" name="comments" rows="4" class="client-input">{{ old('comments') }}</textarea>
                    <button type="submit" class="client-btn">{{ __('Submit feedback') }}</button>
                </form>
            </div>
        @endif
    </div>
</x-layouts.client>
