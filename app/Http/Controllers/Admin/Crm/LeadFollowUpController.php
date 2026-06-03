<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Enums\FollowUpStatus;
use App\Http\Controllers\Controller;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadFollowUp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadFollowUpController extends Controller
{
    public function store(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $data = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
            'scheduled_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        LeadFollowUp::query()->create([
            ...$data,
            'company_id' => $lead->company_id,
            'branch_id' => $lead->branch_id,
            'lead_id' => $lead->id,
            'created_by' => auth()->id(),
            'status' => FollowUpStatus::Pending,
        ]);

        return back()->with('status', __('Follow-up scheduled.'));
    }

    public function update(Request $request, Lead $lead, LeadFollowUp $followUp): RedirectResponse
    {
        $this->authorize('update', $lead);
        abort_unless($followUp->lead_id === $lead->id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::enum(FollowUpStatus::class)],
            'notes' => ['nullable', 'string'],
        ]);

        $status = FollowUpStatus::from($data['status']);

        $followUp->update([
            ...$data,
            'status' => $status,
            'completed_at' => $status === FollowUpStatus::Completed ? now() : null,
        ]);

        return back()->with('status', __('Follow-up updated.'));
    }

    public function destroy(Lead $lead, LeadFollowUp $followUp): RedirectResponse
    {
        $this->authorize('update', $lead);
        abort_unless($followUp->lead_id === $lead->id, 404);

        $followUp->delete();

        return back()->with('status', __('Follow-up removed.'));
    }
}
