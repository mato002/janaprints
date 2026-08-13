@php
    use App\Enums\InventoryStockRole;

    $item = $item ?? null;
    $jobCard = $jobCard ?? null;
    $buttonClass = $buttonClass ?? 'erp-btn-primary text-sm';
@endphp

@if ($item && ($item->stock_role ?? null) !== InventoryStockRole::FinishedGood)
    @can('classify', $item)
        <form method="POST" action="{{ route('admin.inventory.items.classify-finished-good', $item) }}" class="inline" onsubmit="return confirm(@js(__('Set :item as a finished good so production can post output against it?', ['item' => $item->item_name])))">
            @csrf
            @if ($jobCard)
                <input type="hidden" name="production_job_card_id" value="{{ $jobCard->id }}">
            @endif
            <button type="submit" class="{{ $buttonClass }}">{{ __('Set as finished good') }}</button>
        </form>
    @endcan
@endif
