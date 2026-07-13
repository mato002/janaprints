<x-admin-layout
    :title="$priceBook->name"
    :breadcrumbs="[
        ['label' => __('Commercial')],
        ['label' => __('Sales')],
        ['label' => __('Price Books'), 'url' => route('admin.commercial.price-books.index')],
        ['label' => $priceBook->name],
    ]"
>
    <div class="price-book-show w-full min-w-0 space-y-3">
        <div class="price-book-show__toolbar">
            <a
                href="{{ route('admin.commercial.price-books.index') }}"
                class="price-book-show__back"
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ __('Back to Price Books') }}
            </a>

            @can('update', $priceBook)
                <a href="{{ route('admin.commercial.price-books.edit', $priceBook) }}" class="erp-btn-secondary erp-btn--sm">{{ __('Edit') }}</a>
            @endcan
        </div>

        <section class="price-book-show__hero">
            <div class="price-book-show__hero-main">
                <div class="price-book-show__hero-top">
                    <h1 class="price-book-show__title">{{ $priceBook->name }}</h1>
                    <x-admin.status-badge :variant="$priceBook->status === App\Enums\CommercialPriceBookStatus::Active ? 'success' : 'neutral'">
                        {{ $priceBook->status->label() }}
                    </x-admin.status-badge>
                    @if ($priceBook->is_default)
                        <span class="price-book-show__chip">{{ __('Default') }}</span>
                    @endif
                </div>
                @if ($priceBook->description)
                    <p class="price-book-show__description">{{ $priceBook->description }}</p>
                @endif
            </div>

            <dl class="price-book-show__meta">
                <div class="price-book-show__meta-item">
                    <dt>{{ __('Code') }}</dt>
                    <dd class="font-mono">{{ $priceBook->code }}</dd>
                </div>
                <div class="price-book-show__meta-item">
                    <dt>{{ __('Branch') }}</dt>
                    <dd>{{ $priceBook->branch?->name ?? __('Company-wide') }}</dd>
                </div>
                <div class="price-book-show__meta-item">
                    <dt>{{ __('Items') }}</dt>
                    <dd>{{ $priceBook->items->count() }}</dd>
                </div>
                <div class="price-book-show__meta-item">
                    <dt>{{ __('Customers') }}</dt>
                    <dd>{{ $priceBook->customerAssignments->count() }}</dd>
                </div>
            </dl>
        </section>

        <div class="price-book-show__layout">
            <section class="price-book-show__panel">
                <header class="price-book-show__panel-head">
                    <h2 class="price-book-show__panel-title">{{ __('Price book items') }}</h2>
                    <span class="price-book-show__panel-count">{{ $priceBook->items->count() }}</span>
                </header>

                @can('update', $priceBook)
                    <form
                        method="POST"
                        action="{{ route('admin.commercial.price-books.items.store', $priceBook) }}"
                        class="price-book-show__inline-form"
                    >
                        @csrf
                        <select name="inventory_item_id" class="erp-input price-book-show__input" required>
                            <option value="">{{ __('Inventory item') }}</option>
                            @foreach ($inventoryItems as $item)
                                <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->sku }})</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.01" name="unit_price" class="erp-input price-book-show__input price-book-show__input--price" placeholder="{{ __('Unit price') }}" required>
                        <input type="number" step="0.0001" name="minimum_quantity" class="erp-input price-book-show__input price-book-show__input--qty" placeholder="{{ __('Min qty') }}">
                        <button type="submit" class="erp-btn-primary erp-btn--sm shrink-0">{{ __('Add item') }}</button>
                    </form>
                @endcan

                <div class="price-book-show__table-wrap">
                    <table class="price-book-show__table erp-table w-full text-sm">
                        <colgroup>
                            <col class="price-book-show__col-item">
                            <col class="price-book-show__col-price">
                            <col class="price-book-show__col-qty">
                            <col class="price-book-show__col-status">
                            @can('update', $priceBook)
                                <col class="price-book-show__col-actions">
                            @endcan
                        </colgroup>
                        <thead>
                            <tr>
                                <th scope="col">{{ __('Item') }}</th>
                                <th scope="col" class="price-book-show__col--numeric">{{ __('Unit price') }}</th>
                                <th scope="col" class="price-book-show__col--numeric">{{ __('Min qty') }}</th>
                                <th scope="col">{{ __('Status') }}</th>
                                @can('update', $priceBook)
                                    <th scope="col" class="erp-table-actions-col"></th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($priceBook->items as $item)
                                <tr>
                                    <td class="font-medium">{{ $item->inventoryItem?->item_name ?? $item->service_code ?? $item->description }}</td>
                                    <td class="price-book-show__col--numeric tabular-nums">{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="price-book-show__col--numeric tabular-nums">{{ $item->minimum_quantity ?? '—' }}</td>
                                    <td>
                                        <x-admin.status-badge :variant="$item->status === App\Enums\CommercialPriceBookStatus::Active ? 'success' : 'neutral'">
                                            {{ $item->status->label() }}
                                        </x-admin.status-badge>
                                    </td>
                                    @can('update', $priceBook)
                                        <td class="erp-table-actions-col">
                                            <form method="POST" action="{{ route('admin.commercial.price-books.items.destroy', [$priceBook, $item]) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="price-book-show__remove">{{ __('Remove') }}</button>
                                            </form>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()?->can('update', $priceBook) ? 5 : 4 }}" class="price-book-show__empty">
                                        {{ __('No items yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <aside class="price-book-show__aside">
                <section class="price-book-show__panel">
                    <header class="price-book-show__panel-head">
                        <h2 class="price-book-show__panel-title">{{ __('Customer assignments') }}</h2>
                        <span class="price-book-show__panel-count">{{ $priceBook->customerAssignments->count() }}</span>
                    </header>

                    @can('update', $priceBook)
                        <form
                            method="POST"
                            action="{{ route('admin.commercial.price-books.assign-customer', $priceBook) }}"
                            class="price-book-show__assign-form"
                        >
                            @csrf
                            <select name="customer_id" class="erp-input price-book-show__input min-w-0 flex-1" required>
                                <option value="">{{ __('Select customer') }}</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->company_name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="erp-btn-secondary erp-btn--sm shrink-0" @disabled($customers->isEmpty())>{{ __('Assign') }}</button>
                        </form>
                    @endcan

                    <ul class="price-book-show__assignments" role="list">
                        @forelse ($priceBook->customerAssignments as $assignment)
                            <li class="price-book-show__assignment">
                                <div class="price-book-show__assignment-copy">
                                    <span class="price-book-show__assignment-name">{{ $assignment->customer?->company_name }}</span>
                                    <span class="price-book-show__assignment-status">{{ $assignment->status->label() }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="price-book-show__empty">{{ __('No customer assignments.') }}</li>
                        @endforelse
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</x-admin-layout>
