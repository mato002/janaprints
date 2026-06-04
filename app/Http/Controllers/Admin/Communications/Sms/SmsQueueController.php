<?php

namespace App\Http\Controllers\Admin\Communications\Sms;

use App\Http\Controllers\Controller;
use App\Models\Communications\SmsCampaign;
use App\Models\Communications\SmsMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsQueueController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SmsCampaign::class);

        $messages = SmsMessage::query()
            ->forTenant()
            ->with(['campaign:id,name,campaign_code'])
            ->when($request->queue_status, fn ($q, $s) => $q->where('queue_status', $s))
            ->when($request->delivery_status, fn ($q, $s) => $q->where('delivery_status', $s))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.communications.sms.queues.index', compact('messages'));
    }
}
