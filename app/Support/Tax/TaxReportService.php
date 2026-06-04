<?php

namespace App\Support\Tax;

use App\Enums\TaxCategoryType;
use App\Enums\TaxDirection;
use App\Models\Tax\TaxTransaction;

class TaxReportService
{
    public function __construct(
        protected TaxTransactionRecorder $recorder,
    ) {}

    /**
     * @param  array{company_id: int, from_date?: string, to_date?: string, tax_period_id?: int}  $filters
     */
    public function vatSummary(array $filters): array
    {
        $rows = $this->vatTransactions($filters);

        $output = $rows->where('direction', TaxDirection::Output)->sum(fn ($r) => (float) $r->tax_amount);
        $input = $rows->where('direction', TaxDirection::Input)->sum(fn ($r) => (float) $r->tax_amount);
        $wht = $this->withholdingTotal($filters);

        return [
            'from_date' => $filters['from_date'] ?? null,
            'to_date' => $filters['to_date'] ?? null,
            'output_vat' => round($output, 2),
            'input_vat' => round($input, 2),
            'withholding_tax' => round($wht, 2),
            'net_liability' => round($output - $input - $wht, 2),
            'by_code' => $this->groupByCode($rows),
        ];
    }

    public function outputVat(array $filters): array
    {
        return $this->directionReport($filters, TaxDirection::Output);
    }

    public function inputVat(array $filters): array
    {
        return $this->directionReport($filters, TaxDirection::Input);
    }

    public function taxLiability(array $filters): array
    {
        $summary = $this->vatSummary($filters);

        return [
            'from_date' => $summary['from_date'],
            'to_date' => $summary['to_date'],
            'output_vat' => $summary['output_vat'],
            'input_vat' => $summary['input_vat'],
            'withholding_tax' => $summary['withholding_tax'],
            'net_liability' => $summary['net_liability'],
        ];
    }

    /**
     * @param  array{company_id: int, from_date?: string, to_date?: string}  $filters
     */
    protected function directionReport(array $filters, TaxDirection $direction): array
    {
        $rows = $this->vatTransactions($filters)->where('direction', $direction);

        return [
            'direction' => $direction->value,
            'label' => $direction->label(),
            'from_date' => $filters['from_date'] ?? null,
            'to_date' => $filters['to_date'] ?? null,
            'total_tax' => round($rows->sum(fn ($r) => (float) $r->tax_amount), 2),
            'total_taxable' => round($rows->sum(fn ($r) => (float) $r->taxable_amount), 2),
            'rows' => $rows->map(fn ($r) => [
                'document_date' => $r->document_date->toDateString(),
                'document_number' => $r->document_number,
                'tax_code' => $r->taxCode?->code,
                'tax_name' => $r->taxCode?->name,
                'taxable_amount' => (float) $r->taxable_amount,
                'tax_amount' => (float) $r->tax_amount,
                'source_type' => $r->source_type,
            ])->values()->all(),
        ];
    }

    protected function vatTransactions(array $filters)
    {
        $query = TaxTransaction::query()
            ->where('company_id', $filters['company_id'])
            ->whereHas('taxCategory', fn ($q) => $q->where('type', TaxCategoryType::Vat->value))
            ->with(['taxCode', 'taxCategory']);

        if (! empty($filters['from_date'])) {
            $query->whereDate('document_date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('document_date', '<=', $filters['to_date']);
        }

        if (! empty($filters['tax_period_id'])) {
            $query->where('tax_period_id', $filters['tax_period_id']);
        }

        return $query->orderBy('document_date')->get();
    }

    protected function withholdingTotal(array $filters): float
    {
        $query = TaxTransaction::query()
            ->where('company_id', $filters['company_id'])
            ->whereHas('taxCategory', fn ($q) => $q->where('type', TaxCategoryType::WithholdingTax->value));

        if (! empty($filters['from_date'])) {
            $query->whereDate('document_date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('document_date', '<=', $filters['to_date']);
        }

        return (float) $query->sum('tax_amount');
    }

    protected function groupByCode($rows): array
    {
        return $rows->groupBy('tax_code_id')->map(function ($group) {
            $first = $group->first();

            return [
                'tax_code' => $first->taxCode?->code,
                'tax_name' => $first->taxCode?->name,
                'direction' => $first->direction->value,
                'taxable_amount' => round($group->sum(fn ($r) => (float) $r->taxable_amount), 2),
                'tax_amount' => round($group->sum(fn ($r) => (float) $r->tax_amount), 2),
            ];
        })->values()->all();
    }
}
