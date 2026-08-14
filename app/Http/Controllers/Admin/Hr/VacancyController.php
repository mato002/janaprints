<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Vacancy;
use App\Rules\DateNotInThePast;
use App\Support\Hr\RecruitmentDashboardService;
use App\Support\Hr\VacancyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VacancyController extends Controller
{
    public function __construct(
        protected VacancyService $vacancies,
        protected RecruitmentDashboardService $form,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Vacancy::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.recruitment.vacancies.index', [
            'vacancies' => $this->vacancies->paginate($companyId, $request->only(['status', 'search'])),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Vacancy::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.recruitment.vacancies.create', [
            'formData' => $this->form->formData($companyId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Vacancy::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $vacancy = $this->vacancies->create($companyId, $this->validated($request, $companyId), $request->user());

        return redirect()
            ->route('admin.hr.recruitment.vacancies.show', $vacancy)
            ->with('status', __('Vacancy created.'));
    }

    public function show(Vacancy $vacancy): View
    {
        $this->authorize('view', $vacancy);

        $vacancy->load(['department', 'jobTitle', 'branch', 'requisition', 'applications.candidate']);

        return view('admin.hr.recruitment.vacancies.show', [
            'vacancy' => $vacancy,
        ]);
    }

    public function publish(Vacancy $vacancy): RedirectResponse
    {
        $this->authorize('update', $vacancy);
        $this->vacancies->publish($vacancy);

        return back()->with('status', __('Vacancy published.'));
    }

    public function close(Vacancy $vacancy): RedirectResponse
    {
        $this->authorize('update', $vacancy);
        $this->vacancies->close($vacancy);

        return back()->with('status', __('Vacancy closed.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, int $companyId): array
    {
        return $request->validate([
            'job_requisition_id' => ['nullable', Rule::exists('job_requisitions', 'id')->where('company_id', $companyId)],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'department_id' => ['nullable', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'job_title_id' => ['nullable', Rule::exists('job_titles', 'id')->where('company_id', $companyId)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'positions' => ['nullable', 'integer', 'min:1', 'max:99'],
            'closing_date' => ['nullable', 'date', new DateNotInThePast],
        ]);
    }
}
