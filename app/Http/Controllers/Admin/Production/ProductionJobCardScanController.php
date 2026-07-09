<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Support\Production\JobCardScanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductionJobCardScanController extends Controller
{
    public function scan(string $code, JobCardScanService $scan): RedirectResponse
    {
        return $scan->redirectForScan($code, auth()->user());
    }

    public function label(ProductionJobCard $jobCard, JobCardScanService $scan): View
    {
        $this->authorize('view', $jobCard);

        return view('admin.production.job-cards.scan-label', [
            'jobCard' => $jobCard,
            'label' => $scan->labelPayload($jobCard),
        ]);
    }
}
