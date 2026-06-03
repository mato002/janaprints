<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyController extends Controller
{
    use ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', Company::class);

        $companies = $this->scopeToTenant(Company::query())->latest()->paginate(15);

        return view('admin.companies.index', compact('companies'));
    }

    public function create(): View
    {
        $this->authorize('create', Company::class);

        return view('admin.companies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Company::class);

        $validated = $this->validateCompany($request);
        Company::query()->create($validated);

        return redirect()->route('admin.companies.index')->with('status', __('Company created.'));
    }

    public function edit(Company $company): View
    {
        $this->authorize('update', $company);

        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $this->authorize('update', $company);

        $company->update($this->validateCompany($request));

        return redirect()->route('admin.companies.index')->with('status', __('Company updated.'));
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('delete', $company);

        $company->delete();

        return redirect()->route('admin.companies.index')->with('status', __('Company deleted.'));
    }

    protected function validateCompany(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('companies', 'code')->ignore($request->route('company'))],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'logo' => ['nullable', 'string', 'max:255'],
            'settings_json' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);
    }
}
