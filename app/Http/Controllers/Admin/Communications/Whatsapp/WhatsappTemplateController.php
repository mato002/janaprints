<?php

namespace App\Http\Controllers\Admin\Communications\Whatsapp;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\WhatsappConversation;
use App\Support\Communications\Whatsapp\WhatsappAutomationMapper;
use App\Support\Communications\Whatsapp\WhatsappTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WhatsappTemplateController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected WhatsappTemplateService $templates,
        protected WhatsappAutomationMapper $automation,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', WhatsappConversation::class);

        $companyId = $this->requireCompanyId();
        $bindings = $this->templates->listBindings($companyId);
        $automationMap = $this->automation->mapForCompany($companyId);

        return view('admin.communications.whatsapp.templates.index', compact('bindings', 'automationMap'));
    }

    public function sync(): RedirectResponse
    {
        $this->authorize('manage', WhatsappConversation::class);

        $count = $this->templates->syncFromCom1($this->requireCompanyId(), auth()->id());

        return back()->with('status', __(':count WhatsApp template binding(s) synced from COM-1.', ['count' => $count]));
    }
}
