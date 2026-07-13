<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\RecruitmentPipelineStage;
use App\Http\Controllers\Controller;
use App\Models\Hr\JobApplication;
use App\Models\Hr\JobRequisition;
use App\Models\Hr\Vacancy;
use App\Support\Hr\JobRequisitionService;
use App\Support\Hr\RecruitmentApplicationService;
use App\Support\Hr\RecruitmentDashboardService;
use App\Support\Hr\VacancyService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecruitmentDashboardController extends Controller
{
    public function __construct(
        protected RecruitmentDashboardService $dashboard,
        protected JobRequisitionService $requisitions,
        protected VacancyService $vacancies,
        protected RecruitmentApplicationService $applications,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', JobApplication::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $tab = $this->resolveTab($request);

        $payload = [
            'stats' => $this->dashboard->stats($companyId),
            'pipeline' => $this->dashboard->pipelineCounts($companyId),
            'tab' => $tab,
            'formData' => $this->dashboard->formData($companyId),
        ];

        return view('admin.hr.recruitment.dashboard', match ($tab) {
            'applications' => array_merge($payload, $this->applicationsPayload($request, $companyId)),
            'vacancies' => array_merge($payload, $this->vacanciesPayload($request, $companyId)),
            'requisitions' => array_merge($payload, $this->requisitionsPayload($request, $companyId)),
            default => array_merge($payload, $this->pipelinePayload($companyId)),
        });
    }

    protected function resolveTab(Request $request): string
    {
        $tab = $request->string('tab')->toString();

        return in_array($tab, ['pipeline', 'applications', 'vacancies', 'requisitions'], true)
            ? $tab
            : 'pipeline';
    }

    /**
     * @return array<string, mixed>
     */
    protected function pipelinePayload(int $companyId): array
    {
        return [
            'board' => $this->applications->pipelineBoard($companyId),
            'stages' => RecruitmentPipelineStage::cases(),
            'recentApplications' => $this->dashboard->recentApplications($companyId),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function applicationsPayload(Request $request, int $companyId): array
    {
        $filters = $request->only(['stage', 'vacancy_id', 'search']);

        return [
            'applications' => $this->applications->paginate($companyId, $filters),
            'filters' => $filters,
            'stages' => RecruitmentPipelineStage::cases(),
            'openVacancies' => $this->vacancies->openVacancies($companyId),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function vacanciesPayload(Request $request, int $companyId): array
    {
        $this->authorize('viewAny', Vacancy::class);

        $filters = $request->only(['status', 'search']);

        return [
            'vacancies' => $this->vacancies->paginate($companyId, $filters),
            'filters' => $filters,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function requisitionsPayload(Request $request, int $companyId): array
    {
        $this->authorize('viewAny', JobRequisition::class);

        $filters = $request->only(['status', 'search']);

        return [
            'requisitions' => $this->requisitions->paginate($companyId, $filters),
            'filters' => $filters,
        ];
    }
}
