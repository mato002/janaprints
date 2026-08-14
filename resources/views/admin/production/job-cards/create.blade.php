<x-admin.modal-form
    :title="__('New job card')"
    :breadcrumbs="[
        ['label' => __('Job Cards'), 'url' => route('admin.production.job-cards.index')],
        ['label' => __('New')],
    ]"
    maxWidth="xl"
>
    @php
        $resolution = $resolutionContext ?? [
            'summary' => $eligibilitySummary ?? ['already_have_job' => 0, 'blocked_artwork' => 0, 'ready_without_job' => 0],
            'already_have_job' => [],
            'blocked_artwork' => [],
        ];
        $summary = $resolution['summary'];
        $alreadyCount = (int) ($summary['already_have_job'] ?? 0);
        $blockedCount = (int) ($summary['blocked_artwork'] ?? 0);
        $hasBlockers = $alreadyCount > 0 || $blockedCount > 0;
        $registerUrl = \App\Support\Production\ProductionFloorDeskViews::registerIndexUrl();
        $lookupOptions = $eligibleOrders->map(fn ($order) => [
            'value' => $order->id,
            'label' => trim($order->order_number.' — '.($order->customer?->company_name ?? '')),
        ])->values()->all();
    @endphp

    <div
        class="space-y-4"
        x-data="erpContinuousWorkspace({ reloadOnReturn: false })"
    >
        <x-admin.form-shell :action="route('admin.production.job-cards.store')">
            @if ($fromProductionFloor ?? request('from') === 'production-floor')
                <input type="hidden" name="from" value="production-floor">
            @endif
            <div class="erp-form-grid">
                <div class="md:col-span-2 space-y-3">
                    <x-admin.lookup-select
                        name="sales_order_id"
                        :label="__('Sales order')"
                        :options="$lookupOptions"
                        :value="old('sales_order_id', $preselectedSalesOrderId ?? null)"
                        :required="true"
                        create-route="admin.sales-orders.create"
                        :create-query="array_filter([
                            'tab' => 'direct',
                            'from' => ($fromProductionFloor ?? false) ? 'production-floor' : null,
                        ])"
                        refresh-route="admin.lookups.job_card_sales_orders"
                        permission="sales_orders.create"
                        :modal-title="__('Create sales order')"
                        :placeholder="__('Select sales order')"
                    />

                    @if ($eligibleOrders->isEmpty())
                        <div class="rounded-md border border-amber-200 bg-amber-50 px-3 py-3 text-sm text-amber-950">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-medium">{{ __('No sales order ready for a new job card') }}</p>
                                    <p class="mt-1 text-xs text-amber-900/80">
                                        @if ($hasBlockers)
                                            {{ __('Released orders already have jobs, or artwork still needs approval. Use the shortcuts below only if you need to unblock one.') }}
                                        @else
                                            @can('sales_orders.create')
                                                {{ __('No confirmed orders yet. Use + beside Sales order to create one, then check again.') }}
                                            @else
                                                {{ __('No confirmed orders yet. Ask Sales to create and release an order, then check again.') }}
                                            @endcan
                                        @endif
                                    </p>
                                </div>
                                <button type="button" class="erp-btn-secondary shrink-0 text-xs" @click="checkAgain()">
                                    {{ __('Check again') }}
                                </button>
                            </div>

                            @if ($hasBlockers)
                                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                    @if ($alreadyCount > 0)
                                        <span class="inline-flex items-center rounded-full border border-amber-300/80 bg-white/70 px-2.5 py-1 font-medium text-amber-950">
                                            {{ trans_choice(':count already has a job|:count already have jobs', $alreadyCount, ['count' => $alreadyCount]) }}
                                        </span>
                                    @endif
                                    @if ($blockedCount > 0)
                                        <span class="inline-flex items-center rounded-full border border-amber-300/80 bg-white/70 px-2.5 py-1 font-medium text-amber-950">
                                            {{ trans_choice(':count needs artwork|:count need artwork', $blockedCount, ['count' => $blockedCount]) }}
                                        </span>
                                    @endif
                                    <a href="{{ $registerUrl }}" class="inline-flex items-center rounded-full border border-amber-300/80 bg-white/70 px-2.5 py-1 font-medium text-erp-primary hover:underline" data-turbo-frame="erp-main" data-turbo-action="advance">
                                        {{ __('Open job register') }}
                                    </a>
                                </div>

                                <div class="mt-3 space-y-2" x-data="{ open: null }">
                                    @if (! empty($resolution['already_have_job']))
                                        <div class="rounded border border-amber-200/80 bg-white/60">
                                            <button
                                                type="button"
                                                class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-amber-900/80"
                                                @click="open = open === 'jobs' ? null : 'jobs'"
                                                :aria-expanded="open === 'jobs'"
                                            >
                                                <span>{{ __('Continue an existing job') }}</span>
                                                <span class="font-normal normal-case tracking-normal text-amber-900/60" x-text="open === 'jobs' ? '{{ __('Hide') }}' : '{{ __('Show') }}'"></span>
                                            </button>
                                            <ul x-show="open === 'jobs'" x-cloak class="max-h-36 space-y-1 overflow-y-auto border-t border-amber-200/70 px-2 py-2">
                                                @foreach ($resolution['already_have_job'] as $row)
                                                    <li class="flex items-center justify-between gap-2 rounded px-1.5 py-1.5 text-xs hover:bg-amber-50/80">
                                                        <span class="min-w-0 truncate" title="{{ $row['label'] }} · {{ $row['job_label'] }}">{{ $row['label'] }} · {{ $row['job_label'] }}</span>
                                                        @if ($row['job_url'])
                                                            <a
                                                                href="{{ \App\Support\Navigation\WorkspaceEmbed::mainUrl($row['job_url']) }}"
                                                                class="shrink-0 text-erp-primary hover:underline"
                                                                data-turbo-frame="erp-main"
                                                                data-turbo-action="advance"
                                                            >{{ __('Open') }}</a>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if (! empty($resolution['blocked_artwork']))
                                        <div class="rounded border border-amber-200/80 bg-white/60">
                                            <button
                                                type="button"
                                                class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-amber-900/80"
                                                @click="open = open === 'artwork' ? null : 'artwork'"
                                                :aria-expanded="open === 'artwork'"
                                            >
                                                <span>{{ __('Unblock artwork') }}</span>
                                                <span class="font-normal normal-case tracking-normal text-amber-900/60" x-text="open === 'artwork' ? '{{ __('Hide') }}' : '{{ __('Show') }}'"></span>
                                            </button>
                                            <ul x-show="open === 'artwork'" x-cloak class="max-h-36 space-y-1 overflow-y-auto border-t border-amber-200/70 px-2 py-2">
                                                @foreach ($resolution['blocked_artwork'] as $row)
                                                    <li class="flex items-center justify-between gap-2 rounded px-1.5 py-1.5 text-xs hover:bg-amber-50/80">
                                                        <span class="min-w-0 truncate" title="{{ $row['label'] }}">{{ $row['label'] }}</span>
                                                        @if ($row['resolve_url'])
                                                            <a
                                                                href="{{ \App\Support\Navigation\WorkspaceEmbed::mainUrl($row['resolve_url']) }}"
                                                                class="shrink-0 text-erp-primary hover:underline"
                                                                data-turbo-frame="erp-main"
                                                                data-turbo-action="advance"
                                                            >{{ $row['resolve_label'] }}</a>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <x-admin.select name="production_type" :label="__('Production type')" :required="true">
                    @foreach ($productionTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('production_type', 'mixed') === $type->value)>
                            {{ ucfirst(str_replace('_', ' ', $type->value)) }}
                        </option>
                    @endforeach
                </x-admin.select>

                <x-admin.select name="priority" :label="__('Priority')" :required="true">
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->value }}" @selected(old('priority', 'normal') === $priority->value)>
                            {{ ucfirst($priority->value) }}
                        </option>
                    @endforeach
                </x-admin.select>

                <x-admin.input
                    name="planned_start_date"
                    type="date"
                    :label="__('Planned start')"
                    :value="old('planned_start_date')"
                />

                <x-admin.input
                    name="planned_end_date"
                    type="date"
                    :label="__('Planned end')"
                    :value="old('planned_end_date')"
                    min="{{ now()->toDateString() }}"
                />
            </div>

            <x-admin.form-actions>
                <x-primary-button>{{ __('Create job card') }}</x-primary-button>
            </x-admin.form-actions>
        </x-admin.form-shell>
    </div>
</x-admin.modal-form>
