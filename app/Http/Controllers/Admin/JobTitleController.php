<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobTitleLevel;
use App\Http\Controllers\Admin\Concerns\ExportsTabularIndex;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\JobTitle;
use App\Support\Export\TabularExportWriter;
use App\Support\Organization\JobTitleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobTitleController extends Controller
{
    use ExportsTabularIndex;
    use ScopesToTenant;

    public function __construct(
        protected JobTitleService $jobTitles,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', JobTitle::class);

        $companyId = (int) (tenant()->companyId() ?? auth()->user()->company_id);
        $titles = $this->jobTitles->titlesForCompany($companyId);

        return view('admin.job-titles.index', compact('titles'));
    }

    public function export(Request $request, string $format, TabularExportWriter $writer): StreamedResponse
    {
        $this->authorize('viewAny', JobTitle::class);

        $companyId = (int) (tenant()->companyId() ?? auth()->user()->company_id);
        $titles = $this->jobTitles->titlesForCompany($companyId);

        $headers = [__('Code'), __('Title'), __('Department'), __('Level'), __('Reports To'), __('Employees'), __('Status')];
        $rows = $titles->map(fn (JobTitle $jobTitle) => [
            $jobTitle->code,
            $jobTitle->title,
            $jobTitle->department?->name ?? '—',
            $jobTitle->level->label(),
            $jobTitle->reportsTo?->title ?? '—',
            (string) ($jobTitle->employees_count ?? 0),
            $jobTitle->is_active ? __('Active') : __('Inactive'),
        ])->all();

        return $this->downloadTabularExport($writer, $format, 'job-titles', $headers, $rows, __('Job Titles'));
    }

    public function hierarchy(): View
    {
        $this->authorize('viewAny', JobTitle::class);

        $companyId = (int) (tenant()->companyId() ?? auth()->user()->company_id);
        $hierarchy = $this->jobTitles->hierarchyTree($companyId);

        return view('admin.job-titles.hierarchy', compact('hierarchy'));
    }

    public function create(): View
    {
        $this->authorize('create', JobTitle::class);

        return view('admin.job-titles.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', JobTitle::class);

        JobTitle::query()->create($this->validateJobTitle($request));

        return redirect()->route('admin.job-titles.index')->with('status', __('Job title created.'));
    }

    public function edit(JobTitle $jobTitle): View
    {
        $this->authorize('update', $jobTitle);

        return view('admin.job-titles.edit', array_merge(
            ['jobTitle' => $jobTitle],
            $this->formData($jobTitle),
        ));
    }

    public function update(Request $request, JobTitle $jobTitle): RedirectResponse
    {
        $this->authorize('update', $jobTitle);

        $jobTitle->update($this->validateJobTitle($request, $jobTitle));

        return redirect()->route('admin.job-titles.index')->with('status', __('Job title updated.'));
    }

    public function deactivate(JobTitle $jobTitle): RedirectResponse
    {
        $this->authorize('deactivate', $jobTitle);

        $this->jobTitles->assertCanDeactivate($jobTitle);
        $jobTitle->update(['is_active' => false]);

        return redirect()->route('admin.job-titles.index')->with('status', __('Job title deactivated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateJobTitle(Request $request, ?JobTitle $jobTitle = null): array
    {
        $companyId = auth()->user()->hasRole('Super Admin')
            ? (int) $request->input('company_id')
            : (int) auth()->user()->company_id;

        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('job_titles', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($jobTitle?->id),
            ],
            'title' => ['required', 'string', 'max:120'],
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where('company_id', $companyId),
            ],
            'description' => ['nullable', 'string'],
            'level' => ['required', Rule::enum(JobTitleLevel::class)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'reports_to_job_title_id' => [
                'nullable',
                Rule::exists('job_titles', 'id')->where('company_id', $companyId),
                function (string $attribute, mixed $value, \Closure $fail) use ($jobTitle) {
                    if ($jobTitle && (int) $value === (int) $jobTitle->id) {
                        $fail(__('A job title cannot report to itself.'));
                    }
                },
            ],
            'approval_authority' => ['nullable', 'string', 'max:120'],
            'is_active' => ['boolean'],
        ]);

        $validated['sort_order'] ??= 100;
        $validated['is_active'] ??= true;

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(?JobTitle $jobTitle = null): array
    {
        $companyId = $jobTitle?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;

        return [
            'companies' => $this->companies(),
            'departments' => Department::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'levels' => JobTitleLevel::cases(),
            'reportsToOptions' => JobTitle::query()
                ->where('company_id', $companyId)
                ->when($jobTitle, fn ($query) => $query->where('id', '!=', $jobTitle->id))
                ->where('is_active', true)
                ->orderBy('title')
                ->get(),
            'approvalRoles' => Role::query()->where('guard_name', 'web')->orderBy('name')->pluck('name'),
        ];
    }

    protected function companies()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return Company::query()->where('is_active', true)->orderBy('name')->get();
        }

        return Company::query()->where('id', auth()->user()->company_id)->get();
    }
}
