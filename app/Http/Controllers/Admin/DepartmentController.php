<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    use ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', Department::class);

        $departments = $this->scopeToTenant(
            Department::query()->with('company')
        )->latest()->paginate(15);

        return view('admin.departments.index', compact('departments'));
    }

    public function create(): View
    {
        $this->authorize('create', Department::class);

        return view('admin.departments.create', ['companies' => $this->companies()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Department::class);

        Department::query()->create($this->validateDepartment($request));

        return redirect()->route('admin.departments.index')->with('status', __('Department created.'));
    }

    public function edit(Department $department): View
    {
        $this->authorize('update', $department);

        return view('admin.departments.edit', [
            'department' => $department,
            'companies' => $this->companies(),
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        $department->update($this->validateDepartment($request, $department));

        return redirect()->route('admin.departments.index')->with('status', __('Department updated.'));
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);

        $department->delete();

        return redirect()->route('admin.departments.index')->with('status', __('Department deleted.'));
    }

    protected function validateDepartment(Request $request, ?Department $department = null): array
    {
        $companyId = auth()->user()->hasRole('Super Admin')
            ? $request->input('company_id')
            : auth()->user()->company_id;

        return $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('departments', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($department?->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);
    }

    protected function companies()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return Company::query()->where('is_active', true)->orderBy('name')->get();
        }

        return Company::query()->where('id', auth()->user()->company_id)->get();
    }
}
