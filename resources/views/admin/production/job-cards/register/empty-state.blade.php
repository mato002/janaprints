<x-admin.empty-state
    icon="collection"
    :title="__('No active production jobs found.')"
    :description="__('Create a job card from a confirmed sales order or review orders ready for production.')"
    data-export-skip
>
    <x-slot name="action">
        <div class="flex flex-wrap items-center justify-center gap-3">
            @if ($canCreate && $createUrl)
                <a href="{{ $createUrl }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Create Job Card') }}</a>
            @endif
            @if ($salesOrdersUrl)
                <a href="{{ $salesOrdersUrl }}" class="erp-btn-secondary" data-turbo-frame="erp-main">{{ __('View Sales Orders') }}</a>
            @endif
        </div>
    </x-slot>
</x-admin.empty-state>
