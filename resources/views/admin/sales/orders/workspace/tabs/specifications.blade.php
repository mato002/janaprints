<article class="so-360__card">
    <div class="so-360__card-head">
        <h2 class="so-360__card-title">{{ __('Specifications') }}</h2>
        <a
            href="{{ route('admin.sales-orders.specifications.print', $salesOrder) }}"
            class="erp-btn-secondary"
            target="_blank"
            rel="noopener"
            data-turbo="false"
        >{{ __('Print') }}</a>
    </div>

    <p class="mb-4 text-sm text-slate-600">{{ __('Print recipe for each line — size, paper, ink, and finishing. Sales prices stay on the Commercial tab.') }}</p>

    @forelse ($salesOrder->items as $item)
        @php
            $specEntry = $itemSpecifications[$item->id] ?? [];
            $specModel = $specEntry['model'] ?? null;
            $specDisplay = $specEntry['display'] ?? ['has_specification' => false];
        @endphp
        <section class="mb-6 border-b border-slate-100 pb-6 last:mb-0 last:border-0 last:pb-0">
            <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">{{ $item->item_name }}</h3>
                    <p class="text-xs text-slate-500">
                        {{ __('Qty') }}: {{ $item->quantity }}
                        @if ($item->description)
                            · {{ $item->description }}
                        @endif
                    </p>
                </div>
                @if ($specModel)
                    @can('update', $specModel)
                        <a href="{{ route('admin.sales-orders.items.specification.edit', [$salesOrder, $item, $specModel]) }}" class="erp-btn-secondary text-xs">
                            {{ __('Edit specification') }}
                        </a>
                    @endcan
                @else
                    @can('create', [App\Models\Production\ProductionSpecification::class, $salesOrder])
                        <a href="{{ route('admin.sales-orders.items.specification.create', [$salesOrder, $item]) }}" class="erp-btn-primary text-xs">
                            {{ __('Add specification') }}
                        </a>
                    @endcan
                @endif
            </div>

            @include('admin.production.specifications.partials.read-only-display', [
                'specification' => $specDisplay,
                'hideApprovalStatus' => true,
            ])
        </section>
    @empty
        <p class="text-sm text-slate-500">{{ __('No line items.') }}</p>
    @endforelse
</article>
