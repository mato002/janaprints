<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\JobRequisition;
use App\Support\Hr\JobRequisitionService;
use App\Support\Hr\RecruitmentDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobRequisitionController extends Controller
{
    public function __construct(
        protected JobRequisitionService $requisitions,
        protected RecruitmentDashboardService $form,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', JobRequisition::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.recruitment.requisitions.index', [
            'requisitions' => $this->requisitions->paginate($companyId, $request->only(['status', 'search'])),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', JobRequisition::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.recruitment.requisitions.create', [
            'formData' => $this->form->formData($companyId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', JobRequisition::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $requisition = $this->requisitions->create($companyId, $this->validated($request, $companyId), $request->user());

        return redirect()
            ->route('admin.hr.recruitment.requisitions.show', $requisition)
            ->with('status', __('Job requisition created.'));
    }

    public function show(JobRequisition $jobRequisition): View
    {
        $this->authorize('view', $jobRequisition);

        $jobRequisition->load(['department', 'jobTitle', 'branch', 'requestedBy', 'approvedBy', 'vacancies']);

        return view('admin.hr.recruitment.requisitions.show', [
            'requisition' => $jobRequisition,
        ]);
    }

    public function submit(JobRequisition $jobRequisition): RedirectResponse
    {
        $this->authorize('update', $jobRequisition);
        $this->requisitions->submit($jobRequisition);

        return back()->with('status', __('Requisition submitted for approval.'));
    }

    public function approve(JobRequisition $jobRequisition): RedirectResponse
    {
        $this->authorize('update', $jobRequisition);
        $this->requisitions->approve($jobRequisition, request()->user());

        return back()->with('status', __('Requisition approved.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, int $companyId): array
    {
        return $request->validate([
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'department_id' => ['nullable', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'job_title_id' => ['nullable', Rule::exists('job_titles', 'id')->where('company_id', $companyId)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'headcount' => ['nullable', 'integer', 'min:1', 'max:99'],
            'justification' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
