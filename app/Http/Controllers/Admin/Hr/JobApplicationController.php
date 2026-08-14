<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\InterviewRecommendation;
use App\Enums\RecruitmentPipelineStage;
use App\Http\Controllers\Controller;
use App\Models\Hr\JobApplication;
use App\Models\Hr\OfferLetter;
use App\Rules\DateNotInThePast;
use App\Support\Hr\RecruitmentApplicationService;
use App\Support\Hr\RecruitmentDashboardService;
use App\Support\Hr\VacancyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function __construct(
        protected RecruitmentApplicationService $applications,
        protected VacancyService $vacancies,
        protected RecruitmentDashboardService $form,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', JobApplication::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.recruitment.applications.index', [
            'applications' => $this->applications->paginate($companyId, $request->only(['stage', 'vacancy_id', 'search'])),
            'filters' => $request->only(['stage', 'vacancy_id', 'search']),
            'stages' => RecruitmentPipelineStage::cases(),
            'openVacancies' => $this->vacancies->openVacancies($companyId),
        ]);
    }

    public function pipeline(Request $request): View
    {
        $this->authorize('viewAny', JobApplication::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.recruitment.applications.pipeline', [
            'board' => $this->applications->pipelineBoard($companyId),
            'stages' => RecruitmentPipelineStage::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', JobApplication::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.recruitment.applications.create', [
            'openVacancies' => $this->vacancies->openVacancies($companyId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', JobApplication::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        $validated = $request->validate([
            'vacancy_id' => ['required', Rule::exists('vacancies', 'id')->where('company_id', $companyId)],
            'candidate_id' => ['nullable', Rule::exists('candidates', 'id')->where('company_id', $companyId)],
            'first_name' => ['required_without:candidate_id', 'string', 'max:255'],
            'last_name' => ['required_without:candidate_id', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'resume_notes' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $application = $this->applications->apply($companyId, $validated, $request->user());

        return redirect()
            ->route('admin.hr.recruitment.applications.show', $application)
            ->with('status', __('Application recorded.'));
    }

    public function show(JobApplication $jobApplication): View
    {
        $this->authorize('view', $jobApplication);

        $jobApplication->load([
            'candidate',
            'vacancy.department',
            'vacancy.jobTitle',
            'interviews.interviewer',
            'interviews.feedback',
            'offerLetters',
            'onboarding.employee',
            'employee',
        ]);

        $companyId = $jobApplication->company_id;

        return view('admin.hr.recruitment.applications.show', [
            'application' => $jobApplication,
            'stages' => RecruitmentPipelineStage::cases(),
            'recommendations' => InterviewRecommendation::cases(),
            'formData' => $this->form->formData($companyId),
        ]);
    }

    public function advance(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        $this->authorize('update', $jobApplication);

        $validated = $request->validate([
            'stage' => ['required', Rule::enum(RecruitmentPipelineStage::class)],
        ]);

        $this->applications->advanceStage(
            $jobApplication,
            RecruitmentPipelineStage::from($validated['stage']),
        );

        return back()->with('status', __('Pipeline stage updated.'));
    }

    public function reject(JobApplication $jobApplication): RedirectResponse
    {
        $this->authorize('update', $jobApplication);
        $this->applications->reject($jobApplication);

        return back()->with('status', __('Application rejected.'));
    }

    public function scheduleInterview(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        $this->authorize('update', $jobApplication);

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date', new DateNotInThePast],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:480'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_link' => ['nullable', 'string', 'max:500'],
            'interviewer_user_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->applications->scheduleInterview($jobApplication, $validated, $request->user());

        return back()->with('status', __('Interview scheduled.'));
    }

    public function recordFeedback(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        $this->authorize('update', $jobApplication);

        $validated = $request->validate([
            'interview_schedule_id' => ['required', 'exists:interview_schedules,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'recommendation' => ['required', Rule::enum(InterviewRecommendation::class)],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $schedule = $jobApplication->interviews()
            ->whereKey($validated['interview_schedule_id'])
            ->firstOrFail();

        $this->applications->recordFeedback($schedule, $validated, $request->user());

        return back()->with('status', __('Interview feedback recorded.'));
    }

    public function createOffer(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        $this->authorize('update', $jobApplication);

        $validated = $request->validate([
            'salary_offered' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date', new DateNotInThePast],
            'terms' => ['nullable', 'string', 'max:2000'],
        ]);

        $offer = $this->applications->createOffer($jobApplication, $validated, $request->user());

        return back()->with('status', __('Offer letter :ref created.', ['ref' => $offer->reference]));
    }

    public function sendOffer(OfferLetter $offerLetter): RedirectResponse
    {
        $this->authorize('update', $offerLetter->application);
        $this->applications->sendOffer($offerLetter);

        return back()->with('status', __('Offer letter sent.'));
    }

    public function acceptOffer(OfferLetter $offerLetter): RedirectResponse
    {
        $this->authorize('update', $offerLetter->application);
        $this->applications->acceptOffer($offerLetter);

        return back()->with('status', __('Offer accepted.'));
    }
}
