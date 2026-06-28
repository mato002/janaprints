@include('admin.production.specifications.partials.read-only-display', ['specification' => $tabData['specification'] ?? ['has_specification' => false]])

@if (! empty($tabData['edit_url']))
    <div class="mt-4">
        <a href="{{ $tabData['edit_url'] }}" class="erp-btn-secondary text-sm">{{ __('Edit specification') }}</a>
    </div>
@endif
