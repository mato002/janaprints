<?php

namespace App\Http\Controllers\Admin\Communications\Sms;

use App\Http\Controllers\Controller;
use App\Models\Communications\SmsCampaign;
use App\Models\Communications\SmsProviderLog;
use Illuminate\View\View;

class SmsProviderLogController extends Controller
{
    public function index(): View
    {
        $this->authorize('audit', SmsCampaign::class);

        $companyId = tenant()->companyId();

        $logs = SmsProviderLog::query()
            ->with(['message.campaign'])
            ->when($companyId, fn ($q) => $q->whereHas('message', fn ($m) => $m->where('company_id', $companyId)))
            ->latest('created_at')
            ->paginate(25);

        return view('admin.communications.sms.provider-logs.index', compact('logs'));
    }
}
