<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerActivity;
use App\Models\Crm\Lead;
use App\Models\User;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommercialActivityController extends Controller
{
    use ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CustomerActivity::class);

        $activities = $this->scopeToTenant(
            CustomerActivity::query()
                ->with(['customer:id,company_name,customer_code', 'lead:id,lead_name', 'user:id,name'])
        )
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->integer('customer_id')))
            ->when($request->filled('lead_id'), fn ($q) => $q->where('lead_id', $request->integer('lead_id')))
            ->when($request->filled('activity_type'), fn ($q) => $q->where('activity_type', $request->string('activity_type')))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('activity_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('activity_at', '<=', $request->date('date_to')))
            ->orderByDesc('activity_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.commercial.activities.index', [
            'activities' => $activities,
            'filters' => $request->only(['customer_id', 'lead_id', 'activity_type', 'user_id', 'status', 'date_from', 'date_to']),
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->limit(100)->get(['id', 'company_name']),
            'leads' => Lead::query()->forTenant()->orderBy('lead_name')->limit(100)->get(['id', 'lead_name']),
            'users' => $this->assignableUsers(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', CustomerActivity::class);

        return view('admin.commercial.activities.create', $this->formMeta($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CustomerActivity::class);

        $data = $this->validateActivity($request);
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $activity = CustomerActivity::query()->create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'user_id' => $data['user_id'] ?? auth()->id(),
        ]);

        return redirect()
            ->route('admin.commercial.activities.show', $activity)
            ->with('status', __('Activity logged.'));
    }

    public function show(CustomerActivity $activity): View
    {
        $this->authorize('view', $activity);

        $activity->load(['customer', 'lead', 'user']);

        return view('admin.commercial.activities.show', compact('activity'));
    }

    public function edit(CustomerActivity $activity): View
    {
        $this->authorize('update', $activity);

        return view('admin.commercial.activities.edit', array_merge(
            ['activity' => $activity],
            $this->formMeta(request(), $activity),
        ));
    }

    public function update(Request $request, CustomerActivity $activity): RedirectResponse
    {
        $this->authorize('update', $activity);

        $activity->update($this->validateActivity($request, $activity));

        return redirect()
            ->route('admin.commercial.activities.show', $activity)
            ->with('status', __('Activity updated.'));
    }

    public function destroy(CustomerActivity $activity): RedirectResponse
    {
        $this->authorize('delete', $activity);

        $activity->delete();

        return redirect()
            ->route('admin.commercial.activities.index')
            ->with('status', __('Activity removed.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(Request $request, ?CustomerActivity $activity = null): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        return [
            'formFields' => $this->formSettings->resolvedFields('activity.create', $companyId, $branchId, $activity),
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->get(['id', 'company_name']),
            'leads' => Lead::query()->forTenant()->orderBy('lead_name')->get(['id', 'lead_name']),
            'users' => $this->assignableUsers(),
            'activityTypes' => ActivityType::cases(),
            'activityStatuses' => ActivityStatus::cases(),
            'presetCustomerId' => $request->integer('customer_id') ?: $activity?->customer_id,
            'presetLeadId' => $request->integer('lead_id') ?: $activity?->lead_id,
        ];
    }

    /**
     * @return list<User>
     */
    protected function assignableUsers(): array
    {
        $query = User::query()->where('is_active', true)->orderBy('name');

        if (! auth()->user()?->hasRole('Super Admin') && $companyId = tenant()->companyId()) {
            $query->where('company_id', $companyId);
        }

        return $query->limit(100)->get(['id', 'name'])->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateActivity(Request $request, ?CustomerActivity $activity = null): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $data = $this->formSettings->validateRequest($request, 'activity.create', [
            'customer_id' => ['integer', 'exists:customers,id'],
            'lead_id' => ['integer', 'exists:leads,id'],
            'user_id' => ['integer', 'exists:users,id'],
            'activity_type' => [Rule::enum(ActivityType::class)],
            'status' => [Rule::enum(ActivityStatus::class)],
            'subject' => ['string', 'max:255'],
            'description' => ['string'],
            'activity_at' => ['date'],
        ], $companyId, $branchId);

        if (empty($data['customer_id']) && empty($data['lead_id'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'customer_id' => __('Link the activity to a customer or lead.'),
            ]);
        }

        if (! empty($data['customer_id'])) {
            $customer = Customer::query()->forTenant()->find($data['customer_id']);
            abort_if($customer === null, 404);
        }

        if (! empty($data['lead_id'])) {
            $lead = Lead::query()->forTenant()->find($data['lead_id']);
            abort_if($lead === null, 404);
        }

        return $data;
    }
}
