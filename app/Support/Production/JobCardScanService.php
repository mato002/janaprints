<?php

namespace App\Support\Production;

use App\Models\Production\ProductionJobCard;
use App\Support\Production\DepartmentQueueRoutingService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

/**
 * Scan architecture for job cards — no hardware integration; resolves codes to ERP destinations.
 */
class JobCardScanService
{
    public function __construct(
        protected DepartmentQueueRoutingService $routing,
    ) {}

    public function findByCode(string $code): ?ProductionJobCard
    {
        $normalized = strtoupper(trim($code));

        return ProductionJobCard::query()
            ->forTenant()
            ->where(function ($query) use ($normalized, $code) {
                $query->where('job_card_number', $normalized)
                    ->orWhere('job_card_number', trim($code));
            })
            ->first();
    }

    public function scanUrl(ProductionJobCard $jobCard): string
    {
        return route('admin.production.scan.show', ['code' => $jobCard->job_card_number]);
    }

    public function barcodeValue(ProductionJobCard $jobCard): string
    {
        return strtoupper($jobCard->job_card_number);
    }

    public function qrSvg(ProductionJobCard $jobCard): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'scale' => 4,
            'imageBase64' => false,
        ]);

        return (new QRCode($options))->render($this->scanUrl($jobCard));
    }

    /**
     * @return array<string, mixed>
     */
    public function labelPayload(ProductionJobCard $jobCard): array
    {
        $jobCard->loadMissing(['customer:id,company_name', 'salesOrder:id,order_number']);

        $routing = $this->routing->resolveForJobCard($jobCard);

        return [
            'job_number' => $jobCard->job_card_number,
            'barcode' => $this->barcodeValue($jobCard),
            'scan_url' => $this->scanUrl($jobCard),
            'qr_svg' => $this->qrSvg($jobCard),
            'customer' => $jobCard->customer?->company_name,
            'sales_order' => $jobCard->salesOrder?->order_number,
            'department' => $routing['department'] ?? null,
            'department_label' => $routing['department_label'] ?? null,
        ];
    }

    public function redirectForScan(string $code, ?\App\Models\User $user = null): RedirectResponse
    {
        $jobCard = $this->findByCode($code);

        abort_unless($jobCard !== null, 404, __('Job card not found.'));
        abort_unless($user?->can('view', $jobCard), 403);

        $routing = $this->routing->resolveForJobCard($jobCard);
        $department = $routing['department'] ?? null;

        if ($department && Route::has('admin.production.queue.department')) {
            return redirect()->route('admin.production.queue.department', [
                'department' => $department,
                'search' => $jobCard->job_card_number,
            ])->with('status', __('Scanned job :number', ['number' => $jobCard->job_card_number]));
        }

        return redirect()
            ->route('admin.production.job-cards.show', $jobCard)
            ->with('status', __('Scanned job :number', ['number' => $jobCard->job_card_number]));
    }
}
