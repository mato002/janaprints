<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Operations\RetentionPolicy;
use App\Operations\DataRetentionCenter;
use App\Services\Operations\DataRetentionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DataRetentionController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected DataRetentionService $retention,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DataRetentionCenter::class);

        $companyId = (int) ($this->tenantCompanyId() ?? $request->user()->company_id);

        return view('admin.operations.retention.index', [
            'policies' => $this->retention->policiesForCompany($companyId),
            'metrics' => $this->retention->summaryMetrics($companyId),
            'canManage' => $request->user()->can('manage', DataRetentionCenter::class),
        ]);
    }

    public function update(Request $request, RetentionPolicy $retentionPolicy): RedirectResponse
    {
        $this->authorize('manage', DataRetentionCenter::class);

        $companyId = (int) ($this->tenantCompanyId() ?? $request->user()->company_id);

        if ((int) $retentionPolicy->company_id !== $companyId) {
            abort(404);
        }

        $this->retention->updatePolicy($retentionPolicy, $request->all(), $request->user());

        return redirect()
            ->route('admin.operations.retention.index')
            ->with('success', __('Retention policy updated for :domain.', [
                'domain' => $retentionPolicy->domain->label(),
            ]));
    }

    protected function tenantCompanyId(): ?int
    {
        if (function_exists('tenant') && tenant()->hasCompany()) {
            return tenant()->companyId();
        }

        return null;
    }
}
