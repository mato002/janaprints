@php
    $stepLabels = [
        1 => __('Customer'),
        2 => __('Specification'),
        3 => __('Order'),
        4 => __('Release'),
    ];

    $createCustomerUrl = route('admin.crm.customers.create', ['from' => 'sales-desk']);
    $createSpecificationUrl = $customer
        ? route('admin.crm.customers.print-specifications.create', [$customer, 'from' => 'sales-desk'])
        : null;
    $createOrderUrl = $customer
        ? route('admin.sales-orders.create', array_filter([
            'from' => 'sales-desk',
            'tab' => 'direct',
            'customer_id' => $customer->id,
            'print_specification_id' => $specification?->id,
        ]))
        : null;
@endphp

<x-admin-layout
    :title="__('Sales Desk')"
    :breadcrumbs="$operatorMode
        ? [['label' => __('Sales Desk')]]
        : [
            ['label' => __('Commercial'), 'url' => $fullCommercialDeskUrl],
            ['label' => __('Sales Desk')],
        ]"
>
    <div
        class="sales-desk-shell min-w-0 max-w-full"
        x-data="salesDeskSearch(@js([
            'searchUrl' => $searchUrl,
            'deskUrl' => route('admin.sales.desk'),
        ]))"
    >
        <div class="mb-3 flex flex-col gap-2 rounded-lg border border-erp-accent/25 bg-erp-accent/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-erp-primary">{{ __('Sales desk') }}</p>
                <p class="text-xs text-slate-600">{{ __('Walk-in to production — forms open in modals. Stay on this desk.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @unless ($operatorMode)
                    <a href="{{ $fullCommercialDeskUrl }}" class="erp-btn-secondary text-xs" data-turbo-frame="_top">{{ __('Full Commercial desk') }}</a>
                @endunless
                <a href="{{ route('admin.sales.desk') }}" class="erp-btn-secondary text-xs" data-turbo-frame="_top">{{ __('Start another') }}</a>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if (! empty($specificationNotice))
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $specificationNotice }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <nav class="mb-4 flex flex-wrap gap-2" aria-label="{{ __('Walk-in steps') }}">
            @foreach ($stepLabels as $id => $label)
                @php
                    $enabled = $id === 1
                        || ($id === 2 && $customer)
                        || ($id === 3 && $customer && ($specification || count($printSpecifications)))
                        || ($id === 4 && $order);
                    $href = match (true) {
                        $id === 1 => route('admin.sales.desk', ['step' => 1]),
                        $id === 2 && $customer => route('admin.sales.desk', ['customer' => $customer->getRouteKey(), 'step' => 2]),
                        $id === 3 && $customer => route('admin.sales.desk', array_filter([
                            'customer' => $customer->getRouteKey(),
                            'specification' => $specification?->id,
                            'step' => 3,
                        ])),
                        $id === 4 && $order => route('admin.sales.desk', [
                            'customer' => $customer?->getRouteKey() ?? $order->customer?->getRouteKey(),
                            'order' => $order->getRouteKey(),
                            'step' => 4,
                        ]),
                        default => null,
                    };
                @endphp
                @if ($enabled && $href)
                    <a
                        href="{{ $href }}"
                        data-turbo-frame="_top"
                        @class([
                            'rounded-full border px-3 py-1 text-xs font-medium transition',
                            'border-erp-accent bg-erp-accent text-white' => $step === $id,
                            'border-emerald-300 bg-emerald-50 text-emerald-800' => $step > $id,
                            'border-slate-200 bg-white text-slate-600' => $step < $id,
                        ])
                    >{{ $label }}</a>
                @else
                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-400">{{ $label }}</span>
                @endif
            @endforeach
        </nav>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                @if ($step === 1)
                    <x-admin.card>
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <h2 class="text-sm font-semibold text-slate-900">{{ __('1. Find or create customer') }}</h2>
                            <x-admin.form-modal-link :href="$createCustomerUrl">
                                {{ __('Create customer') }}
                            </x-admin.form-modal-link>
                        </div>

                        <label class="erp-label" for="desk-customer-search">{{ __('Search existing') }}</label>
                        <input
                            id="desk-customer-search"
                            type="search"
                            class="erp-input w-full"
                            placeholder="{{ __('Name, code, phone, or email…') }}"
                            x-model="query"
                            x-on:input.debounce.300ms="search()"
                        >
                        <ul class="mt-2 divide-y divide-slate-100 rounded-lg border border-slate-200 bg-white" x-show="results.length" x-cloak>
                            <template x-for="row in results" :key="row.id">
                                <li>
                                    <a
                                        class="flex w-full items-start justify-between gap-2 px-3 py-2 text-left text-sm hover:bg-slate-50"
                                        :href="deskUrl + '?customer=' + encodeURIComponent(row.key || row.id) + '&step=2'"
                                        data-turbo-frame="_top"
                                    >
                                        <span>
                                            <span class="font-medium text-slate-900" x-text="row.label"></span>
                                            <span class="block text-xs text-slate-500" x-text="[row.code, row.phone, row.email].filter(Boolean).join(' · ')"></span>
                                        </span>
                                        <span class="text-xs text-erp-accent">{{ __('Select') }}</span>
                                    </a>
                                </li>
                            </template>
                        </ul>
                        <p class="mt-3 text-xs text-slate-500">{{ __('Select a customer above, or create a new one in the modal.') }}</p>
                    </x-admin.card>
                @endif

                @if ($step === 2 && $customer)
                    <x-admin.card>
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 class="text-sm font-semibold text-slate-900">{{ __('2. Print specification') }}</h2>
                                <p class="text-xs text-slate-600">{{ __('Customer') }}: <span class="font-medium text-slate-900">{{ $customer->name }}</span></p>
                            </div>
                            <x-admin.form-modal-link :href="$createSpecificationUrl">
                                {{ __('Create specification') }}
                            </x-admin.form-modal-link>
                        </div>

                        @if (count($printSpecifications))
                            <div class="mb-2">
                                <h3 class="mb-2 text-sm font-medium text-slate-800">{{ __('Select an active specification') }}</h3>
                                <div class="erp-table-scroll rounded-lg border border-erp-border">
                                    <table class="erp-table text-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Code') }}</th>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Product') }}</th>
                                                <th>{{ __('Artwork') }}</th>
                                                <th class="erp-table-actions-col"><span class="sr-only">{{ __('Actions') }}</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($printSpecifications as $spec)
                                                @php
                                                    $artworkMissing = ($spec['artwork_required'] ?? false) && ! ($spec['has_active_artwork'] ?? false);
                                                @endphp
                                                <tr @class(['bg-erp-accent/5' => $specification && (int) $specification->id === (int) $spec['id']])>
                                                    <td class="font-mono text-xs whitespace-nowrap">{{ $spec['specification_code'] }}</td>
                                                    <td class="min-w-[8rem] font-medium">{{ $spec['name'] }}</td>
                                                    <td class="min-w-[6rem]">{{ $spec['product_name'] ?? '—' }}</td>
                                                    <td class="min-w-[5rem] text-xs whitespace-nowrap">
                                                        @if ($spec['has_active_artwork'] ?? false)
                                                            <span class="inline-flex items-center gap-1 text-emerald-700">
                                                                <span>&#10003;</span> {{ $spec['current_artwork_label'] }}
                                                            </span>
                                                        @elseif ($artworkMissing)
                                                            <span class="inline-flex items-center gap-1 text-amber-700">
                                                                <span>!</span> {{ __('Required') }}
                                                            </span>
                                                        @else
                                                            <span class="text-slate-400">{{ __('N/A') }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="erp-table-actions-col whitespace-nowrap">
                                                        <div class="flex items-center justify-end gap-1">
                                                        @if ($artworkMissing)
                                                            <a
                                                                class="erp-btn-secondary text-xs py-1 px-2"
                                                                href="{{ route('admin.crm.customers.print-specifications.edit', [$customer, $spec['id'], 'from' => 'sales-desk']) }}"
                                                                data-erp-modal-open
                                                                title="{{ __('Upload artwork first') }}"
                                                            >{{ __('Upload artwork') }}</a>
                                                        @endif
                                                        <a
                                                            class="erp-btn-secondary text-xs py-1 px-2"
                                                            href="{{ route('admin.sales.desk', ['customer' => $customer->getRouteKey(), 'specification' => $spec['id'], 'step' => 3]) }}"
                                                            data-turbo-frame="_top"
                                                        >{{ __('Use') }}</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <p class="mt-2 text-xs text-slate-500">{{ __('Artwork is uploaded on the print specification. When creating a new spec, use the artwork section in the form. For existing specs missing artwork, click "Upload artwork" to open the spec editor.') }}</p>
                            </div>
                        @else
                            <p class="text-sm text-slate-600">{{ __('No active print specifications yet. Create one in the modal (Status = Active, product selected). Include artwork if the product requires it.') }}</p>
                        @endif
                    </x-admin.card>
                @endif

                @if ($step === 3 && $customer)
                    <x-admin.card>
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 class="text-sm font-semibold text-slate-900">{{ __('3. Create order') }}</h2>
                                <p class="text-xs text-slate-600">
                                    {{ $customer->name }}
                                    @if ($specification)
                                        · {{ $specification->name }}
                                    @endif
                                </p>
                            </div>
                            @if ($specification || count($printSpecifications))
                                <x-admin.form-modal-link :href="$createOrderUrl">
                                    {{ __('Create order') }}
                                </x-admin.form-modal-link>
                            @endif
                        </div>

                        @if (! $specification && ! count($printSpecifications))
                            <p class="text-sm text-amber-800">{{ __('No active print specification for this customer yet. Create one on the Specification step.') }}</p>
                            <a href="{{ route('admin.sales.desk', ['customer' => $customer->getRouteKey(), 'step' => 2]) }}" class="erp-btn-primary mt-3 inline-flex text-sm" data-turbo-frame="_top">{{ __('Go to specification') }}</a>
                        @else
                            @if ($specification)
                                @php
                                    $specArtwork = $specification->activeArtworkVersion;
                                    $specProduct = $specification->inventoryItem;
                                    $specArtworkRequired = $specProduct && $specProduct->stock_role === \App\Enums\InventoryStockRole::FinishedGood;
                                    $specArtworkMissing = $specArtworkRequired && ! $specArtwork;
                                @endphp
                                <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                                    <p class="font-medium text-slate-900">{{ $specification->name }}</p>
                                    <p class="text-xs text-slate-600">{{ $specification->specification_code }} · {{ $specProduct?->item_name }}</p>
                                    <p class="mt-1 text-xs">
                                        @if ($specArtwork)
                                            <span class="text-emerald-700">&#10003; {{ __('Artwork') }}: {{ $specArtwork->versionLabel() }}</span>
                                        @elseif ($specArtworkRequired)
                                            <span class="text-amber-700">! {{ __('Artwork required but missing') }}</span>
                                        @else
                                            <span class="text-slate-400">{{ __('Artwork not required') }}</span>
                                        @endif
                                    </p>
                                </div>
                                @if ($specArtworkMissing)
                                    <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                        <p class="font-medium">{{ __('Artwork needed before order') }}</p>
                                        <p class="mt-1 text-xs">{{ __('This product requires artwork on the print specification. Upload artwork to proceed, or the order creation will be blocked.') }}</p>
                                        <a
                                            class="erp-btn-secondary mt-2 inline-flex text-xs"
                                            href="{{ route('admin.crm.customers.print-specifications.edit', [$customer, $specification, 'from' => 'sales-desk']) }}"
                                            data-erp-modal-open
                                        >{{ __('Upload artwork to spec') }}</a>
                                    </div>
                                @else
                                    <p class="mt-2 text-sm text-slate-600">{{ __('Open the order form in a modal. Customer and specification are pre-filled.') }}</p>
                                @endif
                            @else
                                <p class="text-sm text-slate-600">{{ __('Open the order form in a modal. Select a specification inside the order form.') }}</p>
                            @endif
                        @endif
                    </x-admin.card>
                @endif

                @if ($step === 4 && $order)
                    <x-admin.card>
                        <h2 class="mb-3 text-sm font-semibold text-slate-900">{{ __('4. Release to production') }}</h2>

                        <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                            <p class="font-medium text-slate-900">{{ $orderPresentation['order_number'] }}</p>
                            <p class="text-xs text-slate-600">
                                {{ $orderPresentation['status_label'] }}
                                @if ($orderPresentation['job_card_number'])
                                    · {{ __('Job') }} {{ $orderPresentation['job_card_number'] }}
                                @endif
                            </p>
                        </div>

                        @if (empty($orderPresentation['job_card_id']) && ! empty($orderPresentation['readiness']['checks']))
                            <div class="mb-4">
                                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Readiness') }}</p>
                                <ul class="space-y-1 text-sm">
                                    @foreach ($orderPresentation['readiness']['checks'] as $check)
                                        <li class="flex items-start gap-2">
                                            <span @class([
                                                'text-emerald-600' => $check['passed'] ?? false,
                                                'text-amber-600' => ! ($check['passed'] ?? false) && ($check['severity'] ?? '') === 'warning',
                                                'text-rose-600' => ! ($check['passed'] ?? false) && ($check['severity'] ?? '') !== 'warning',
                                            ])>{{ ($check['passed'] ?? false) ? '✓' : '!' }}</span>
                                            <span>
                                                {{ $check['label'] }}
                                                @if (! empty($check['message']))
                                                    <span class="block text-xs text-slate-500">{{ $check['message'] }}</span>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($orderPresentation['can_release'] && empty($orderPresentation['job_card_id']))
                            <form method="POST" action="{{ route('admin.sales-orders.release-to-production', $order) }}" class="mb-4" data-erp-desk-form>
                                @csrf
                                <input type="hidden" name="from" value="sales-desk">
                                <button type="submit" class="erp-btn-primary" @disabled(! ($orderPresentation['readiness']['ready'] ?? false))>
                                    {{ __('Release to production') }}
                                </button>
                            </form>
                        @endif

                        @if ($orderPresentation['job_card_id'])
                            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                                <p class="font-medium">{{ __('Handed off to Production Floor') }}</p>
                                <p class="mt-1 text-xs">{{ __('Sales handoff is complete. Production can queue and start this job from Operator Floor.') }}</p>
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ $orderPresentation['show_url'] }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Open sales order') }}</a>
                            @if ($orderPresentation['job_url'])
                                <a href="{{ $orderPresentation['job_url'] }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Open job card') }}</a>
                            @endif
                            <a href="{{ route('admin.sales.desk') }}" class="erp-btn-primary text-sm" data-turbo-frame="_top">{{ __('Start another walk-in') }}</a>
                        </div>
                    </x-admin.card>
                @endif
            </div>

            <aside class="space-y-3">
                <x-admin.card>
                    <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Progress') }}</h3>
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('Customer') }}</dt>
                            <dd class="font-medium text-slate-900">{{ $customer?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('Specification') }}</dt>
                            <dd class="font-medium text-slate-900">{{ $specification?->name ?? '—' }}</dd>
                        </div>
                        @if ($specification)
                            <div>
                                <dt class="text-xs text-slate-500">{{ __('Artwork') }}</dt>
                                <dd class="font-medium">
                                    @if ($specification->activeArtworkVersion)
                                        <span class="text-emerald-700">{{ $specification->activeArtworkVersion->versionLabel() }} — {{ $specification->activeArtworkVersion->artwork_name }}</span>
                                    @else
                                        <span class="text-slate-400">{{ __('—') }}</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('Order') }}</dt>
                            <dd class="font-medium text-slate-900">{{ $orderPresentation['order_number'] ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('Production') }}</dt>
                            <dd class="font-medium text-slate-900">{{ $orderPresentation['job_card_number'] ?? '—' }}</dd>
                        </div>
                    </dl>
                </x-admin.card>
            </aside>
        </div>
    </div>
</x-admin-layout>
