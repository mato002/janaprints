<x-admin-layout :title="$item->sku" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Items'), 'url' => route('admin.inventory.items.index')], ['label' => $item->sku]]">
    <x-admin.page-header :title="$item->item_name" :description="$item->sku">
        <x-slot name="actions">
            @if($item->stock_role)
                <span class="erp-badge {{ $item->stock_role->badgeClass() }}">{{ $item->stock_role->label() }}</span>
            @endif
            <span class="erp-badge">{{ __('Stock') }}: {{ number_format($stockBalance, 3) }}</span>
            @can('update', $item)<a href="{{ route('admin.inventory.items.edit', $item) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>@endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <x-admin.card class="xl:col-span-2">
            <dl class="text-sm space-y-2">
                <div><dt class="text-slate-500">{{ __('Stock role') }}</dt><dd>{{ $item->stock_role?->label() ?? __('-') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Category') }}</dt><dd>{{ $item->category?->name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Subcategory') }}</dt><dd>{{ $item->subcategory?->name ?? __('-') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Brand') }}</dt><dd>{{ $item->brand?->name ?? __('-') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Unit') }}</dt><dd>{{ $item->unitOfMeasure?->name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Item code') }}</dt><dd>{{ $item->item_code ?? __('-') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Reorder level') }}</dt><dd>{{ $item->reorder_level }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Standard cost') }}</dt><dd>{{ number_format($item->standard_cost, 2) }}</dd></div>
            </dl>

            @if($item->attributeValues->isNotEmpty())
                <h2 class="mt-6 text-sm font-semibold text-slate-900">{{ __('Attributes') }}</h2>
                <dl class="mt-2 grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                    @foreach($item->attributeValues as $value)
                        <div><dt class="text-slate-500">{{ $value->attribute?->name }}</dt><dd>{{ $value->option?->label ?? $value->value }}</dd></div>
                    @endforeach
                </dl>
            @endif

            @if($item->priceListItems->isNotEmpty())
                <h2 class="mt-6 text-sm font-semibold text-slate-900">{{ __('Prices') }}</h2>
                <dl class="mt-2 grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                    @foreach($item->priceListItems as $price)
                        <div><dt class="text-slate-500">{{ $price->priceList?->name }}</dt><dd>{{ $price->priceList?->currency }} {{ number_format((float) $price->price_override, 2) }}</dd></div>
                    @endforeach
                </dl>
            @endif

            <p class="text-xs text-slate-500 mt-4">{{ __('Balance is calculated from inventory movements only.') }}</p>
        </x-admin.card>

        <x-admin.card>
            <h2 class="text-sm font-semibold text-slate-900">{{ __('Product Images') }}</h2>
            <form method="POST" enctype="multipart/form-data" action="{{ route('admin.inventory.items.images.store', $item) }}" class="mt-3 space-y-3">
                @csrf
                <input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp" class="erp-input w-full">
                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="primary" value="1"><span>{{ __('Make primary') }}</span></label>
                <button class="erp-btn-secondary">{{ __('Upload') }}</button>
            </form>
            <div class="mt-4 grid grid-cols-2 gap-3">
                @forelse($item->images as $image)
                    <div class="rounded-md border border-erp-border p-2">
                        <img src="{{ $image->thumbnailUrl() }}" alt="" class="aspect-square w-full rounded object-cover">
                        <div class="mt-2 flex flex-wrap gap-1">
                            @if(! $image->is_primary)
                                <form method="POST" action="{{ route('admin.inventory.items.images.primary', [$item, $image]) }}">@csrf @method('PATCH')<button class="erp-btn-ghost py-1 text-xs">{{ __('Primary') }}</button></form>
                            @else
                                <span class="erp-badge">{{ __('Primary') }}</span>
                            @endif
                            <form method="POST" action="{{ route('admin.inventory.items.images.destroy', [$item, $image]) }}" onsubmit="return confirm(@js(__('Remove this image?')))">@csrf @method('DELETE')<button class="erp-btn-ghost py-1 text-xs text-red-700">{{ __('Remove') }}</button></form>
                        </div>
                    </div>
                @empty
                    <x-admin.empty-state icon="image" :title="__('No images uploaded')" />
                @endforelse
            </div>
        </x-admin.card>
    </div>
</x-admin-layout>
