<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Currency;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->can('accounting.currencies.view'), 403);

        $currencies = Currency::query()
            ->orderByDesc('is_active')
            ->orderBy('code')
            ->get();

        $baseCurrency = config('accounting.base_currency', 'KES');

        return view('admin.accounting.currencies.index', compact('currencies', 'baseCurrency'));
    }
}
