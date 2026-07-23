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
        $lookupOptions = $eligibleOrders->map(fn ($order) => [
            'value' => $order->id,
            'label' => trim($order->order_number.' — '.($order->customer?->company_name ?? '')),
        ])->values()->all();
    @endphp

    <div
        class="space-y-4"
        x-data="erpContinuousWorkspace({ reloadOnReturn: true })"
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
                        refresh-route="admin.lookups.job_card_sales_orders"
                        :placeholder="__('Select sales order')"
                    />

                    @if ($eligibleOrders->isEmpty())
                        <div class="rounded-md border border-amber-200 bg-amber-50 px-3 py-3 text-sm text-amber-950">
                            <p class="font-medium">{{ __('Nothing ready to start here — resolve the blocker without leaving this screen.') }}</p>
                            <p class="mt-1 text-xs text-amber-900/80">
                                {{ __('When Sales already released an order, open its job card. When artwork is pending, approve it here, then check again.') }}
                            </p>

                            @if (! empty($resolution['already_have_job']))
                                <div class="mt-3 space-y-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-900/70">{{ __('Already have a job card') }}</p>
                                    <ul class="space-y-1.5">
                                        @foreach ($resolution['already_have_job'] as $row)
                                            <li class="flex flex-wrap items-center justify-between gap-2 rounded border border-amber-200/80 bg-white/70 px-2.5 py-2 text-xs">
                                                <span class="min-w-0 truncate">{{ $row['label'] }} · {{ $row['job_label'] }}</span>
                                                @if ($row['job_url'])
                                                    <a href="{{ $row['job_url'] }}" class="erp-btn-secondary shrink-0 !px-2 !py-1 text-xs" data-erp-modal-open>
                                                        {{ __('Continue job') }}
                                                    </a>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (! empty($resolution['blocked_artwork']))
                                <div class="mt-3 space-y-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-900/70">{{ __('Need approved artwork') }}</p>
                                    <ul class="space-y-1.5">
                                        @foreach ($resolution['blocked_artwork'] as $row)
                                            <li class="flex flex-wrap items-center justify-between gap-2 rounded border border-amber-200/80 bg-white/70 px-2.5 py-2 text-xs">
                                                <span class="min-w-0 truncate">{{ $row['label'] }}</span>
                                                @if ($row['resolve_url'])
                                                    <a href="{{ $row['resolve_url'] }}" class="erp-btn-secondary shrink-0 !px-2 !py-1 text-xs" data-erp-modal-open>
                                                        {{ $row['resolve_label'] }}
                                                    </a>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (($summary['already_have_job'] ?? 0) === 0 && ($summary['blocked_artwork'] ?? 0) === 0)
                                <p class="mt-2 text-xs">
                                    {{ __('No confirmed orders yet. Create a sales order below, then return here to continue.') }}
                                </p>
                            @endif

                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($salesOrderCreateUrl)
                                    <a href="{{ $salesOrderCreateUrl }}" class="erp-btn-secondary text-sm" data-erp-modal-open>
                                        {{ __('Create sales order') }}
                                    </a>
                                @endif
                                <button type="button" class="erp-btn-secondary text-sm" @click="checkAgain()">
                                    {{ __('Check again') }}
                                </button>
                            </div>
                        </div>
                    @elseif ($salesOrderCreateUrl)
                        <p class="text-xs text-slate-500">
                            <a href="{{ $salesOrderCreateUrl }}" class="font-medium text-slate-700 underline-offset-2 hover:underline" data-erp-modal-open>
                                {{ __('Create another sales order') }}
                            </a>
                            {{ __('without leaving this form — you will return here after saving.') }}
                        </p>
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
                />
            </div>

            <x-admin.form-actions>
                <x-primary-button>{{ __('Create job card') }}</x-primary-button>
            </x-admin.form-actions>
        </x-admin.form-shell>
    </div>
</x-admin.modal-form>
