<?php

namespace App\Support\Production;

use App\Models\Production\ProductionSpecification;

class ProductionSpecificationPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(ProductionSpecification $spec): array
    {
        return [
            'id' => $spec->id,
            'has_specification' => true,
            'approval_status' => $spec->approval_status?->value,
            'approval_status_label' => $spec->approval_status?->label(),
            'approval_status_variant' => $spec->approval_status?->badgeVariant(),
            'sections' => [
                'product' => $this->productSection($spec),
                'dimensions' => $this->dimensionsSection($spec),
                'materials' => $this->materialsSection($spec),
                'print' => $this->printSection($spec),
                'finishing' => $this->finishingSection($spec),
                'imposition' => $this->impositionSection($spec),
                'artwork' => $this->artworkSection($spec),
                'notes' => $this->notesSection($spec),
                'job_sheet' => $this->jobSheetSection($spec),
                'outsource' => $this->outsourceSection($spec),
                'digital' => $this->digitalSection($spec),
            ],
            'links' => [
                'sales_order_id' => $spec->sales_order_id,
                'sales_order_item_id' => $spec->sales_order_item_id,
                'production_job_card_id' => $spec->production_job_card_id,
                'customer_id' => $spec->customer_id,
            ],
            'updated_at' => $spec->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function presentSummary(ProductionSpecification $spec): array
    {
        return [
            'has_specification' => true,
            'production_type' => $spec->production_type?->value,
            'production_type_label' => $spec->production_type?->value
                ? str_replace('_', ' ', ucfirst($spec->production_type->value))
                : null,
            'product_description' => $spec->product_description,
            'quantity' => $spec->quantity !== null ? (float) $spec->quantity : null,
            'unit' => $spec->unit,
            'size' => $spec->size,
            'paper' => $spec->paperInventoryItem?->item_name
                ?? (is_array($spec->job_sheet_payload) ? ($spec->job_sheet_payload['paper_stock'] ?? $spec->job_sheet_payload['paper_type'] ?? null) : null),
            'ink' => (is_array($spec->job_sheet_payload) ? ($spec->job_sheet_payload['ink'] ?? null) : null)
                ?? $spec->ink_type?->label()
                ?? $spec->colour_mode,
            'binding' => $spec->binding_type,
            'finishing' => $spec->finishing_type,
            'ups' => $spec->ups,
            'estimated_sheets' => $spec->estimated_sheets,
            'approval_status_label' => $spec->approval_status?->label(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function emptyState(): array
    {
        return [
            'has_specification' => false,
            'message' => __('No structured production specification yet.'),
            'sections' => [],
        ];
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function productSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Production type') => $spec->production_type?->value
                ? str_replace('_', ' ', ucfirst($spec->production_type->value))
                : null,
            __('Description') => $spec->product_description,
            __('Quantity') => $spec->quantity !== null ? number_format((float) $spec->quantity, 0) : null,
            __('Unit') => $spec->unit,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function dimensionsSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Size') => $spec->size,
            __('Finished size') => $spec->finished_size,
            __('Sheet size') => $spec->sheet_size,
            __('Orientation') => $spec->orientation,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function materialsSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Paper') => $spec->paperInventoryItem?->item_name,
            __('Material') => $spec->materialInventoryItem?->item_name,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function printSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Ink type') => $spec->ink_type?->label(),
            __('Ink profile') => $spec->inkProfile?->name,
            __('Colour mode') => $spec->colour_mode,
            __('Sides') => $spec->sides,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function finishingSection(ProductionSpecification $spec): array
    {
        $options = collect([
            'lamination' => __('Lamination'),
            'foiling' => __('Foiling'),
            'spot_uv' => __('Spot UV'),
            'embossing' => __('Embossing'),
            'debossing' => __('Debossing'),
            'die_cutting' => __('Die cutting'),
            'creasing' => __('Creasing'),
            'perforation' => __('Perforation'),
            'numbering_required' => __('Numbering'),
            'eyelets' => __('Eyelets'),
        ])->filter(fn ($label, $key) => (bool) $spec->{$key})->values()->all();

        return $this->fields([
            __('Binding') => $spec->binding_type,
            __('Finishing type') => $spec->finishing_type,
            __('Finishing options') => $options !== [] ? implode(', ', $options) : null,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function impositionSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Ups') => $spec->ups,
            __('Estimated sheets') => $spec->estimated_sheets,
            __('Waste allowance') => $spec->waste_allowance_percent !== null
                ? number_format((float) $spec->waste_allowance_percent, 1).'%'
                : null,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function artworkSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Artwork reference') => $spec->artwork_reference,
            __('Artwork version') => $spec->artwork_version,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function notesSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Production notes') => $spec->production_notes,
            __('Delivery notes') => $spec->delivery_notes,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function jobSheetSection(ProductionSpecification $spec): array
    {
        $sheet = is_array($spec->job_sheet_payload) ? $spec->job_sheet_payload : [];

        if (in_array($sheet['kind'] ?? null, ['outsource', 'digital'], true)) {
            return [];
        }

        $colours = is_array($sheet['ncr_colours'] ?? null) ? $sheet['ncr_colours'] : [];
        $ncr = collect([
            filled($colours['orig'] ?? null) ? __('ORIG').': '.$colours['orig'] : null,
            filled($colours['dup'] ?? null) ? __('DUP').': '.$colours['dup'] : null,
            filled($colours['tri'] ?? null) ? __('TRI').': '.$colours['tri'] : null,
            filled($colours['quad'] ?? null) ? __('QUAD').': '.$colours['quad'] : null,
        ])->filter()->implode(' · ');

        $materials = collect($sheet['material_rows'] ?? [])
            ->filter(fn ($row) => is_array($row) && filled($row['paper_type'] ?? null))
            ->map(function (array $row) {
                $qty = collect([$row['sheets_a4_a3'] ?? null, $row['sheets_a1'] ?? null])
                    ->filter(fn ($value) => filled($value))
                    ->implode(' / ');

                return trim($row['paper_type'].($qty !== '' ? ' ('.$qty.')' : ''));
            })
            ->filter()
            ->implode(', ');

        return collect($this->fields([
            __('Paper colour') => $ncr !== '' ? $ncr : null,
            __('Paper stock') => $sheet['paper_stock'] ?? null,
            __('Ink') => $sheet['ink'] ?? null,
            __('Number') => $sheet['serial_number'] ?? null,
            __('Pages / pad') => $sheet['pages_per_pad'] ?? null,
            __('Material requisition') => $materials !== '' ? $materials : null,
        ]))->filter(fn (array $field) => filled($field['value']))->values()->all();
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function outsourceSection(ProductionSpecification $spec): array
    {
        $sheet = is_array($spec->job_sheet_payload) ? $spec->job_sheet_payload : [];

        if (($sheet['kind'] ?? null) !== 'outsource') {
            return [];
        }

        $payment = $sheet['payment_status'] ?? null;
        $status = $sheet['status'] ?? null;

        return collect($this->fields([
            __('Type of printing') => filled($sheet['printing_type'] ?? null)
                ? str_replace('_', ' ', ucfirst((string) $sheet['printing_type']))
                : null,
            __('Service provider') => $sheet['vendor_name'] ?? null,
            __('Cost') => isset($sheet['cost']) && $sheet['cost'] !== null
                ? number_format((float) $sheet['cost'], 2)
                : null,
            __('Selling price') => isset($sheet['selling_price']) && $sheet['selling_price'] !== null
                ? number_format((float) $sheet['selling_price'], 2)
                : null,
            __('Payment status') => $payment ? (OutsourceSpecificationService::paymentStatuses()[$payment] ?? $payment) : null,
            __('Status') => $status ? (OutsourceSpecificationService::jobStatuses()[$status] ?? $status) : null,
            __('Date sent out') => $sheet['date_sent_out'] ?? null,
            __('Due date / time') => $sheet['due_at'] ?? null,
        ]))->filter(fn (array $field) => filled($field['value']))->values()->all();
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function digitalSection(ProductionSpecification $spec): array
    {
        $sheet = is_array($spec->job_sheet_payload) ? $spec->job_sheet_payload : [];

        if (($sheet['kind'] ?? null) !== 'digital') {
            return [];
        }

        $payment = $sheet['payment_status'] ?? null;
        $status = $sheet['status'] ?? null;

        return collect($this->fields([
            __('Paper type') => $sheet['paper_type'] ?? null,
            __('No. of ups') => $sheet['ups'] ?? $spec->ups,
            __('No. of sheets') => $sheet['sheets'] ?? $spec->estimated_sheets,
            __('Finishing') => $sheet['finishing'] ?? $spec->finishing_type,
            __('Price') => isset($sheet['price']) && $sheet['price'] !== null
                ? number_format((float) $sheet['price'], 2)
                : null,
            __('Amount') => isset($sheet['amount']) && $sheet['amount'] !== null
                ? number_format((float) $sheet['amount'], 2)
                : null,
            __('Due date') => $sheet['due_date'] ?? null,
            __('Payment status') => $payment ? (DigitalSpecificationService::paymentStatuses()[$payment] ?? $payment) : null,
            __('Status') => $status ? (DigitalSpecificationService::jobStatuses()[$status] ?? $status) : null,
        ]))->filter(fn (array $field) => filled($field['value']))->values()->all();
    }

    /**
     * @param  array<string, mixed>  $pairs
     * @return list<array{label: string, value: mixed}>
     */
    protected function fields(array $pairs): array
    {
        return collect($pairs)
            ->map(fn ($value, $label) => ['label' => $label, 'value' => $value])
            ->values()
            ->all();
    }
}
