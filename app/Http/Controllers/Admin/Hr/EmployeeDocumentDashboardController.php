<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeDocument;
use App\Support\Hr\EmployeeDocumentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeDocumentDashboardController extends Controller
{
    public function __construct(
        protected EmployeeDocumentService $documents,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', EmployeeDocument::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.documents.dashboard', [
            'stats' => $this->documents->dashboardStats($companyId),
            'alerts' => $this->documents->renewalAlerts($companyId),
            'formData' => $this->documents->formData($companyId),
        ]);
    }
}
