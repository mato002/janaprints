<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\EmailCampaign;
use App\Support\Communications\Email\EmailCommunicationCertificationService;
use Illuminate\View\View;

class EmailCertificationController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected EmailCommunicationCertificationService $certification,
    ) {}

    public function index(): View
    {
        $this->authorize('manage', EmailCampaign::class);

        $report = $this->certification->report($this->requireCompanyId());

        return view('admin.communications.email.certification.index', compact('report'));
    }
}
