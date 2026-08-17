<?php

namespace App\Support\Production;

use App\Enums\ProductionDestination;
use App\Enums\ProductionSpecificationApprovalStatus;
use App\Enums\ProductionType;
use App\Models\Production\ProductionSpecification;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Support\Arr;

class OffsetJobSheetService
{
    public function __construct(
        protected ProductionSpecificationService $specifications,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function orderValidationRules(): array
    {
        $offset = ProductionDestination::Offset->value;

        return [
            'job_sheet' => ['required_if:production_destination,'.$offset, 'array'],
            'job_sheet.product_description' => ['required_if:production_destination,'.$offset, 'nullable', 'string', 'max:500'],
            'job_sheet.paper_colour_orig' => ['nullable', 'string', 'max:40'],
            'job_sheet.paper_colour_dup' => ['nullable', 'string', 'max:40'],
            'job_sheet.paper_colour_tri' => ['nullable', 'string', 'max:40'],
            'job_sheet.paper_colour_quad' => ['nullable', 'string', 'max:40'],
            'job_sheet.paper_stock' => ['required_if:production_destination,'.$offset, 'nullable', 'string', 'max:120'],
            'job_sheet.ink' => ['nullable', 'string', 'max:80'],
            'job_sheet.serial_number' => ['nullable', 'string', 'max:80'],
            'job_sheet.pages_per_pad' => ['nullable', 'string', 'max:40'],
            'job_sheet.size' => ['required_if:production_destination,'.$offset, 'nullable', 'string', 'max:80'],
            'job_sheet.ups' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'job_sheet.binding_type' => ['nullable', 'string', 'max:60'],
            'job_sheet.production_notes' => ['nullable', 'string', 'max:10000'],
            'job_sheet.material_rows' => ['nullable', 'array', 'max:10'],
            'job_sheet.material_rows.*.paper_type' => ['nullable', 'string', 'max:120'],
            'job_sheet.material_rows.*.sheets_a4_a3' => ['nullable', 'string', 'max:40'],
            'job_sheet.material_rows.*.sheets_a1' => ['nullable', 'string', 'max:40'],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function shouldCapture(array $payload): bool
    {
        $destination = $payload['production_destination'] ?? null;

        if ($destination instanceof ProductionDestination) {
            return $destination === ProductionDestination::Offset;
        }

        return $destination === ProductionDestination::Offset->value;
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

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function specificationAttributes(array $payload, SalesOrder $order): array
    {
        $sheet = is_array($payload['job_sheet'] ?? null) ? $payload['job_sheet'] : [];
        $description = trim((string) ($sheet['product_description'] ?? ''));
        $ink = trim((string) ($sheet['ink'] ?? ''));
        $serial = trim((string) ($sheet['serial_number'] ?? ''));
        $pagesPerPad = trim((string) ($sheet['pages_per_pad'] ?? ''));
        $notes = trim((string) ($sheet['production_notes'] ?? ''));

        $attributes = [
            'production_type' => ProductionType::Offset->value,
            'product_description' => $description !== ''
                ? $description
                : ($order->items->first()?->item_name ?? $order->customerPrintSpecification?->productLabel()),
            'quantity' => $payload['quantity'] ?? $order->items->first()?->quantity,
            'size' => $this->nullableString($sheet['size'] ?? null),
            'finished_size' => $this->nullableString($sheet['size'] ?? null),
            'binding_type' => $this->nullableString($sheet['binding_type'] ?? null),
            'ups' => isset($sheet['ups']) && $sheet['ups'] !== '' ? (int) $sheet['ups'] : null,
            'colour_mode' => $ink !== '' ? mb_substr($ink, 0, 40) : null,
            'production_notes' => $notes !== '' ? $notes : null,
            'numbering_required' => $serial !== '',
            'job_sheet_payload' => $this->payloadFromSheet($sheet),
            'approval_status' => ProductionSpecificationApprovalStatus::Approved->value,
        ];

        if ($pagesPerPad !== '' && is_numeric($pagesPerPad)) {
            $attributes['estimated_sheets'] = (int) $pagesPerPad;
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $sheet
     * @return array<string, mixed>
     */
    public function payloadFromSheet(array $sheet): array
    {
        return [
            'kind' => 'offset',
            'ncr_colours' => [
                'orig' => $this->nullableString($sheet['paper_colour_orig'] ?? null) ?? '',
                'dup' => $this->nullableString($sheet['paper_colour_dup'] ?? null) ?? '',
                'tri' => $this->nullableString($sheet['paper_colour_tri'] ?? null) ?? '',
                'quad' => $this->nullableString($sheet['paper_colour_quad'] ?? null) ?? '',
            ],
            'paper_stock' => $this->nullableString($sheet['paper_stock'] ?? null),
            'ink' => $this->nullableString($sheet['ink'] ?? null),
            'serial_number' => $this->nullableString($sheet['serial_number'] ?? null),
            'pages_per_pad' => $this->nullableString($sheet['pages_per_pad'] ?? null),
            'material_rows' => $this->materialRows($sheet['material_rows'] ?? []),
        ];
    }

    /**
     * Empty job-sheet form defaults for create views.
     *
     * @return array<string, mixed>
     */
    public static function emptyForm(array $old = []): array
    {
        $rows = $old['material_rows'] ?? null;

        return [
            'product_description' => (string) ($old['product_description'] ?? ''),
            'paper_colour_orig' => (string) ($old['paper_colour_orig'] ?? ''),
            'paper_colour_dup' => (string) ($old['paper_colour_dup'] ?? ''),
            'paper_colour_tri' => (string) ($old['paper_colour_tri'] ?? ''),
            'paper_colour_quad' => (string) ($old['paper_colour_quad'] ?? ''),
            'paper_stock' => (string) ($old['paper_stock'] ?? ''),
            'ink' => (string) ($old['ink'] ?? ''),
            'serial_number' => (string) ($old['serial_number'] ?? ''),
            'pages_per_pad' => (string) ($old['pages_per_pad'] ?? ''),
            'size' => (string) ($old['size'] ?? ''),
            'ups' => (string) ($old['ups'] ?? ''),
            'binding_type' => (string) ($old['binding_type'] ?? ''),
            'production_notes' => (string) ($old['production_notes'] ?? ''),
            'material_rows' => self::normalizeFormRows(is_array($rows) ? $rows : []),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function formFromPayload(array $payload = []): array
    {
        $colours = is_array($payload['ncr_colours'] ?? null) ? $payload['ncr_colours'] : [];

        return self::emptyForm([
            'product_description' => $payload['product_description'] ?? '',
            'paper_colour_orig' => $colours['orig'] ?? $payload['paper_colour_orig'] ?? '',
            'paper_colour_dup' => $colours['dup'] ?? $payload['paper_colour_dup'] ?? '',
            'paper_colour_tri' => $colours['tri'] ?? $payload['paper_colour_tri'] ?? '',
            'paper_colour_quad' => $colours['quad'] ?? $payload['paper_colour_quad'] ?? '',
            'paper_stock' => $payload['paper_stock'] ?? '',
            'ink' => $payload['ink'] ?? '',
            'serial_number' => $payload['serial_number'] ?? '',
            'pages_per_pad' => $payload['pages_per_pad'] ?? '',
            'size' => $payload['size'] ?? '',
            'ups' => isset($payload['ups']) && $payload['ups'] !== null && $payload['ups'] !== ''
                ? (string) $payload['ups']
                : '',
            'binding_type' => $payload['binding_type'] ?? '',
            'production_notes' => $payload['production_notes'] ?? '',
            'material_rows' => $payload['material_rows'] ?? [],
        ]);
    }

    public static function formFromSpecification(?ProductionSpecification $specification): array
    {
        if (! $specification) {
            return self::emptyForm();
        }

        $payload = is_array($specification->job_sheet_payload) ? $specification->job_sheet_payload : [];
        $form = self::formFromPayload($payload);

        if ($form['product_description'] === '') {
            $form['product_description'] = (string) ($specification->product_description ?? '');
        }
        if ($form['size'] === '') {
            $form['size'] = (string) ($specification->size ?? $specification->finished_size ?? '');
        }
        if ($form['ups'] === '' && $specification->ups !== null) {
            $form['ups'] = (string) $specification->ups;
        }
        if ($form['binding_type'] === '') {
            $form['binding_type'] = (string) ($specification->binding_type ?? '');
        }
        if ($form['ink'] === '') {
            $form['ink'] = (string) ($specification->colour_mode ?? '');
        }
        if ($form['production_notes'] === '') {
            $form['production_notes'] = (string) ($specification->production_notes ?? '');
        }

        return $form;
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return list<array{paper_type: string, sheets_a4_a3: string, sheets_a1: string}>
     */
    protected function materialRows(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->map(function ($row) {
                if (! is_array($row)) {
                    return null;
                }

                $paperType = trim((string) ($row['paper_type'] ?? ''));
                $a4a3 = trim((string) ($row['sheets_a4_a3'] ?? ''));
                $a1 = trim((string) ($row['sheets_a1'] ?? ''));

                if ($paperType === '' && $a4a3 === '' && $a1 === '') {
                    return null;
                }

                return [
                    'paper_type' => $paperType,
                    'sheets_a4_a3' => $a4a3,
                    'sheets_a1' => $a1,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{paper_type: string, sheets_a4_a3: string, sheets_a1: string}>
     */
    protected static function normalizeFormRows(array $rows): array
    {
        $normalized = collect($rows)
            ->map(fn ($row) => [
                'paper_type' => (string) (Arr::get($row, 'paper_type') ?? ''),
                'sheets_a4_a3' => (string) (Arr::get($row, 'sheets_a4_a3') ?? ''),
                'sheets_a1' => (string) (Arr::get($row, 'sheets_a1') ?? ''),
            ])
            ->values()
            ->all();

        if ($normalized === []) {
            $normalized[] = ['paper_type' => '', 'sheets_a4_a3' => '', 'sheets_a1' => ''];
        }

        return array_slice($normalized, 0, 10);
    }

    protected function nullableString(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
