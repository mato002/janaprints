<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\EmailCampaign;
use App\Support\Communications\Email\EmailVisibilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailCommunicationReportsController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected EmailVisibilityService $visibility,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmailCampaign::class);

        $filters = $request->only(['date_from', 'date_to']);
        $companyId = $this->requireCompanyId();

        return view('admin.communications.email.reports.index', [
            'filters' => $filters,
            'departments' => $this->visibility->departmentReport(
                $companyId,
                $filters['date_from'] ?? null,
                $filters['date_to'] ?? null,
            ),
        ]);
    }
}
