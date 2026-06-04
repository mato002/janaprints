<?php

namespace App\Http\Controllers\Admin\Communications\Whatsapp;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\WhatsappConversation;
use App\Support\Communications\Whatsapp\WhatsappAnalyticsService;
use Illuminate\View\View;

class WhatsappAnalyticsController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected WhatsappAnalyticsService $analytics,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', WhatsappConversation::class);

        $stats = $this->analytics->dashboard($this->requireCompanyId());

        return view('admin.communications.whatsapp.analytics', compact('stats'));
    }
}
