<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Enums\CommercialComplaintPriority;
use App\Enums\CommercialComplaintSource;
use App\Enums\CommercialComplaintStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Commercial\CommercialComplaint;
use App\Models\Crm\Customer;
use App\Models\User;
use App\Support\Commercial\ComplaintService;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommercialComplaintController extends Controller
{
    use ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected ComplaintService $complaints,
        protected FormSettingsService $formSettings,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CommercialComplaint::class);

        $complaints = $this->scopeToTenant(
            CommercialComplaint::query()
                ->with(['customer:id,company_name', 'assignee:id,name', 'branch:id,name'])
        )
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->integer('customer_id')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.commercial.complaints.index', [
            'complaints' => $complaints,
            'filters' => $request->only(['status', 'customer_id']),
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->limit(100)->get(['id', 'company_name']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', CommercialComplaint::class);

        return view('admin.commercial.complaints.create', $this->formMeta($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CommercialComplaint::class);

        $data = $this->validateComplaint($request);
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $complaint = CommercialComplaint::query()->create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'reported_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.commercial.complaints.show', $complaint)->with('status', __('Complaint created.'));
    }

    public function show(CommercialComplaint $complaint): View
    {
        $this->authorize('view', $complaint);

        $complaint->load(['customer', 'assignee', 'reporter', 'branch']);

        return view('admin.commercial.complaints.show', [
            'complaint' => $complaint,
            'users' => $this->assignableUsers(),
        ]);
    }

    public function edit(CommercialComplaint $complaint): View
    {
        $this->authorize('update', $complaint);

        return view('admin.commercial.complaints.edit', array_merge(
            ['complaint' => $complaint],
            $this->formMeta(request()),
        ));
    }

    public function update(Request $request, CommercialComplaint $complaint): RedirectResponse
    {
        $this->authorize('update', $complaint);

        $complaint->update($this->validateComplaint($request, false));

        return redirect()->route('admin.commercial.complaints.show', $complaint)->with('status', __('Complaint updated.'));
    }

    public function assign(Request $request, CommercialComplaint $complaint): RedirectResponse
    {
        $this->authorize('update', $complaint);

        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $this->complaints->assign($complaint, (int) $validated['assigned_to']);

        return back()->with('status', __('Complaint assigned.'));
    }

    public function resolve(Request $request, CommercialComplaint $complaint): RedirectResponse
    {
        $this->authorize('resolve', $complaint);

        $validated = $request->validate([
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->complaints->transition($complaint, CommercialComplaintStatus::Resolved, $validated['resolution_notes'] ?? null);

        return back()->with('status', __('Complaint resolved.'));
    }

    public function close(CommercialComplaint $complaint): RedirectResponse
    {
        $this->authorize('resolve', $complaint);

        $this->complaints->transition($complaint, CommercialComplaintStatus::Closed);

        return back()->with('status', __('Complaint closed.'));
    }

    public function reopen(CommercialComplaint $complaint): RedirectResponse
    {
        $this->authorize('resolve', $complaint);

        $this->complaints->transition($complaint, CommercialComplaintStatus::Reopened);

        return back()->with('status', __('Complaint reopened.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateComplaint(Request $request, bool $requireSubject = true): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $this->formSettings->withoutHiddenInputs($request, 'commercial_complaint.create', $companyId, $branchId);

        $rules = $this->formSettings->mergeValidationRules('commercial_complaint.create', [
            'customer_id' => ['exists:customers,id'],
            'subject' => ['string', 'max:255'],
            'description' => ['string', 'max:10000'],
            'source' => [Rule::enum(CommercialComplaintSource::class)],
            'priority' => [Rule::enum(CommercialComplaintPriority::class)],
            'status' => ['sometimes', Rule::enum(CommercialComplaintStatus::class)],
            'related_document_type' => ['string', 'max:60'],
            'related_document_id' => ['integer', 'min:1'],
        ], $companyId, $branchId);

        if (! $requireSubject) {
            foreach (['subject', 'description'] as $field) {
                if (isset($rules[$field])) {
                    $rules[$field] = array_map(
                        fn ($rule) => $rule === 'required' ? 'sometimes' : $rule,
                        $rules[$field],
                    );
                }
            }
        }

        return $request->validate($rules);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(Request $request): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        return [
            'formFields' => $this->formSettings->resolvedFields('commercial_complaint.create', $companyId, $branchId),
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->get(['id', 'company_name']),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    protected function assignableUsers()
    {
        ['companyId' => $companyId] = $this->tenantIds(request());

        return User::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
