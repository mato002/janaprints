@php
    use App\Enums\InventoryStockRole;

    $unitLabel = $item->unitOfMeasure?->code ?: ($item->unitOfMeasure?->name ?? '');
    $stockFormatted = number_format((float) $stockBalance, 3);
    $reorderFormatted = number_format((float) $item->reorder_level, 3);
    $costFormatted = number_format((float) $item->standard_cost, 2);
    $stockValue = round((float) $stockBalance * (float) $item->standard_cost, 2);
    $categoryPath = collect([$item->category?->name, $item->subcategory?->name])->filter()->implode(' / ');
    $brandLabel = $item->brand_name ?? $item->brand?->name;
    $needsFinishedGood = ($item->stock_role?->value ?? null) !== InventoryStockRole::FinishedGood->value
        && request('needed_role') === InventoryStockRole::FinishedGood->value;
    $canClassify = auth()->user()?->can('classify', $item) ?? false;
    $canUpdate = auth()->user()?->can('update', $item) ?? false;
@endphp

<x-admin-layout
    :title="$item->sku"
    :breadcrumbs="[
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')],
        ['label' => __('Products'), 'url' => route('admin.inventory.items.index')],
        ['label' => $item->sku],
    ]"
>
    <div class="inv-product">
        <header class="inv-product__header">
            <div class="inv-product__identity">
                <p class="inv-product__eyebrow">{{ __('Product') }}</p>
                <div class="inv-product__title-row">
                    <div class="min-w-0">
                        <h1 class="inv-product__title">{{ $item->item_name }}</h1>
                        <p class="inv-product__sku">{{ $item->sku }}</p>
                        @if ($categoryPath !== '')
                            <p class="inv-product__path">{{ $categoryPath }}</p>
                        @endif
                    </div>
                    <div class="inv-product__summary">
                        @if ($item->stock_role)
                            <span class="erp-badge {{ $item->stock_role->badgeClass() }}">{{ $item->stock_role->label() }}</span>
                        @endif
                        <p class="inv-product__stock">
                            <span class="inv-product__stock-label">{{ __('Stock') }}</span>
                            <span class="inv-product__stock-value tabular-nums">{{ $stockFormatted }}{{ $unitLabel ? ' '.$unitLabel : '' }}</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="inv-product__actions">
                @if ($canUpdate)
                    <a href="{{ route('admin.inventory.items.edit', $item) }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Edit product') }}</a>
                @endif
            </div>
        </header>

        @if ($needsFinishedGood)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                <p class="font-medium">{{ __('Production needs this product classified as a finished good before output can be posted.') }}</p>
                <div class="mt-2">
                    @include('admin.inventory.items.partials.set-finished-good-form', ['item' => $item, 'buttonClass' => 'erp-btn-primary text-sm'])
                    @cannot('classify', $item)
                        <p class="mt-1 text-amber-800">{{ __('Ask a storekeeper to set stock role to Finished good. Sales catalogue access cannot change this.') }}</p>
                    @endcannot
                </div>
            </div>
        @endif

        <div class="inv-product__workspace">
            <x-admin.card class="inv-product__details">
                <h2 class="inv-product__section-title">{{ __('Product details') }}</h2>
                <dl class="inv-product__attr-grid">
                    <div>
                        <dt>{{ __('Stock role') }}</dt>
                        <dd class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span>{{ $item->stock_role?->label() ?? __('—') }}</span>
                            @if ($canUpdate)
                                <a href="{{ route('admin.inventory.items.edit', $item) }}" class="text-xs font-medium text-erp-primary hover:underline" data-erp-modal-open>{{ __('Change') }}</a>
                            @endif
                            @if (! $needsFinishedGood && $canClassify && ($item->stock_role?->value ?? null) !== InventoryStockRole::FinishedGood->value)
                                @include('admin.inventory.items.partials.set-finished-good-form', [
                                    'item' => $item,
                                    'buttonClass' => 'text-xs font-medium text-slate-500 hover:text-erp-primary hover:underline',
                                ])
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt>{{ __('Category') }}</dt>
                        <dd>{{ $item->category?->name ?? __('—') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Subcategory') }}</dt>
                        <dd>{{ $item->subcategory?->name ?? __('—') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Brand') }}</dt>
                        <dd>{{ $brandLabel ?: __('—') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Unit') }}</dt>
                        <dd>{{ $item->unitOfMeasure?->name ?? __('—') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Reorder level') }}</dt>
                        <dd class="tabular-nums">{{ $reorderFormatted }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Standard cost') }}</dt>
                        <dd class="tabular-nums">{{ $costFormatted }}</dd>
                    </div>
                </dl>

                @if ($item->attributeValues->isNotEmpty())
                    <h3 class="inv-product__subsection-title">{{ __('Attributes') }}</h3>
                    <dl class="inv-product__attr-grid">
                        @foreach ($item->attributeValues as $value)
                            <div>
                                <dt>{{ $value->attribute?->name }}</dt>
                                <dd>{{ $value->option?->label ?? $value->value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if ($item->priceListItems->isNotEmpty())
                    <h3 class="inv-product__subsection-title">{{ __('Prices') }}</h3>
                    <dl class="inv-product__attr-grid">
                        @foreach ($item->priceListItems as $price)
                            <div>
                                <dt>{{ $price->priceList?->name }}</dt>
                                <dd class="tabular-nums">{{ $price->priceList?->currency }} {{ number_format((float) $price->price_override, 2) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if ($item->uses_serial_numbers)
                    <h3 class="inv-product__subsection-title">{{ __('Serial format') }}</h3>
                    <p class="text-sm"><code>{{ $item->serial_prefix }}{{ str_repeat('0', max(0, ($item->serial_padding_length ?? 6) - 1)) }}1</code></p>
                @endif
            </x-admin.card>

            <x-admin.card class="inv-product__media">
                <h2 class="inv-product__section-title">{{ __('Product images') }}</h2>

                <form
                    method="POST"
                    enctype="multipart/form-data"
                    action="{{ route('admin.inventory.items.images.store', $item) }}"
                    class="inv-product__upload"
                >
                    @csrf
                    <label class="inv-product__dropzone">
                        <input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp" class="sr-only">
                        <span class="inv-product__dropzone-icon" aria-hidden="true">
                            <x-admin.icon name="image" class="h-8 w-8" />
                        </span>
                        <span class="inv-product__dropzone-title">{{ __('Upload images') }}</span>
                        <span class="inv-product__dropzone-hint">{{ __('JPG, PNG or WebP. First upload can be marked primary.') }}</span>
                    </label>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="primary" value="1" class="rounded border-slate-300">
                            <span>{{ __('Make primary') }}</span>
                        </label>
                        <button type="submit" class="erp-btn-secondary text-sm">{{ __('Upload images') }}</button>
                    </div>
                </form>

                <div class="inv-product__thumbs">
                    @forelse ($item->images as $image)
                        <div class="inv-product__thumb">
                            <img src="{{ $image->thumbnailUrl() }}" alt="" class="inv-product__thumb-img">
                            <div class="inv-product__thumb-actions">
                                @if (! $image->is_primary)
                                    <form method="POST" action="{{ route('admin.inventory.items.images.primary', [$item, $image]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="erp-btn-ghost py-1 text-xs">{{ __('Make primary') }}</button>
                                    </form>
                                @else
                                    <span class="erp-badge text-xs">{{ __('Primary') }}</span>
                                @endif
                                <form method="POST" action="{{ route('admin.inventory.items.images.destroy', [$item, $image]) }}" onsubmit="return confirm(@js(__('Remove this image?')))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="erp-btn-ghost py-1 text-xs text-red-700">{{ __('Remove') }}</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="inv-product__media-empty">{{ __('No images yet. Use the upload area above to add product photos.') }}</p>
                    @endforelse
                </div>
            </x-admin.card>
        </div>

        <x-admin.card class="inv-product__panel">
            <h2 class="inv-product__section-title">{{ __('Inventory') }}</h2>
            <dl class="inv-product__attr-grid inv-product__attr-grid--metrics">
                <div>
                    <dt>{{ __('Current stock') }}</dt>
                    <dd class="tabular-nums">{{ $stockFormatted }}{{ $unitLabel ? ' '.$unitLabel : '' }}</dd>
                </div>
                <div>
                    <dt>{{ __('Reorder level') }}</dt>
                    <dd class="tabular-nums">{{ $reorderFormatted }}</dd>
                </div>
                <div>
                    <dt>{{ __('Standard cost') }}</dt>
                    <dd class="tabular-nums">{{ $costFormatted }}</dd>
                </div>
                <div>
                    <dt>{{ __('Stock value') }}</dt>
                    <dd class="tabular-nums">{{ number_format($stockValue, 2) }}</dd>
                </div>
            </dl>
            <p class="mt-3 text-xs text-slate-500">{{ __('Balance is calculated from inventory movements only.') }}</p>
        </x-admin.card>

        <x-admin.card class="inv-product__panel">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <h2 class="inv-product__section-title mb-0">{{ __('Production') }}</h2>
                @if ($canUpdate)
                    <a href="{{ route('admin.inventory.items.edit', $item) }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Configure route') }}</a>
                @endif
            </div>
            <p class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Default production route') }}</p>
            @if ($item->productionRouteSteps->isNotEmpty())
                <ol class="mt-3 space-y-1.5 text-sm">
                    @foreach ($item->productionRouteSteps as $index => $step)
                        <li @class(['flex items-baseline gap-2', 'text-slate-400 line-through' => ! $step->is_active])>
                            <span class="tabular-nums text-xs text-slate-400">{{ $index + 1 }}.</span>
                            <span>{{ $step->step_name }}</span>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="mt-3 text-sm text-slate-600">{{ __('No production route configured') }}</p>
            @endif
        </x-admin.card>
    </div>
</x-admin-layout>
