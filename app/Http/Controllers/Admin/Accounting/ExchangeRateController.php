<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Currency;
use App\Models\Accounting\ExchangeRate;
use App\Support\Accounting\ExchangeRateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExchangeRateController extends Controller
{
    use ResolvesAccountingTenant;

    public function __construct(
        protected ExchangeRateService $rates,
    ) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->can('accounting.currencies.view'), 403);

        $rates = ExchangeRate::query()
            ->forTenant()
            ->with('currency')
            ->orderByDesc('rate_date')
            ->orderBy('currency_code')
            ->paginate(50);

        $currencies = Currency::query()
            ->where('is_active', true)
            ->where('code', '!=', config('accounting.base_currency', 'KES'))
            ->orderBy('code')
            ->get();

        $baseCurrency = config('accounting.base_currency', 'KES');

        return view('admin.accounting.currencies.rates', compact('rates', 'currencies', 'baseCurrency'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('accounting.currencies.manage'), 403);

        ['companyId' => $companyId] = $this->tenantIds();

        $validated = $request->validate([
            'currency_code' => ['required', 'string', 'size:3'],
            'rate_date' => ['required', 'date'],
            'rate_to_base' => ['required', 'numeric', 'gt:0'],
            'source' => ['nullable', 'string', 'max:128'],
        ]);

        $this->rates->storeRate($companyId, $validated);

        return back()->with('status', __('Exchange rate saved.'));
    }
}
