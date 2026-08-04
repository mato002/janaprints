@php
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Sales\SalesDeskViews;

    $activeSalesView = SalesDeskViews::normalize($activeSalesView ?? request('view'));
    $isPanel = SalesDeskViews::isPanelView($activeSalesView);
    $deskTitle = $isPanel ? ($registerTitle ?? __('Sales Desk')) : __('Sales Desk');
    $stepLabels = [
        1 => __('Customer'),
        2 => __('Specification'),
        3 => __('Order'),
        4 => __('Artwork'),
        5 => __('Complete'),
    ];
    $deskFrame = WorkspaceEmbed::turboFrame();

    $createCustomerUrl = route('admin.crm.customers.create', ['from' => 'sales-desk']);
    $walkInComplete = ! empty($orderPresentation['released_to_queue']);
    $hasSpecs = count($printSpecifications ?? []) > 0;
    $defaultSpecMode = $hasSpecs ? 'existing' : 'new';
@endphp

<x-admin-layout
    :title="$deskTitle"
    :breadcrumbs="$operatorMode
        ? [['label' => __('Sales Desk')]]
        : [
            ['label' => __('Commercial'), 'url' => $fullCommercialDeskUrl],
            ['label' => __('Sales Desk')],
        ]"
>
    <div
        class="sales-desk-shell min-w-0 max-w-full"
        @unless ($isPanel)
        x-data="salesDeskSearch(@js([
            'searchUrl' => $searchUrl,
            'deskUrl' => route('admin.sales.desk'),
        ]))"
        @endunless
    >
        @unless (WorkspaceEmbed::inWorkspaceContext())
            @include('admin.sales.desk.partials.desk-mode-nav', ['activeSalesView' => $activeSalesView])
        @endunless

        @if ($isPanel)
            @include('admin.sales.desk.partials.register-panel')
        @else
        <div class="mb-3 flex flex-col gap-2 rounded-lg border border-erp-accent/25 bg-erp-accent/5 px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-erp-primary">{{ __('Sales desk') }}</p>
                <p class="text-xs text-slate-600">{{ __('One guided walk-in — customer through order, without leaving this desk.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @unless ($operatorMode)
                    <a href="{{ $fullCommercialDeskUrl }}" class="erp-btn-secondary text-xs" data-turbo-frame="erp-main">{{ __('Full Commercial desk') }}</a>
                @endunless
                <a href="{{ WorkspaceEmbed::url(route('admin.sales.desk')) }}" class="erp-btn-secondary text-xs" data-turbo-frame="{{ $deskFrame }}" data-turbo-action="advance">{{ __('Start another') }}</a>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" data-erp-flash-status>
                {{ session('status') }}
                @if (session('sales_desk_receipt_url'))
                    <a href="{{ session('sales_desk_receipt_url') }}" class="ml-2 font-medium underline" data-erp-modal-open>{{ __('View receipt') }}</a>
                @endif
            </div>
        @endif
        @if (! empty($specificationNotice))
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $specificationNotice }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" data-erp-flash-error>
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" data-erp-validation-errors>
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('admin.sales.desk.partials.fast-actions', ['fastActions' => $fastActions])

        <nav class="mb-4 -mx-1 flex gap-2 overflow-x-auto px-1 pb-1" aria-label="{{ __('Walk-in steps') }}">
            @foreach ($stepLabels as $id => $label)
                @php
                    $enabled = $id === 1
                        || ($id === 2 && $customer)
                        || ($id === 3 && $customer && ($specification || $hasSpecs))
                        || ($id === 4 && $order)
                        || ($id === 5 && $order && $walkInComplete);
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
                        $id === 5 && $order && $walkInComplete => route('admin.sales.desk', [
                            'customer' => $customer?->getRouteKey() ?? $order->customer?->getRouteKey(),
                            'order' => $order->getRouteKey(),
                            'step' => 5,
                        ]),
                        default => null,
                    };
                    $stepComplete = $id < $step || ($id === 5 && $walkInComplete);
                    $stepCurrent = $step === $id && ! ($id === 5 && $walkInComplete && $step > 5);
                    if ($id === 5 && $walkInComplete && $step === 5) {
                        $stepCurrent = true;
                        $stepComplete = true;
                    }
                @endphp
                @if ($enabled && $href)
                    <a
                        href="{{ WorkspaceEmbed::url($href) }}"
                        data-turbo-frame="{{ $deskFrame }}"
                        data-turbo-action="advance"
                        @class([
                            'shrink-0 rounded-full border px-3 py-1 text-xs font-medium transition',
                            'border-erp-accent bg-erp-accent text-white' => $stepCurrent && ! $stepComplete,
                            'border-emerald-300 bg-emerald-50 text-emerald-800' => $stepComplete,
                            'border-slate-200 bg-white text-slate-600' => ! $stepComplete && ! $stepCurrent,
                        ])
                    >{{ $stepComplete ? '✓ ' : '' }}{{ $label }}</a>
                @else
                    <span class="shrink-0 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-400">{{ $label }}</span>
                @endif
            @endforeach
        </nav>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                @if ($step === 1)
                    <x-admin.card>
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <h2 class="text-sm font-semibold text-slate-900">{{ __('1. Find or create customer') }}</h2>
                            <div class="flex flex-wrap gap-2">
                                <x-admin.form-modal-link :href="$createCustomerUrl">
                                    {{ __('Create customer') }}
                                </x-admin.form-modal-link>
                                @if ($operatorMode && $customer && ($deskUrls['quotation'] ?? null))
                                    <x-admin.form-modal-link :href="$deskUrls['quotation']" class="erp-btn-secondary text-xs">
                                        {{ __('Quote first') }}
                                    </x-admin.form-modal-link>
                                @endif
                            </div>
                        </div>

                        <div class="relative" @click.outside="closeDropdown()">
                            <label class="erp-label" for="desk-customer-search">{{ __('Search existing') }}</label>
                            <div class="relative">
                                <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input
                                    id="desk-customer-search"
                                    type="text"
                                    class="erp-input w-full py-2 pl-9 pr-3"
                                    placeholder="{{ __('Customer, quote, order, job, phone…') }}"
                                    autocomplete="off"
                                    role="combobox"
                                    aria-autocomplete="list"
                                    aria-controls="desk-customer-search-list"
                                    :aria-expanded="open"
                                    x-model="query"
                                    @focus="openDropdown()"
                                    @click="openDropdown()"
                                    @input="onInput()"
                                    @keydown.escape.prevent="closeDropdown()"
                                >
                            </div>

                            <div
                                id="desk-customer-search-list"
                                role="listbox"
                                x-show="open"
                                x-cloak
                                class="absolute z-30 mt-1 max-h-64 w-full overflow-y-auto rounded-lg border border-erp-border bg-white shadow-lg"
                            >
                                <p x-show="loading" class="px-3 py-4 text-center text-sm text-slate-500">{{ __('Loading…') }}</p>

                                <template x-if="! loading && results.length === 0">
                                    <p class="px-3 py-4 text-center text-sm text-slate-500">
                                        <span x-show="query.trim()">{{ __('No matches for your search.') }}</span>
                                        <span x-show="! query.trim()">{{ __('No active customers yet.') }}</span>
                                    </p>
                                </template>

                                <ul x-show="! loading && results.length" class="divide-y divide-slate-100">
                                    <template x-for="row in results" :key="`${row.kind}-${row.id}`">
                                        <li>
                                            <a
                                                role="option"
                                                class="flex w-full items-start justify-between gap-2 px-3 py-2.5 text-left text-sm transition hover:bg-slate-50 focus:bg-slate-50 focus:outline-none"
                                                :href="resultHref(row)"
                                                :data-erp-modal-open="row.modal ? '' : null"
                                                :data-turbo-frame="row.modal ? null : '_top'"
                                                @click="closeDropdown()"
                                            >
                                                <span class="min-w-0">
                                                    <span class="mb-0.5 inline-flex rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600" x-text="resultKindLabel(row)"></span>
                                                    <span class="block truncate font-medium text-slate-900" x-text="row.label"></span>
                                                    <span class="block truncate text-xs text-slate-500" x-text="row.meta || ''"></span>
                                                </span>
                                                <span class="shrink-0 text-xs font-medium text-erp-accent">{{ __('Open') }}</span>
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-slate-500">{{ __('Search customers, quotes, orders, or jobs—or create a new customer to start a walk-in.') }}</p>
                    </x-admin.card>
                @endif

                @if ($step === 2 && $customer)
                    <x-admin.card>
                        <div
                            x-data="{
                                specMode: @js($defaultSpecMode),
                                specQuery: '',
                                setMode(mode) {
                                    if (mode === 'existing' && ! @js($hasSpecs)) {
                                        this.specMode = 'new';
                                        return;
                                    }
                                    this.specMode = mode;
                                },
                            }"
                            x-on:desk-spec-mode.window="setMode($event.detail.mode || 'existing')"
                        >
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 class="text-sm font-semibold text-slate-900">{{ __('2. Print specification') }}</h2>
                                <p class="text-xs text-slate-600">
                                    {{ __('Customer') }}:
                                    <span class="font-medium text-slate-900">{{ $customer->name }}</span>
                                    <span class="ml-1 rounded-full border border-emerald-200 bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-800">{{ __('Locked') }}</span>
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if (($deskUrls['customer_360'] ?? null))
                                    <a href="{{ $deskUrls['customer_360'] }}" class="erp-btn-secondary text-xs" data-turbo-frame="erp-main">{{ __('View Customer 360') }}</a>
                                @endif
                                @if ($operatorMode && ($deskUrls['quotation'] ?? null))
                                    <x-admin.form-modal-link :href="$deskUrls['quotation']" class="erp-btn-secondary text-xs">
                                        {{ __('Quote first') }}
                                    </x-admin.form-modal-link>
                                @endif
                            </div>
                        </div>

                        <fieldset class="mb-4">
                            <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Print specification') }}</legend>
                            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                                <label
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm transition"
                                    :class="specMode === 'existing' ? 'border-erp-accent bg-erp-accent/5 text-slate-900' : 'border-slate-200 bg-white text-slate-600'"
                                >
                                    <input
                                        type="radio"
                                        class="text-erp-accent"
                                        name="desk_spec_mode"
                                        value="existing"
                                        @checked($defaultSpecMode === 'existing')
                                        @disabled(! $hasSpecs)
                                        @change="setMode('existing')"
                                    >
                                    {{ __('Use existing specification') }}
                                </label>
                                <label
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm transition"
                                    :class="specMode === 'new' ? 'border-erp-accent bg-erp-accent/5 text-slate-900' : 'border-slate-200 bg-white text-slate-600'"
                                >
                                    <input
                                        type="radio"
                                        class="text-erp-accent"
                                        name="desk_spec_mode"
                                        value="new"
                                        @checked($defaultSpecMode === 'new')
                                        @change="setMode('new')"
                                    >
                                    {{ __('Create new specification') }}
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-slate-500" x-text="specMode === 'existing'
                                ? @js(__('Pick a saved specification, then click Use to continue to the order.'))
                                : @js(__('Fill in the form below, save, and the walk-in continues automatically.'))"></p>
                        </fieldset>

                        <div x-show="specMode === 'existing'" style="{{ $defaultSpecMode === 'existing' ? '' : 'display: none' }}">
                            @if ($hasSpecs)
                                <div class="mb-3">
                                    <label class="erp-label" for="desk-spec-search">{{ __('Search specification') }}</label>
                                    <input
                                        id="desk-spec-search"
                                        type="search"
                                        class="erp-input w-full"
                                        placeholder="{{ __('Name, code, or product…') }}"
                                        x-model="specQuery"
                                    >
                                </div>

                                <h3 class="mb-2 text-sm font-medium text-slate-800">{{ __('Recent & saved specifications') }}</h3>
                                <div class="erp-table-scroll rounded-lg border border-erp-border">
                                    <table class="erp-table text-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Code') }}</th>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Product') }}</th>
                                                <th>{{ __('Artwork') }}</th>
                                                <th>{{ __('Default price') }}</th>
                                                <th>{{ __('Last used') }}</th>
                                                <th class="erp-table-actions-col"><span class="sr-only">{{ __('Actions') }}</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($printSpecifications as $spec)
                                                @php
                                                    $artworkMissing = ($spec['artwork_required'] ?? false) && ! ($spec['has_active_artwork'] ?? false);
                                                    $searchBlob = strtolower(implode(' ', array_filter([
                                                        $spec['specification_code'] ?? '',
                                                        $spec['name'] ?? '',
                                                        $spec['product_name'] ?? '',
                                                    ])));
                                                    $lastUsed = $spec['last_used_at'] ?? null;
                                                    if ($lastUsed instanceof \Carbon\CarbonInterface) {
                                                        $lastUsedLabel = $lastUsed->format('d M Y');
                                                    } elseif (is_string($lastUsed) && $lastUsed !== '') {
                                                        $lastUsedLabel = \Illuminate\Support\Carbon::parse($lastUsed)->format('d M Y');
                                                    } else {
                                                        $lastUsedLabel = '—';
                                                    }
                                                @endphp
                                                <tr
                                                    data-spec-search="{{ e($searchBlob) }}"
                                                    x-show="!specQuery.trim() || ($el.dataset.specSearch || '').includes(specQuery.trim().toLowerCase())"
                                                    @class(['bg-erp-accent/5' => $specification && (int) $specification->id === (int) $spec['id']])
                                                >
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
                                                    <td class="font-mono text-xs whitespace-nowrap">{{ $spec['default_unit_price'] ?? '—' }}</td>
                                                    <td class="text-xs whitespace-nowrap">{{ $lastUsedLabel }}</td>
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
                                                                href="{{ WorkspaceEmbed::url(route('admin.sales.desk', ['customer' => $customer->getRouteKey(), 'specification' => $spec['id'], 'step' => 3])) }}"
                                                                data-turbo-frame="{{ $deskFrame }}"
                                                                data-turbo-action="advance"
                                                            >{{ __('Use') }}</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-slate-600">{{ __('No saved specifications for this customer yet. Choose Create new specification above.') }}</p>
                            @endif
                        </div>

                        <div x-show="specMode === 'new'" style="{{ $defaultSpecMode === 'new' ? '' : 'display: none' }}">
                            @include('admin.sales.desk.partials.inline-spec-form', [
                                'customer' => $customer,
                                'inventoryItemOptions' => $inventoryItemOptions ?? [],
                            ])
                        </div>
                        </div>
                    </x-admin.card>
                @endif

                @if ($step === 3 && $customer)
                    <x-admin.card>
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 class="text-sm font-semibold text-slate-900">{{ __('3. Order details') }}</h2>
                                <p class="text-xs text-slate-600">{{ __('Enter quantity and price for this order. Delivery, priority, and billing come next — customer and specification stay locked.') }}</p>
                            </div>
                        </div>

                        @if (! $specification && ! $hasSpecs)
                            <p class="text-sm text-amber-800">{{ __('No active print specification for this customer yet. Create one on the Specification step.') }}</p>
                            <a href="{{ WorkspaceEmbed::url(route('admin.sales.desk', ['customer' => $customer->getRouteKey(), 'step' => 2])) }}" class="erp-btn-primary mt-3 inline-flex text-sm" data-turbo-frame="{{ $deskFrame }}" data-turbo-action="advance">{{ __('Go to specification') }}</a>
                        @elseif (! $specification)
                            <p class="text-sm text-slate-600">{{ __('Select a specification to continue. It will be locked for this order.') }}</p>
                            <a href="{{ WorkspaceEmbed::url(route('admin.sales.desk', ['customer' => $customer->getRouteKey(), 'step' => 2])) }}" class="erp-btn-primary mt-3 inline-flex text-sm" data-turbo-frame="{{ $deskFrame }}" data-turbo-action="advance">{{ __('Choose specification') }}</a>
                        @else
                            @include('admin.sales.desk.partials.inline-order-form', [
                                'customer' => $customer,
                                'specification' => $specification,
                                'deskUrls' => $deskUrls,
                                'orderPriorities' => $orderPriorities,
                                'canSendToProduction' => $canSendToProduction,
                            ])
                        @endif
                    </x-admin.card>
                @endif

                @if ($step === 4 && $order)
                    <x-admin.card>
                        <h2 class="mb-3 text-sm font-semibold text-slate-900">{{ __('4. Artwork & release') }}</h2>

                        <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                            <p class="font-medium text-slate-900">{{ $orderPresentation['order_number'] }}</p>
                            <p class="text-xs text-slate-600">
                                {{ $orderPresentation['status_label'] }}
                                @if ($orderPresentation['job_card_number'])
                                    · {{ __('Job') }} {{ $orderPresentation['job_card_number'] }}
                                @endif
                            </p>
                            @if ($specification)
                                <p class="mt-2 text-xs">
                                    @if ($specification->activeArtworkVersion)
                                        <span class="text-emerald-700">&#10003; {{ __('Artwork') }}: {{ $specification->activeArtworkVersion->versionLabel() }} — {{ $specification->activeArtworkVersion->artwork_name }}</span>
                                    @else
                                        <span class="text-amber-700">{{ __('Artwork pending on specification') }}</span>
                                    @endif
                                </p>
                            @endif
                        </div>

                        @if ($specification && ! $specification->activeArtworkVersion)
                            <div class="mb-4 flex flex-wrap gap-2">
                                <a
                                    class="erp-btn-secondary text-xs"
                                    href="{{ route('admin.crm.customers.print-specifications.edit', [$customer ?? $order->customer, $specification, 'from' => 'sales-desk']) }}"
                                    data-erp-modal-open
                                >{{ __('Upload artwork') }}</a>
                                @if ($operatorMode && ($deskUrls['artwork_request'] ?? null))
                                    <a class="erp-btn-secondary text-xs" href="{{ $deskUrls['artwork_request'] }}" data-erp-modal-open>{{ __('Send to designer') }}</a>
                                @endif
                            </div>
                        @endif

                        @if ($orderPresentation['can_release'] && ! empty($orderPresentation['readiness']['checks']))
                            @php
                                $releaseDashboard = $walkInPanel['dashboard'] ?? [];
                                $releaseReady = (bool) ($orderPresentation['readiness']['ready'] ?? false);
                            @endphp
                            <div class="mb-4">
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Release readiness') }}</p>
                                    @if ($releaseReady)
                                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-800">{{ __('Ready') }}</span>
                                    @endif
                                </div>
                                <ul class="divide-y divide-slate-100 rounded-lg border border-erp-border bg-white text-sm">
                                    @foreach ($releaseDashboard as $row)
                                        <li class="px-3 py-2.5">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="font-medium text-slate-800">{{ $row['label'] }}</span>
                                                <span @class([
                                                    'text-sm font-semibold',
                                                    'text-emerald-700' => $row['passed'] ?? false,
                                                    'text-amber-700' => ! ($row['passed'] ?? false) && ($row['severity'] ?? '') === 'warning',
                                                    'text-rose-700' => ! ($row['passed'] ?? false) && ($row['severity'] ?? '') !== 'warning',
                                                ])>{{ ($row['passed'] ?? false) ? '✓' : '!' }}</span>
                                            </div>
                                            @if (! ($row['passed'] ?? false) && ! empty($row['message']))
                                                <p class="mt-1 text-xs text-slate-600">{{ $row['message'] }}</p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                                @if ($releaseReady)
                                    <p class="mt-3 text-sm font-semibold uppercase tracking-wide text-emerald-800">{{ __('Ready for production') }}</p>
                                @endif
                            </div>
                        @endif

                        @if ($orderPresentation['can_release'])
                            <form
                                method="POST"
                                action="{{ route('admin.sales-orders.release-to-production', $order) }}"
                                class="mb-4"
                                data-erp-desk-form
                                data-turbo="false"
                                data-erp-desk-success-message="{{ __('Sales order sent to production queue.') }}"
                                data-erp-desk-submitting-message="{{ __('Submitting to production queue…') }}"
                            >
                                @csrf
                                <input type="hidden" name="from" value="sales-desk">
                                <button type="submit" class="erp-btn-primary" @disabled(! ($orderPresentation['readiness']['ready'] ?? false))>
                                    @if (empty($orderPresentation['job_card_id']))
                                        {{ __('Release to production') }}
                                    @else
                                        {{ __('Submit to production queue') }}
                                    @endif
                                </button>
                            </form>
                            @if (empty($orderPresentation['readiness']['ready'] ?? false))
                                <p class="mb-4 text-sm text-amber-700">{{ __('Fix the items marked above before releasing to production.') }}</p>
                            @endif
                        @endif

                        @if ($orderPresentation['job_card_id'])
                            @include('admin.sales.desk.partials.production-handoff', ['orderPresentation' => $orderPresentation])
                        @endif

                        @if ($operatorMode)
                            @include('admin.sales.desk.partials.order-actions', ['orderPresentation' => $orderPresentation])
                        @endif

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ $orderPresentation['show_url'] }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Open sales order') }}</a>
                            @if ($orderPresentation['job_url'])
                                <a href="{{ $orderPresentation['job_url'] }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Open job card') }}</a>
                            @endif
                        </div>
                    </x-admin.card>
                @endif

                @if ($step === 5 && $order)
                    <x-admin.card class="border-emerald-200">
                        <div class="mb-4 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
                            <span class="text-lg font-semibold text-emerald-600" aria-hidden="true">✓</span>
                            <div class="min-w-0">
                                <h2 class="text-sm font-semibold text-emerald-900">{{ __('5. Walk-in complete') }}</h2>
                                <p class="mt-1 text-sm text-emerald-800">
                                    {{ __(':order is on the production queue. Production picks up from here.', ['order' => $orderPresentation['order_number']]) }}
                                </p>
                                @if (! empty($orderPresentation['production']['work_center']))
                                    <p class="mt-1 text-xs text-emerald-700">
                                        {{ __('Queued at :work_center · :status', [
                                            'work_center' => $orderPresentation['production']['work_center'],
                                            'status' => $orderPresentation['production']['queue_status'] ?? __('Waiting'),
                                        ]) }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Completed steps') }}</p>
                            <ul class="space-y-1 text-sm text-emerald-800">
                                <li>✓ {{ __('Customer') }} — {{ $customer?->name ?? '—' }}</li>
                                <li>✓ {{ __('Specification') }} — {{ $specification?->name ?? __('On order') }}</li>
                                <li>✓ {{ __('Order') }} — {{ $orderPresentation['order_number'] }}</li>
                                <li>✓ {{ __('Artwork') }} — {{ $specification?->activeArtworkVersion?->versionLabel() ?? __('Reviewed') }}</li>
                                <li>✓ {{ __('Complete') }} — {{ $orderPresentation['job_card_number'] ?? __('Job created') }}</li>
                            </ul>
                        </div>

                        @if ($orderPresentation['job_card_id'])
                            @include('admin.sales.desk.partials.production-handoff', ['orderPresentation' => $orderPresentation])
                        @endif

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ $orderPresentation['show_url'] }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Open sales order') }}</a>
                            @if ($orderPresentation['job_url'])
                                <a href="{{ $orderPresentation['job_url'] }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Open job card') }}</a>
                            @endif
                            @if (! empty($orderPresentation['production']['department_queue_url']))
                                <a href="{{ WorkspaceEmbed::url($orderPresentation['production']['department_queue_url']) }}" class="erp-btn-secondary text-sm" data-turbo-frame="{{ $deskFrame }}" data-turbo-action="advance">{{ __('Open production queue') }}</a>
                            @endif
                            <a href="{{ WorkspaceEmbed::url(route('admin.sales.desk')) }}" class="erp-btn-primary text-sm" data-turbo-frame="{{ $deskFrame }}" data-turbo-action="advance">{{ __('Start another walk-in') }}</a>
                        </div>
                    </x-admin.card>
                @endif
            </div>

            @include('admin.sales.desk.partials.walk-in-panel', [
                'walkInPanel' => $walkInPanel ?? [],
            ])
        </div>

        @include('admin.sales.desk.partials.work-queue', ['workQueue' => $workQueue])
        @endif
    </div>
</x-admin-layout>
