<?php

namespace App\Support\Organization;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class JobTitleService
{
    /**
     * @return Collection<int, JobTitle>
     */
    public function titlesForCompany(int $companyId, bool $activeOnly = false): Collection
    {
        return JobTitle::query()
            ->where('company_id', $companyId)
            ->when($activeOnly, fn (Builder $query) => $query->where('is_active', true))
            ->with(['department', 'reportsTo'])
            ->withCount('employees')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function hierarchyTree(int $companyId): array
    {
        $titles = JobTitle::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['department'])
            ->withCount(['employees' => fn (Builder $query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->keyBy('id');

        $branches = Branch::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->withCount(['employees' => fn (Builder $query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        $departments = Department::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->withCount(['employees' => fn (Builder $query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        $roots = $titles->filter(fn (JobTitle $title) => $title->reports_to_job_title_id === null);

        return [
            'company' => Company::query()->find($companyId),
            'branches' => $branches,
            'departments' => $departments,
            'nodes' => $roots
                ->map(fn (JobTitle $title) => $this->buildHierarchyNode($title, $titles))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, JobTitle>  $titles
     * @return array<string, mixed>
     */
    protected function buildHierarchyNode(JobTitle $title, Collection $titles): array
    {
        $children = $titles
            ->filter(fn (JobTitle $candidate) => (int) $candidate->reports_to_job_title_id === (int) $title->id)
            ->map(fn (JobTitle $child) => $this->buildHierarchyNode($child, $titles))
            ->values()
            ->all();

        return [
            'id' => $title->id,
            'code' => $title->code,
            'title' => $title->title,
            'level' => $title->level->label(),
            'department' => $title->department?->name,
            'employee_count' => $title->employees_count,
            'approval_authority' => $title->approval_authority,
            'children' => $children,
        ];
    }

    public function assertCanDeactivate(JobTitle $jobTitle): void
    {
        if ($jobTitle->employees()->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'job_title' => __('Cannot deactivate a job title with active employees assigned.'),
            ]);
        }

        if ($jobTitle->directReports()->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'job_title' => __('Cannot deactivate a job title that other active titles report to.'),
            ]);
        }
    }

    public function syncEmployeeDesignation(Employee $employee): void
    {
        if ($employee->job_title_id === null) {
            return;
        }

        $title = JobTitle::query()->find($employee->job_title_id);

        if ($title) {
            $employee->forceFill(['designation' => $title->title])->saveQuietly();
        }
    }

    /**
     * @return array<string, string>
     */
    public function approvalAuthorityOptions(int $companyId): array
    {
        return JobTitle::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereNotNull('approval_authority')
            ->orderBy('title')
            ->get()
            ->mapWithKeys(fn (JobTitle $title) => [
                $title->code => $title->title.' → '.$title->approval_authority,
            ])
            ->all();
    }
}
