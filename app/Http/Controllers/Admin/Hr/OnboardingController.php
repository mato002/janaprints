<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\JobApplication;
use App\Models\Hr\OnboardingRecord;
use App\Support\Hr\OnboardingService;
use App\Support\Hr\RecruitmentDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(
        protected OnboardingService $onboarding,
        protected RecruitmentDashboardService $form,
    ) {}

    public function start(JobApplication $jobApplication): RedirectResponse
    {
        $this->authorize('update', $jobApplication);

        $record = $this->onboarding->start($jobApplication, request()->user());

        return redirect()
            ->route('admin.hr.recruitment.onboarding.show', $record)
            ->with('status', __('Onboarding started.'));
    }

    public function show(OnboardingRecord $onboardingRecord): View
    {
        $this->authorize('view', $onboardingRecord);

        $onboardingRecord->load([
            'application.candidate',
            'application.vacancy',
            'employee',
            'department',
            'jobTitle',
            'branch',
            'supervisor',
        ]);

        return view('admin.hr.recruitment.onboarding.show', [
            'onboarding' => $onboardingRecord,
            'formData' => $this->form->formData($onboardingRecord->company_id),
        ]);
    }

    public function update(Request $request, OnboardingRecord $onboardingRecord): RedirectResponse
    {
        $this->authorize('update', $onboardingRecord);

        $companyId = $onboardingRecord->company_id;

        $validated = $request->validate([
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'department_id' => ['nullable', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'job_title_id' => ['nullable', Rule::exists('job_titles', 'id')->where('company_id', $companyId)],
            'supervisor_employee_id' => ['nullable', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'employee_number' => ['nullable', 'string', 'max:50'],
            'hire_date' => ['nullable', 'date'],
            'documents_collected' => ['nullable', 'boolean'],
            'system_access_granted' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->onboarding->update($onboardingRecord, $validated);

        return back()->with('status', __('Onboarding updated.'));
    }

    public function complete(OnboardingRecord $onboardingRecord): RedirectResponse
    {
        $this->authorize('update', $onboardingRecord);

        $record = $this->onboarding->complete($onboardingRecord, request()->user());

        return redirect()
            ->route('admin.hr.employees.show', $record->employee)
            ->with('status', __('Onboarding completed. Employee created.'));
    }
}
