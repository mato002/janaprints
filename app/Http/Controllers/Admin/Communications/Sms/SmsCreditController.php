<?php

namespace App\Http\Controllers\Admin\Communications\Sms;

use App\Http\Controllers\Admin\Communications\Sms\Concerns\ResolvesSmsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\SmsCampaign;
use App\Models\Communications\SmsCreditTransaction;
use App\Support\Communications\Sms\SmsCreditService;
use App\Support\Communications\Sms\SmsCrmWalletTopupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsCreditController extends Controller
{
    use ResolvesSmsTenant;

    public function __construct(
        protected SmsCreditService $credits,
        protected SmsCrmWalletTopupService $topups,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', SmsCampaign::class);

        $companyId = $this->requireCompanyId();
        $balance = $this->credits->balanceFor($companyId);
        $live = $this->topups->liveBalance($companyId);
        $topupConfig = $this->topups->uiConfig();

        $transactions = SmsCreditTransaction::query()
            ->forTenant()
            ->with(['creator', 'campaign:id,name', 'branch', 'department'])
            ->latest()
            ->paginate(20);

        return view('admin.communications.sms.credits.index', compact('balance', 'transactions', 'live', 'topupConfig'));
    }

    public function topup(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('audit', SmsCampaign::class);

        $config = $this->topups->uiConfig();
        $min = (float) ($config['min_amount'] ?? 10);
        $max = (float) ($config['max_amount'] ?? 50000);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.$min, 'max:'.$max],
            'phone' => ['required', 'string', 'max:32'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $topup = $this->topups->initiate(
            $this->requireCompanyId(),
            (float) $validated['amount'],
            (string) $validated['phone'],
            $request->user(),
            $validated['notes'] ?? null,
        );

        $message = $topup->message ?: __('M-Pesa prompt sent. Approve on your phone to credit SMS balance.');

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'reference' => $topup->reference,
                'status' => $topup->status,
                'message' => $message,
                'amount' => (float) $topup->amount,
                'phone' => $topup->phone_number,
                'status_url' => route('admin.communications.sms.credits.topup.status', $topup->reference, absolute: false),
                'next_poll_seconds' => 3,
            ]);
        }

        return back()
            ->with('info', $message)
            ->with('sms_topup_poll_reference', $topup->reference);
    }

    public function topupStatus(Request $request, string $reference): JsonResponse
    {
        $this->authorize('audit', SmsCampaign::class);

        return response()->json(
            $this->topups->status($reference, $this->requireCompanyId(), $request->user()),
        );
    }
}
