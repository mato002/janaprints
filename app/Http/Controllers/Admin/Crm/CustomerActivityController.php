<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerActivity;
use App\Models\Crm\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerActivityController extends Controller
{
    use ResolvesCrmTenant;

    public function storeForCustomer(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('create', CustomerActivity::class);
        $this->authorize('view', $customer);

        $this->createActivity($request, $customer->company_id, $customer->branch_id, $customer->id, null);

        return back()->with('status', __('Activity logged.'));
    }

    public function storeForLead(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('create', CustomerActivity::class);
        $this->authorize('view', $lead);

        $this->createActivity($request, $lead->company_id, $lead->branch_id, $lead->customer_id, $lead->id);

        return back()->with('status', __('Activity logged.'));
    }

    public function destroy(CustomerActivity $activity): RedirectResponse
    {
        $this->authorize('delete', $activity);

        $activity->loadMissing(['lead:id,public_id', 'customer:id,public_id']);

        $redirect = auth()->user()?->can('commercial.activities.view')
            ? route('admin.commercial.activities.index')
            : ($activity->lead
                ? route('admin.crm.leads.show', $activity->lead)
                : ($activity->customer
                    ? route('admin.crm.customers.show', $activity->customer)
                    : route('admin.crm.customers.index')));

        $activity->delete();

        return redirect($redirect)->with('status', __('Activity removed.'));
    }

    protected function createActivity(Request $request, int $companyId, int $branchId, ?int $customerId, ?int $leadId): void
    {
        $data = $request->validate([
            'activity_type' => ['required', Rule::enum(ActivityType::class)],
            'status' => ['nullable', Rule::enum(ActivityStatus::class)],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'activity_at' => ['required', 'date'],
        ]);

        $data['status'] ??= ActivityStatus::Completed;

        CustomerActivity::query()->create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'lead_id' => $leadId,
            'user_id' => auth()->id(),
            'activity_at' => $data['activity_at'],
        ]);
    }
}
