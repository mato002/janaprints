<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Controller;
use App\Models\Tax\TaxCode;
use App\Models\Tax\TaxPeriod;
use Illuminate\View\View;

class TaxPeriodController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewPeriods', TaxCode::class);

        $periods = TaxPeriod::query()
            ->forTenant()
            ->orderByDesc('start_date')
            ->get();

        return view('admin.tax.periods.index', compact('periods'));
    }
}
