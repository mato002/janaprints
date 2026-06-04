<?php

namespace App\Http\Controllers\Admin\Communications\Sms;

use App\Http\Controllers\Admin\Communications\Sms\Concerns\ResolvesSmsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\SmsCampaign;
use App\Models\Communications\SmsCreditTransaction;
use App\Support\Communications\Sms\SmsCreditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsCreditController extends Controller
{
    use ResolvesSmsTenant;

    public function __construct(
        protected SmsCreditService $credits,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', SmsCampaign::class);

        $companyId = $this->requireCompanyId();
        $balance = $this->credits->balanceFor($companyId);

        $transactions = SmsCreditTransaction::query()
            ->forTenant()
            ->with(['creator', 'campaign:id,name', 'branch', 'department'])
            ->latest()
            ->paginate(20);

        return view('admin.communications.sms.credits.index', compact('balance', 'transactions'));
    }

    public function purchase(Request $request): RedirectResponse
    {
        $this->authorize('audit', SmsCampaign::class);

        $request->validate(['credits' => ['required', 'numeric', 'min:1']]);

        $this->credits->purchase(
            $this->requireCompanyId(),
            (float) $request->input('credits'),
            $request->user(),
            __('Manual credit purchase'),
        );

        return back()->with('status', __('Credits purchased.'));
    }
}
