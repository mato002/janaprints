@php
    use App\Enums\ProductionDestination;
    use App\Support\Production\DigitalSpecificationService;
    use App\Support\Production\OffsetJobSheetService;
    use App\Support\Production\OutsourceSpecificationService;

    $specification = $specification ?? null;
    $preselectedDestination = old(
        'production_destination',
        $preselectedDestination ?? $specification?->production_destination?->value ?? '',
    );
    $lockDestination = (bool) ($lockDestination ?? filled($preselectedDestination));
    $destinationEnum = is_string($preselectedDestination) && $preselectedDestination !== ''
        ? ProductionDestination::tryFrom($preselectedDestination)
        : null;
    $payload = is_array($specification?->job_sheet_payload) ? $specification->job_sheet_payload : [];
    if (empty($payload['description']) && empty($payload['product_description'])) {
        $fromModel = $specification?->product_description
            ?? $specification?->description
            ?? $specification?->name
            ?? null;
        if (filled($fromModel)) {
            $payload['description'] = $fromModel;
            $payload['product_description'] = $fromModel;
        }
    }
    $kind = $payload['kind'] ?? $specification?->production_destination?->value;
    if ($kind instanceof ProductionDestination) {
        $kind = $kind->value;
    }
    $jobSheetForm = OffsetJobSheetService::emptyForm(old(
        'job_sheet',
        $kind === 'offset' ? OffsetJobSheetService::formFromPayload($payload) : [],
    ));
    $digitalForm = DigitalSpecificationService::emptyForm(old(
        'digital',
        $kind === 'digital' ? DigitalSpecificationService::formFromPayload($payload) : [],
    ));
    $outsourceForm = OutsourceSpecificationService::emptyForm(old(
        'outsource',
        $kind === 'outsource' ? OutsourceSpecificationService::formFromPayload($payload) : [],
    ));
    $productionVendors = $productionVendors ?? app(OutsourceSpecificationService::class)->productionVendors();
    $idPrefix = $idPrefix ?? 'spec-job';
    $customerName = $customerName ?? ($customer->company_name ?? $customer->name ?? null);
    $compact = $compact ?? true;
    $quantityValue = $quantityValue ?? null;
    $unitPriceValue = $unitPriceValue ?? null;
    $asOfDate = $asOfDate ?? null;
    $showCommercials = (bool) ($showCommercials ?? false);

    if ($digitalForm['price'] === '' && filled($unitPriceValue) && (float) $unitPriceValue > 0) {
        $digitalForm['price'] = (string) $unitPriceValue;
    }

    if (
        $outsourceForm['selling_price'] === ''
        && filled($unitPriceValue)
        && filled($quantityValue)
        && (float) $unitPriceValue > 0
        && (float) $quantityValue > 0
    ) {
        $outsourceForm['selling_price'] = (string) round((float) $quantityValue * (float) $unitPriceValue, 2);
    }
@endphp

<div
    class="space-y-4"
    x-data="{ specDestination: @js($preselectedDestination) }"
>
    @if ($lockDestination && $destinationEnum)
        <input type="hidden" name="production_destination" value="{{ $destinationEnum->value }}">
        <p class="text-sm font-medium text-slate-800">{{ $destinationEnum->label() }}</p>
    @else
        @include('admin.sales.orders.partials.production-destination-picker', [
            'alpineModel' => 'specDestination',
            'value' => $preselectedDestination,
            'required' => true,
            'legend' => __('Type'),
        ])
    @endif

    @if ($showCommercials)
        <div>
            <h4 class="mb-2 text-sm font-medium text-slate-800">{{ __('Quantity and price') }}</h4>
            @include('admin.sales.orders.partials.order-commercial-fields', [
                'salesOrder' => $salesOrder ?? null,
                'quantityValue' => $quantityValue,
                'unitPriceValue' => $unitPriceValue,
                'idPrefix' => ($idPrefix ?? 'order').'-commercial',
            ])
        </div>
    @endif

    <template x-if="specDestination === 'digital'">
        <div>
            @include('admin.sales.orders.partials.digital-specification-fields', [
                'digitalForm' => $digitalForm,
                'includeQuantity' => false,
                'compact' => $compact,
                'quantityValue' => $quantityValue,
                'unitPriceValue' => $unitPriceValue,
                'asOfDate' => $asOfDate,
                'customerName' => $customerName,
                'idPrefix' => $idPrefix.'-digital',
            ])
        </div>
    </template>

    <template x-if="specDestination === 'offset'">
        <div>
            @include('admin.sales.orders.partials.offset-job-sheet-fields', [
                'jobSheetForm' => $jobSheetForm,
                'includeQuantity' => false,
                'includeCollectionDate' => false,
                'compact' => $compact,
                'quantityValue' => $quantityValue,
                'idPrefix' => $idPrefix.'-offset',
            ])
        </div>
    </template>

    <template x-if="specDestination === 'outsource'">
        <div>
            @include('admin.sales.orders.partials.outsource-specification-fields', [
                'outsourceForm' => $outsourceForm,
                'includeQuantity' => false,
                'compact' => $compact,
                'quantityValue' => $quantityValue,
                'productionVendors' => $productionVendors,
                'customerName' => $customerName,
                'idPrefix' => $idPrefix.'-outsource',
            ])
        </div>
    </template>
</div>
