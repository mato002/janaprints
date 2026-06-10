<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ExportsTabularIndex;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Support\Export\TabularExportWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BranchController extends Controller
{
    use ExportsTabularIndex;
    use ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', Branch::class);

        $branches = $this->scopeToTenant(
            Branch::query()->with('company')
        )->latest()->paginate(15);

        return view('admin.branches.index', compact('branches'));
    }

    public function export(Request $request, string $format, TabularExportWriter $writer): StreamedResponse
    {
        $this->authorize('viewAny', Branch::class);

        $branches = $this->scopeToTenant(
            Branch::query()->with('company')
        )->latest()->get();

        $headers = [__('Branch'), __('Code'), __('Company')];
        $rows = $branches->map(fn (Branch $branch) => [
            $branch->name,
            $branch->code,
            $branch->company?->name ?? '',
        ])->all();

        return $this->downloadTabularExport($writer, $format, 'branches', $headers, $rows, __('Branches'));
    }

    public function create(): View
    {
        $this->authorize('create', Branch::class);

        return view('admin.branches.create', ['companies' => $this->companies()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Branch::class);

        Branch::query()->create($this->validateBranch($request));

        return redirect()->route('admin.branches.index')->with('status', __('Branch created.'));
    }

    public function edit(Branch $branch): View
    {
        $this->authorize('update', $branch);

        return view('admin.branches.edit', [
            'branch' => $branch,
            'companies' => $this->companies(),
        ]);
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorize('update', $branch);

        $branch->update($this->validateBranch($request, $branch));

        return redirect()->route('admin.branches.index')->with('status', __('Branch updated.'));
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $this->authorize('delete', $branch);

        $branch->delete();

        return redirect()->route('admin.branches.index')->with('status', __('Branch deleted.'));
    }

    protected function validateBranch(Request $request, ?Branch $branch = null): array
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
                Rule::unique('branches', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($branch?->id),
            ],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'is_head_office' => ['boolean'],
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
