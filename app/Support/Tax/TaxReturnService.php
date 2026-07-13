<?php

namespace App\Support\Tax;

use App\Enums\TaxReturnStatus;
use App\Models\Tax\TaxPeriod;
use App\Models\Tax\TaxReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TaxReturnService
{
    public function __construct(
        protected TaxReportService $reports,
    ) {}

    public function buildDraft(TaxPeriod $period, int $userId): TaxReturn
    {
        $summary = $this->reports->vatSummary([
            'company_id' => $period->company_id,
            'from_date' => $period->start_date->toDateString(),
            'to_date' => $period->end_date->toDateString(),
            'tax_period_id' => $period->id,
        ]);

        return TaxReturn::query()->updateOrCreate(
            [
                'company_id' => $period->company_id,
                'tax_period_id' => $period->id,
                'return_type' => 'vat',
            ],
            [
                'return_number' => 'TR-'.$period->code,
                'status' => TaxReturnStatus::Draft,
                'output_tax' => $summary['output_vat'],
                'input_tax' => $summary['input_vat'],
                'withholding_tax' => $summary['withholding_tax'],
                'net_liability' => $summary['net_liability'],
            ],
        );
    }

    public function file(TaxReturn $taxReturn, int $userId): TaxReturn
    {
        if ($taxReturn->status === TaxReturnStatus::Filed) {
            throw ValidationException::withMessages([
                'tax_return' => __('This tax return is already filed.'),
            ]);
        }

        return DB::transaction(function () use ($taxReturn, $userId) {
            $package = $this->buildFilingPackage($taxReturn);
            $path = 'tax-returns/'.$taxReturn->company_id.'/'.$taxReturn->return_number.'-'.now()->format('YmdHis').'.json';
            Storage::disk('local')->put($path, json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $taxReturn->update([
                'status' => TaxReturnStatus::Filed,
                'filed_by' => $userId,
                'filed_at' => now(),
                'filing_package_path' => $path,
                'filing_package_checksum' => hash('sha256', json_encode($package)),
            ]);

            return $taxReturn->fresh(['taxPeriod', 'filedByUser']);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function buildFilingPackage(TaxReturn $taxReturn): array
    {
        $taxReturn->loadMissing('taxPeriod');
        $period = $taxReturn->taxPeriod;

        $summary = $this->reports->vatSummary([
            'company_id' => $taxReturn->company_id,
            'from_date' => $period?->start_date?->toDateString(),
            'to_date' => $period?->end_date?->toDateString(),
            'tax_period_id' => $period?->id,
        ]);

        return [
            'schema' => 'jana-prints.tax-return.v1',
            'generated_at' => now()->toIso8601String(),
            'return' => [
                'number' => $taxReturn->return_number,
                'type' => $taxReturn->return_type,
                'period_code' => $period?->code,
                'period_name' => $period?->name,
                'from_date' => $period?->start_date?->toDateString(),
                'to_date' => $period?->end_date?->toDateString(),
                'output_tax' => (float) $taxReturn->output_tax,
                'input_tax' => (float) $taxReturn->input_tax,
                'withholding_tax' => (float) $taxReturn->withholding_tax,
                'net_liability' => (float) $taxReturn->net_liability,
            ],
            'vat_summary' => $summary,
            'submission' => [
                'channel' => 'manual_package',
                'note' => 'Download and submit via KRA/eTIMS or your tax agent. This is not an automatic KRA API submission.',
            ],
        ];
    }

    public function downloadPackage(TaxReturn $taxReturn): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (! $taxReturn->filing_package_path || ! Storage::disk('local')->exists($taxReturn->filing_package_path)) {
            $package = $this->buildFilingPackage($taxReturn);
            $filename = $taxReturn->return_number.'-filing-package.json';

            return response()->streamDownload(
                function () use ($package) {
                    echo json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                },
                $filename,
                ['Content-Type' => 'application/json'],
            );
        }

        return Storage::disk('local')->download(
            $taxReturn->filing_package_path,
            $taxReturn->return_number.'-filing-package.json',
        );
    }
}
