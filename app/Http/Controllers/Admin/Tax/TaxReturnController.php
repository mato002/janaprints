<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Controller;
use App\Models\Tax\TaxCode;
use App\Models\Tax\TaxPeriod;
use App\Models\Tax\TaxReturn;
use App\Support\Tax\TaxReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaxReturnController extends Controller
{
    public function __construct(
        protected TaxReturnService $returns,
    ) {}

    public function index(): View
    {
        $this->authorize('manageReturns', TaxCode::class);

        $returns = TaxReturn::query()
            ->forTenant()
            ->with('taxPeriod')
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.tax.returns.index', compact('returns'));
    }

    public function show(TaxReturn $taxReturn): View
    {
        $this->authorize('manageReturns', TaxCode::class);

        $taxReturn->load(['taxPeriod', 'filedByUser']);

        return view('admin.tax.returns.show', compact('taxReturn'));
    }

    public function buildDraft(TaxPeriod $taxPeriod): RedirectResponse
    {
        $this->authorize('manageReturns', TaxCode::class);

        $taxReturn = $this->returns->buildDraft($taxPeriod, (int) auth()->id());

        return redirect()
            ->route('admin.tax.returns.show', $taxReturn)
            ->with('status', __('VAT return draft prepared.'));
    }

    public function file(TaxReturn $taxReturn): RedirectResponse
    {
        $this->authorize('manageReturns', TaxCode::class);

        $this->returns->file($taxReturn, (int) auth()->id());

        return back()->with('status', __('Tax return filed. Filing package is ready to download.'));
    }

    public function downloadPackage(TaxReturn $taxReturn)
    {
        $this->authorize('manageReturns', TaxCode::class);

        return $this->returns->downloadPackage($taxReturn);
    }
}
