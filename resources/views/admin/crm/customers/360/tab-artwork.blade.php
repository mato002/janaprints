@php
    $artworks = $customer->artworks()->with('uploader:id,name')->orderByDesc('uploaded_at')->get();
    $serialProfiles = $customer->productSerialProfiles()->with('inventoryItem:id,item_name,sku')->get();
    $catalogueItems = \App\Models\Inventory\InventoryItem::query()
        ->forTenant()
        ->where('uses_serial_numbers', true)
        ->where('is_active', true)
        ->orderBy('item_name')
        ->get(['id', 'item_name', 'sku']);
    $finishedItems = \App\Models\Inventory\InventoryItem::query()
        ->forTenant()
        ->where('is_active', true)
        ->orderBy('item_name')
        ->get(['id', 'item_name', 'sku']);
@endphp

<div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
    <x-admin.artwork-preview-lightbox>
        <x-admin.card>
            <h3 class="mb-4 text-sm font-semibold text-slate-900">{{ __('Artwork Library') }}</h3>

            @can('update', $customer)
                <form method="POST" action="{{ route('admin.crm.customers.artworks.store', $customer) }}" enctype="multipart/form-data" class="mb-6 space-y-3 rounded-lg border border-erp-border p-4">
                    @csrf
                    <div><label class="erp-label">{{ __('Artwork name') }}</label><input name="artwork_name" class="erp-input w-full" required placeholder="{{ __('e.g. Receipt Book Layout') }}"></div>
                    <div>
                        <label class="erp-label">{{ __('Type') }}</label>
                        <select name="artwork_type" class="erp-input w-full" required>
                            @foreach (\App\Enums\CustomerArtworkType::cases() as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="erp-label">{{ __('File') }}</label><input type="file" name="file" class="erp-input w-full" accept=".jpg,.jpeg,.png,.webp,.pdf" required></div>
                    <button type="submit" class="erp-btn-primary">{{ __('Upload new version') }}</button>
                    <p class="text-xs text-slate-500">{{ __('Versions are never overwritten. Each upload creates a new version.') }}</p>
                </form>
            @endcan

            @if ($artworks->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="erp-table w-full text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Version') }}</th>
                                <th>{{ __('Uploaded') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($artworks as $artwork)
                                <tr>
                                    <td>{{ $artwork->artwork_name }}</td>
                                    <td>{{ $artwork->artwork_type->label() }}</td>
                                    <td>
                                        {{ $artwork->versionLabel() }}
                                        @if ($artwork->is_active_version)<span class="erp-badge">{{ __('Active') }}</span>@endif
                                    </td>
                                    <td>{{ $artwork->uploaded_at?->format('Y-m-d') }}</td>
                                    <td>{{ $artwork->status->label() }}</td>
                                    <td>
                                        @if ($artwork->isPreviewable() && $artwork->previewUrl())
                                            <button
                                                type="button"
                                                class="erp-btn-ghost text-xs"
                                                data-preview-url="{{ $artwork->previewUrl() }}"
                                                data-preview-title="{{ $artwork->artwork_name }}"
                                                data-preview-pdf="{{ $artwork->mime_type === 'application/pdf' ? '1' : '0' }}"
                                                @click="show($el.dataset.previewUrl, $el.dataset.previewTitle, $el.dataset.previewPdf === '1')"
                                            >{{ __('Preview') }}</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-admin.empty-state :title="__('No artwork uploaded')" />
            @endif
        </x-admin.card>
    </x-admin.artwork-preview-lightbox>

    <x-admin.card>
        <h3 class="mb-4 text-sm font-semibold text-slate-900">{{ __('Customer Serial Numbering Profiles') }}</h3>
        <p class="mb-4 text-sm text-slate-600">{{ __('Overrides product default serial format for this customer.') }}</p>

        @can('update', $customer)
            <form method="POST" action="{{ route('admin.crm.customers.serial-profiles.store', $customer) }}" class="mb-6 space-y-3 rounded-lg border border-erp-border p-4">
                @csrf
                <div>
                    <label class="erp-label">{{ __('Product') }}</label>
                    <select name="inventory_item_id" class="erp-input w-full" required>
                        <option value="">{{ __('Select product') }}</option>
                        @foreach ($catalogueItems as $item)
                            <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="erp-label">{{ __('Prefix') }}</label><input name="serial_prefix" class="erp-input w-full" placeholder="FL-" required></div>
                <div><label class="erp-label">{{ __('Padding length') }}</label><input type="number" name="serial_padding_length" class="erp-input w-full" value="6" min="1" max="12" required></div>
                <button type="submit" class="erp-btn-secondary">{{ __('Save profile') }}</button>
            </form>
        @endcan

        @if ($serialProfiles->isNotEmpty())
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Product') }}</th><th>{{ __('Format') }}</th><th></th></tr></thead>
                <tbody>
                    @foreach ($serialProfiles as $profile)
                        <tr>
                            <td>{{ $profile->inventoryItem?->item_name }}</td>
                            <td><code>{{ $profile->serial_prefix }}{{ str_repeat('0', max(0, $profile->serial_padding_length - 1)) }}1</code></td>
                            <td>
                                @can('update', $customer)
                                    <form method="POST" action="{{ route('admin.crm.customers.serial-profiles.destroy', [$customer, $profile]) }}" onsubmit="return confirm(@js(__('Remove this profile?')))">
                                        @csrf @method('DELETE')
                                        <button class="erp-btn-ghost text-xs text-red-700">{{ __('Remove') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-sm text-slate-500">{{ __('No customer-specific serial profiles.') }}</p>
        @endif
    </x-admin.card>
</div>
