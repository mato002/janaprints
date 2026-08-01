<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Support\Commercial\CommercialApprovalQueueService;
use Illuminate\Http\Request;
use App\Support\Sales\SalesDeskViews;
use Illuminate\Http\RedirectResponse;

class CommercialApprovalQueueController extends Controller
{
    use ResolvesCrmTenant;

    public function __construct(
        protected CommercialApprovalQueueService $queue,
    ) {}

    public function index(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('commercial.approvals.view'), 403);

        return redirect()->to(SalesDeskViews::deskUrl(SalesDeskViews::APPROVALS, $request->query()));
    }
}
