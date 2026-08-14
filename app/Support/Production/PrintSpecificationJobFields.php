<?php

namespace App\Support\Production;

use App\Enums\ProductionDestination;
use App\Models\Crm\CustomerPrintSpecification;
use Illuminate\Validation\Rule;

class PrintSpecificationJobFields
{
    public function __construct(
        protected OffsetJobSheetService $offset,
        protected DigitalSpecificationService $digital,
        protected OutsourceSpecificationService $outsource,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function validationRules(bool $destinationRequired = true): array
    {
        return array_merge([
            'production_destination' => [
                $destinationRequired ? 'required' : 'nullable',
                Rule::enum(ProductionDestination::class),
            ],
        ], $this->offset->orderValidationRules(), $this->outsource->orderValidationRules(), $this->digital->orderValidationRules());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function payloadFromRequest(array $data): ?array
    {
        $destination = $this->destinationValue($data['production_destination'] ?? null);

        if ($destination === null) {
            return null;
        }

        return match ($destination) {
            ProductionDestination::Offset->value => $this->offsetPayload($data),
            ProductionDestination::Digital->value => $this->digitalPayload($data),
            ProductionDestination::Outsource->value => $this->outsourcePayload($data),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function enrichSpecificationData(array $data): array
    {
        $payload = $this->payloadFromRequest($data);
        $data['job_sheet_payload'] = $payload;

        if (! filled($data['description'] ?? null) && is_array($payload)) {
            $data['description'] = $payload['product_description']
                ?? $payload['description']
                ?? null;
        }

        if (! filled($data['default_unit_price'] ?? null) && is_array($payload)) {
            $data['default_unit_price'] = $payload['price'] ?? $payload['selling_price'] ?? null;
        }

        if (! filled($data['production_notes'] ?? null) && is_array($payload)) {
            $data['production_notes'] = $payload['production_notes'] ?? $payload['notes'] ?? null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function hydrateOrderPayload(array $payload, CustomerPrintSpecification $specification): array
    {
        if (empty($payload['production_destination']) && $specification->production_destination) {
            $payload['production_destination'] = $specification->production_destination->value;
        }

        $sheet = is_array($specification->job_sheet_payload) ? $specification->job_sheet_payload : [];

        if ($sheet === []) {
            return $payload;
        }

        $destination = $this->destinationValue(
            $payload['production_destination'] ?? $specification->production_destination,
        );
        $kind = $sheet['kind'] ?? $destination;

        if ($destination === ProductionDestination::Offset->value && $kind === 'offset') {
            $payload['job_sheet'] = $this->mergeForm(
                OffsetJobSheetService::formFromPayload($sheet),
                is_array($payload['job_sheet'] ?? null) ? $payload['job_sheet'] : [],
            );
        }

        if ($destination === ProductionDestination::Digital->value && $kind === 'digital') {
            $payload['digital'] = $this->mergeForm(
                DigitalSpecificationService::formFromPayload($sheet),
                is_array($payload['digital'] ?? null) ? $payload['digital'] : [],
            );
        }

        if ($destination === ProductionDestination::Outsource->value && $kind === 'outsource') {
            $payload['outsource'] = $this->mergeForm(
                OutsourceSpecificationService::formFromPayload($sheet),
                is_array($payload['outsource'] ?? null) ? $payload['outsource'] : [],
            );
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function orderContextFields(CustomerPrintSpecification $specification): array
    {
        $payload = is_array($specification->job_sheet_payload) ? $specification->job_sheet_payload : [];
        $kind = $payload['kind'] ?? $specification->production_destination?->value;

        return [
            'production_destination' => $specification->production_destination?->value,
            'job_sheet' => $kind === 'offset' ? OffsetJobSheetService::formFromPayload($payload) : null,
            'digital' => $kind === 'digital' ? DigitalSpecificationService::formFromPayload($payload) : null,
            'outsource' => $kind === 'outsource' ? OutsourceSpecificationService::formFromPayload($payload) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function offsetPayload(array $data): array
    {
        $sheet = is_array($data['job_sheet'] ?? null) ? $data['job_sheet'] : [];

        return [
            'kind' => 'offset',
            ...$this->offset->payloadFromSheet($sheet),
            'product_description' => $this->nullableString($sheet['product_description'] ?? null),
            'size' => $this->nullableString($sheet['size'] ?? null),
            'ups' => isset($sheet['ups']) && $sheet['ups'] !== '' ? (int) $sheet['ups'] : null,
            'binding_type' => $this->nullableString($sheet['binding_type'] ?? null),
            'production_notes' => $this->nullableString($sheet['production_notes'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function digitalPayload(array $data): array
    {
        $sheet = is_array($data['digital'] ?? null) ? $data['digital'] : [];

        return [
            'kind' => 'digital',
            'description' => $this->nullableString($sheet['description'] ?? null),
            'paper_type' => $this->nullableString($sheet['paper_type'] ?? null),
            'ups' => isset($sheet['ups']) && $sheet['ups'] !== '' ? (int) $sheet['ups'] : null,
            'sheets' => isset($sheet['sheets']) && $sheet['sheets'] !== '' ? (int) $sheet['sheets'] : null,
            'finishing' => $this->nullableString($sheet['finishing'] ?? null),
            'price' => $this->nullableNumber($sheet['price'] ?? null),
            'due_date' => $this->nullableString($sheet['due_date'] ?? null),
            'payment_status' => $this->nullableString($sheet['payment_status'] ?? null),
            'status' => $this->nullableString($sheet['status'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function outsourcePayload(array $data): array
    {
        $sheet = is_array($data['outsource'] ?? null) ? $data['outsource'] : [];

        return [
            'kind' => 'outsource',
            'description' => $this->nullableString($sheet['description'] ?? null),
            'printing_type' => $this->nullableString($sheet['printing_type'] ?? null),
            'vendor_id' => filled($sheet['vendor_id'] ?? null) ? (int) $sheet['vendor_id'] : null,
            'cost' => $this->nullableNumber($sheet['cost'] ?? null),
            'selling_price' => $this->nullableNumber($sheet['selling_price'] ?? null),
            'payment_status' => $this->nullableString($sheet['payment_status'] ?? null),
            'status' => $this->nullableString($sheet['status'] ?? null),
            'date_sent_out' => $this->nullableString($sheet['date_sent_out'] ?? null),
            'due_at' => $this->nullableString($sheet['due_at'] ?? null),
            'notes' => $this->nullableString($sheet['notes'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $defaults
     * @param  array<string, mixed>  $submitted
     * @return array<string, mixed>
     */
    protected function mergeForm(array $defaults, array $submitted): array
    {
        foreach ($submitted as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                continue;
            }

            $defaults[$key] = $value;
        }

        return $defaults;
    }

    protected function destinationValue(mixed $destination): ?string
    {
        if ($destination instanceof ProductionDestination) {
            return $destination->value;
        }

        if (! is_string($destination) || $destination === '') {
            return null;
        }

        return $destination;
    }

    protected function nullableString(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    protected function nullableNumber(mixed $value): ?float
    {
        if (! filled($value) && $value !== 0 && $value !== '0') {
            return null;
        }

        return round((float) $value, 2);
    }
}
