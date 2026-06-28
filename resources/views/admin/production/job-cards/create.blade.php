<x-admin.modal-form
    :title="__('New job card')"
    :breadcrumbs="[
        ['label' => __('Job Cards'), 'url' => route('admin.production.job-cards.index')],
        ['label' => __('New')],
    ]"
    maxWidth="xl"
>
    <x-admin.form-shell :action="route('admin.production.job-cards.store')">
        <div class="erp-form-grid">
            <div class="md:col-span-2 space-y-2">
                <x-admin.select name="sales_order_id" :label="__('Sales order')" :required="true">
                    <option value="">{{ __('Select sales order') }}</option>
                    @foreach ($eligibleOrders as $order)
                        <option value="{{ $order->id }}" @selected(old('sales_order_id') == $order->id)>
                            {{ $order->order_number }} — {{ $order->customer?->company_name }}
                        </option>
                    @endforeach
                </x-admin.select>

                @if ($eligibleOrders->isEmpty())
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="alert">
                        <p class="font-medium">{{ __('No sales orders are ready for a job card yet.') }}</p>
                        <p class="mt-1 text-amber-800">
                            {{ __('Create a sales order from an accepted quotation with approved artwork, confirm it, then return here to open the job card.') }}
                        </p>
                        @if ($salesOrderCreateUrl || $salesOrdersUrl)
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($salesOrderCreateUrl)
                                    <a href="{{ $salesOrderCreateUrl }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">
                                        {{ __('Create sales order') }}
                                    </a>
                                @endif
                                @if ($salesOrdersUrl)
                                    <a href="{{ $salesOrdersUrl }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">
                                        {{ __('View sales orders') }}
                                    </a>
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
            />
        </div>

        <x-admin.form-actions>
            <x-primary-button>{{ __('Create job card') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
