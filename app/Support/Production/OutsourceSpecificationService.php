<?php

namespace App\Support\Production;

use App\Enums\ProductionDestination;
use App\Enums\ProductionSpecificationApprovalStatus;
use App\Enums\ProductionType;
use App\Enums\VendorStatus;
use App\Models\Procurement\Vendor;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionSpecification;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class OutsourceSpecificationService
{
    public function __construct(
        protected ProductionSpecificationService $specifications,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function orderValidationRules(): array
    {
        $outsource = ProductionDestination::Outsource->value;

        return [
            'outsource' => ['required_if:production_destination,'.$outsource, 'array'],
            'outsource.description' => ['required_if:production_destination,'.$outsource, 'nullable', 'string', 'max:500'],
            'outsource.printing_type' => ['required_if:production_destination,'.$outsource, 'nullable', Rule::enum(ProductionType::class)],
            'outsource.vendor_id' => ['required_if:production_destination,'.$outsource, 'nullable', 'integer', 'exists:vendors,id'],
            'outsource.cost' => ['required_if:production_destination,'.$outsource, 'nullable', 'numeric', 'min:0'],
            'outsource.selling_price' => ['nullable', 'numeric', 'min:0'],
            'outsource.payment_status' => ['nullable', 'string', Rule::in(array_keys(self::paymentStatuses()))],
            'outsource.status' => ['nullable', 'string', Rule::in(array_keys(self::jobStatuses()))],
            'outsource.date_sent_out' => ['nullable', 'date'],
            'outsource.due_at' => ['nullable', 'date'],
            'outsource.notes' => ['nullable', 'string', 'max:10000'],
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

        $sheet = is_array($payload['outsource'] ?? null) ? $payload['outsource'] : [];
        $quantity = (float) ($payload['quantity'] ?? 0);
        $selling = $sheet['selling_price'] ?? null;

        if (! filled($payload['unit_price'] ?? null) && filled($selling) && $quantity > 0) {
            $payload['unit_price'] = round(((float) $selling) / $quantity, 2);
        }

        if (! filled($payload['required_date'] ?? null) && filled($sheet['due_at'] ?? null)) {
            $payload['required_date'] = Carbon::parse($sheet['due_at'])->toDateString();
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
            return $destination === ProductionDestination::Outsource;
        }

        return $destination === ProductionDestination::Outsource->value;
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

        if ($existing) {
            return $existing;
        }

        $user = User::query()->find($createdBy);

        if (! $user) {
            return null;
        }

        return $this->specifications->createForSalesOrderItem(
            $item,
            $this->specificationAttributes($payload, $order),
            $user,
        );
    }

    public function applyToJobCard(ProductionJobCard $jobCard): void
    {
        $spec = $jobCard->productionSpecification
            ?? $this->specifications->findForJobCard($jobCard);

        if (! $spec) {
            return;
        }

        $sheet = is_array($spec->job_sheet_payload) ? $spec->job_sheet_payload : [];

        if (($sheet['kind'] ?? null) !== 'outsource') {
            return;
        }

        $updates = array_filter([
            'production_type' => $sheet['printing_type'] ?? null,
            'outsource_vendor_id' => $sheet['vendor_id'] ?? null,
            'outsource_issue_date' => $sheet['date_sent_out'] ?? null,
            'outsource_expected_return' => filled($sheet['due_at'] ?? null)
                ? Carbon::parse($sheet['due_at'])->toDateString()
                : null,
            'outsource_quoted_cost' => $this->quotedCost($sheet, $spec->quantity),
            'outsource_notes' => $sheet['notes'] ?? $spec->production_notes,
        ], fn ($value) => $value !== null && $value !== '');

        if ($updates === []) {
            return;
        }

        $jobCard->update($updates);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function specificationAttributes(array $payload, SalesOrder $order): array
    {
        $sheet = is_array($payload['outsource'] ?? null) ? $payload['outsource'] : [];
        $description = trim((string) ($sheet['description'] ?? ''));
        $notes = trim((string) ($sheet['notes'] ?? ''));
        $printingType = $sheet['printing_type'] ?? ProductionType::Mixed->value;
        $vendor = filled($sheet['vendor_id'] ?? null)
            ? Vendor::query()->find($sheet['vendor_id'])
            : null;

        return [
            'production_type' => $printingType,
            'product_description' => $description !== ''
                ? $description
                : ($order->items->first()?->item_name ?? $order->customerPrintSpecification?->name),
            'quantity' => $payload['quantity'] ?? $order->items->first()?->quantity,
            'production_notes' => $notes !== '' ? $notes : null,
            'job_sheet_payload' => [
                'kind' => 'outsource',
                'printing_type' => $printingType,
                'vendor_id' => $vendor?->id,
                'vendor_name' => $vendor?->vendor_name,
                'cost' => $this->nullableNumber($sheet['cost'] ?? null),
                'selling_price' => $this->nullableNumber($sheet['selling_price'] ?? null),
                'payment_status' => $this->nullableString($sheet['payment_status'] ?? null),
                'status' => $this->nullableString($sheet['status'] ?? null),
                'date_sent_out' => $this->nullableString($sheet['date_sent_out'] ?? null),
                'due_at' => $this->nullableString($sheet['due_at'] ?? null),
                'notes' => $notes !== '' ? $notes : null,
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
            'printing_type' => (string) ($old['printing_type'] ?? ''),
            'vendor_id' => (string) ($old['vendor_id'] ?? ''),
            'cost' => (string) ($old['cost'] ?? ''),
            'selling_price' => (string) ($old['selling_price'] ?? ''),
            'payment_status' => (string) ($old['payment_status'] ?? ''),
            'status' => (string) ($old['status'] ?? ''),
            'date_sent_out' => (string) ($old['date_sent_out'] ?? ''),
            'due_at' => (string) ($old['due_at'] ?? ''),
            'notes' => (string) ($old['notes'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function formFromPayload(array $payload = []): array
    {
        return self::emptyForm([
            'description' => $payload['description'] ?? '',
            'printing_type' => $payload['printing_type'] ?? '',
            'vendor_id' => $payload['vendor_id'] ?? '',
            'cost' => isset($payload['cost']) && $payload['cost'] !== null && $payload['cost'] !== ''
                ? (string) $payload['cost']
                : '',
            'selling_price' => isset($payload['selling_price']) && $payload['selling_price'] !== null && $payload['selling_price'] !== ''
                ? (string) $payload['selling_price']
                : '',
            'payment_status' => $payload['payment_status'] ?? '',
            'status' => $payload['status'] ?? '',
            'date_sent_out' => $payload['date_sent_out'] ?? '',
            'due_at' => $payload['due_at'] ?? '',
            'notes' => $payload['notes'] ?? '',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function paymentStatuses(): array
    {
        return [
            'unpaid' => __('Unpaid'),
            'invoiced' => __('Invoiced'),
            'paid' => __('Paid'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function jobStatuses(): array
    {
        return [
            'pending' => __('Pending'),
            'sent' => __('Sent out'),
            'in_progress' => __('In progress'),
            'complete' => __('Complete'),
        ];
    }

    /**
     * @return Collection<int, Vendor>
     */
    public function productionVendors(): Collection
    {
        return Vendor::query()
            ->forTenant()
            ->where('is_production_vendor', true)
            ->where('status', VendorStatus::Active)
            ->orderBy('vendor_name')
            ->get(['id', 'vendor_name', 'vendor_code']);
    }

    protected function quotedCost(array $sheet, mixed $quantity): ?float
    {
        $cost = $this->nullableNumber($sheet['cost'] ?? null);

        if ($cost === null) {
            return null;
        }

        $qty = (float) $quantity;

        if ($qty > 0) {
            return round($cost * $qty, 2);
        }

        return $cost;
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
