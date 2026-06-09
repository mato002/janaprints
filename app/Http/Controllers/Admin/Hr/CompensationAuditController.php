<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeCompensation;
use App\Policies\EmployeeCompensationPolicy;
use App\Support\Hr\CompensationAuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompensationAuditController extends Controller
{
    public function __construct(
        protected CompensationAuditService $audit,
    ) {}

    public function __invoke(Request $request): View
    {
        $policy = app(EmployeeCompensationPolicy::class);
        abort_unless($policy->audit($request->user()), 403);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.compensation.audit', $this->audit->paginate($companyId));
    }
}
