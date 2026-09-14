<?php

namespace App\Support\Production;

use App\Enums\ProductionDestination;
use App\Enums\ProductionSpecificationApprovalStatus;
use App\Enums\ProductionType;
use App\Models\Production\ProductionSpecification;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Validation\Rule;

class DigitalSpecificationService
{
    public function __construct(
        protected ProductionSpecificationService $specifications,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function orderValidationRules(): array
    {
        $digital = ProductionDestination::Digital->value;

        return [
            'digital' => ['required_if:production_destination,'.$digital, 'array'],
            'digital.description' => ['required_if:production_destination,'.$digital, 'nullable', 'string', 'max:500'],
            'digital.paper_type' => ['required_if:production_destination,'.$digital, 'nullable', 'string', 'max:80'],
            'digital.ups' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'digital.sheets' => ['nullable', 'integer', 'min:0', 'max:9999999'],
            'digital.finishing' => ['nullable', 'string', 'max:60'],
            'digital.price' => ['nullable', 'numeric', 'min:0'],
            'digital.due_date' => ['nullable', 'date'],
            'digital.payment_status' => ['nullable', 'string', Rule::in(array_keys(self::paymentStatuses()))],
            'digital.status' => ['nullable', 'string', Rule::in(array_keys(self::jobStatuses()))],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function applyCommercialOverrides(array $payload): array
    {
        if (! $this->shouldCapture($payload)) {
            return $payload;
        }

        $sheet = is_array($payload['digital'] ?? null) ? $payload['digital'] : [];

        $jobFields = app(PrintSpecificationJobFields::class);
        if ($jobFields->isMissingAmount($payload['unit_price'] ?? null) && ! $jobFields->isMissingAmount($sheet['price'] ?? null)) {
            $payload['unit_price'] = round((float) $sheet['price'], 2);
        }

        if (! filled($payload['required_date'] ?? null) && filled($sheet['due_date'] ?? null)) {
            $payload['required_date'] = $sheet['due_date'];
        }

        $quantity = (float) ($payload['quantity'] ?? 0);
        if ($quantity <= 1) {
            $finished = self::finishedQuantityFromSheet($sheet);
            if ($finished > 1) {
                $payload['quantity'] = $finished;
                $quantity = $finished;
            }
        }
        $ups = isset($sheet['ups']) && $sheet['ups'] !== '' ? (int) $sheet['ups'] : null;
        $sheets = isset($sheet['sheets']) && $sheet['sheets'] !== '' ? (int) $sheet['sheets'] : null;
        $derived = ProductionImpositionCalculator::estimateSheets($quantity, $ups, $sheets);

        if ($derived !== null) {
            $payload['digital']['sheets'] = $derived;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function shouldCapture(array $payload): bool
    {
        $destination = $payload['production_destination'] ?? null;

        if ($destination instanceof ProductionDestination) {
            return $destination === ProductionDestination::Digital;
        }

        return $destination === ProductionDestination::Digital->value;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function attachToOrder(SalesOrder $order, array $payload, int $createdBy): ?ProductionSpecification
    {
        if (! $this->shouldCapture($payload)) {
            return null;
        }

        $order->loadMissing(['items']);
        $item = $order->items->first();

        if (! $item) {
            return null;
        }

        $existing = $this->specifications->findForSalesOrderItem($item);
        $user = User::query()->find($createdBy);

        if (! $user) {
            return $existing;
        }

        if ($existing) {
            return $this->specifications->update(
                $existing,
                $this->specificationAttributes($payload, $order),
                $user,
            );
        }

        return $this->specifications->createForSalesOrderItem(
            $item,
            $this->specificationAttributes($payload, $order),
            $user,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function specificationAttributes(array $payload, SalesOrder $order): array
    {
        $sheet = is_array($payload['digital'] ?? null) ? $payload['digital'] : [];
        $description = trim((string) ($sheet['description'] ?? ''));
        $paperType = trim((string) ($sheet['paper_type'] ?? ''));
        $finishing = trim((string) ($sheet['finishing'] ?? ''));
        $ups = isset($sheet['ups']) && $sheet['ups'] !== '' ? (int) $sheet['ups'] : null;
        $quantity = $payload['quantity'] ?? $order->items->first()?->quantity;
        $sheets = isset($sheet['sheets']) && $sheet['sheets'] !== '' ? (int) $sheet['sheets'] : null;
        $sheets = ProductionImpositionCalculator::estimateSheets($quantity, $ups, $sheets);
        $price = $this->nullableNumber($sheet['price'] ?? $payload['unit_price'] ?? $order->items->first()?->unit_price);

        return [
            'production_type' => ProductionType::Digital->value,
            'product_description' => $description !== ''
                ? $description
                : ($order->items->first()?->item_name ?? $order->customerPrintSpecification?->productLabel()),
            'quantity' => $quantity,
            'ups' => $ups,
            'estimated_sheets' => $sheets,
            'finishing_type' => $finishing !== '' && strcasecmp($finishing, 'N/A') !== 0 ? $finishing : null,
            'job_sheet_payload' => [
                'kind' => 'digital',
                'description' => $description !== '' ? $description : null,
                'paper_type' => $paperType !== '' ? $paperType : null,
                'ups' => $ups,
                'sheets' => $sheets,
                'finishing' => $finishing !== '' ? $finishing : null,
                'price' => $price,
                'amount' => $this->amount($quantity, $price),
                'due_date' => $this->nullableString($sheet['due_date'] ?? null),
                'payment_status' => $this->nullableString($sheet['payment_status'] ?? null),
                'status' => $this->nullableString($sheet['status'] ?? null),
            ],
            'approval_status' => ProductionSpecificationApprovalStatus::Approved->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function emptyForm(array $old = []): array
    {
        return [
            'description' => (string) ($old['description'] ?? ''),
            'paper_type' => (string) ($old['paper_type'] ?? ''),
            'ups' => (string) ($old['ups'] ?? ''),
            'sheets' => (string) ($old['sheets'] ?? ''),
            'finishing' => (string) ($old['finishing'] ?? ''),
            'price' => (string) ($old['price'] ?? ''),
            'due_date' => (string) ($old['due_date'] ?? ''),
            'payment_status' => (string) ($old['payment_status'] ?? ''),
            'status' => (string) ($old['status'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function formFromPayload(array $payload = []): array
    {
        if (isset($payload['digital']) && is_array($payload['digital'])) {
            $payload = array_merge($payload['digital'], $payload);
        }

        $price = $payload['price'] ?? $payload['unit_price'] ?? '';

        return self::emptyForm([
            'description' => $payload['description'] ?? $payload['product_description'] ?? '',
            'paper_type' => $payload['paper_type'] ?? '',
            'ups' => isset($payload['ups']) && $payload['ups'] !== null && $payload['ups'] !== ''
                ? (string) $payload['ups']
                : '',
            'sheets' => isset($payload['sheets']) && $payload['sheets'] !== null && $payload['sheets'] !== ''
                ? (string) $payload['sheets']
                : '',
            'finishing' => $payload['finishing'] ?? '',
            'price' => isset($price) && $price !== null && $price !== ''
                ? (string) $price
                : '',
            'due_date' => $payload['due_date'] ?? '',
            'payment_status' => $payload['payment_status'] ?? '',
            'status' => $payload['status'] ?? '',
        ]);
    }

    /**
     * @return list<string>
     */
    public static function paperTypes(): array
    {
        return [
            'Adestor',
            'Art 130',
            'Art 150',
            'Art 250',
            'Manilla 300gsm',
            'Bond 80gsm',
            'Sticker',
        ];
    }

    /**
     * @return list<string>
     */
    public static function finishingOptions(): array
    {
        return ['UV', 'Gloss', 'Matt', 'N/A'];
    }

    /**
     * @return array<string, string>
     */
    public static function paymentStatuses(): array
    {
        return OutsourceSpecificationService::paymentStatuses();
    }

    /**
     * @return array<string, string>
     */
    public static function jobStatuses(): array
    {
        return [
            'pending' => __('Pending'),
            'in_progress' => __('In progress'),
            'order_complete' => __('Order complete'),
        ];
    }

    /**
     * Finished pieces implied by a digital job sheet (sheets × ups, or sheets when ups is blank).
     *
     * @param  array<string, mixed>  $sheet
     */
    public static function finishedQuantityFromSheet(array $sheet): float
    {
        $ups = isset($sheet['ups']) && $sheet['ups'] !== '' && $sheet['ups'] !== null
            ? (int) $sheet['ups']
            : 0;
        $sheets = isset($sheet['sheets']) && $sheet['sheets'] !== '' && $sheet['sheets'] !== null
            ? (int) $sheet['sheets']
            : 0;

        if ($sheets > 1 && $ups > 1) {
            return (float) ($sheets * $ups);
        }

        if ($sheets > 1) {
            return (float) $sheets;
        }

        return 0.0;
    }

    protected function amount(mixed $quantity, mixed $price): ?float
    {
        if (! filled($price) && $price !== 0 && $price !== '0') {
            return null;
        }

        return round(((float) $quantity) * (float) $price, 2);
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
