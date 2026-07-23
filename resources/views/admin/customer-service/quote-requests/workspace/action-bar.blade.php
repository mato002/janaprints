@php
    $groups = $workspace['action_groups'] ?? [['key' => 'all', 'items' => $workspace['action_bar'] ?? []]];
@endphp

<x-admin.record-workspace.action-bar :groups="$groups">
    @can('update', $quoteRequest)
        <div class="rw-actions__group" data-group="danger">
            <form method="POST" action="{{ route('admin.public-quote-requests.update-status', $quoteRequest) }}" class="inline">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="spam">
                <button
                    type="submit"
                    class="rw-actions__btn rw-actions__btn--danger"
                    onclick="return confirm(@js(__('Reject this quote request?')))"
                >{{ __('Reject') }}</button>
            </form>
        </div>
    @endcan
</x-admin.record-workspace.action-bar>
