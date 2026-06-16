<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ExportsTabularIndex;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Support\Branding\BrandingAssets;
use App\Support\Export\TabularExportWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompanyController extends Controller
{
    use ExportsTabularIndex;
    use HandlesModalFormResponses;
    use ScopesToTenant;

    public function __construct(
        protected BrandingAssets $assets,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Company::class);

        $companies = $this->scopeToTenant(Company::query())->latest()->paginate(15);

        return view('admin.companies.index', compact('companies'));
    }

    public function export(Request $request, string $format, TabularExportWriter $writer): StreamedResponse
    {
        $this->authorize('viewAny', Company::class);

        $companies = $this->scopeToTenant(Company::query())->latest()->get();

        $headers = [__('Name'), __('Code'), __('Status')];
        $rows = $companies->map(fn (Company $company) => [
            $company->name,
            $company->code,
            $company->is_active ? __('Active') : __('Inactive'),
        ])->all();

        return $this->downloadTabularExport($writer, $format, 'companies', $headers, $rows, __('Companies'));
    }

    public function create(): View
    {
        $this->authorize('create', Company::class);

        return view('admin.companies.create');
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', Company::class);

        $validated = $this->validateCompany($request);
        $company = Company::query()->create($validated);
        $this->syncBrandingUploads($request, $company);

        return $this->modalOrRedirect(
            __('Company created.'),
            redirect()->route('admin.companies.index'),
        );
    }

    public function edit(Company $company): View
    {
        $this->authorize('update', $company);

        return view('admin.companies.edit', [
            'company' => $company,
            'logoUrl' => $this->assets->url($company->logo),
            'faviconUrl' => $this->assets->url($company->favicon_path),
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse|Response
    {
        $this->authorize('update', $company);

        $company->update($this->validateCompany($request));
        $this->syncBrandingUploads($request, $company);

        return $this->modalOrRedirect(
            __('Company updated.'),
            redirect()->route('admin.companies.index'),
        );
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('delete', $company);

        $company->delete();

        return redirect()->route('admin.companies.index')->with('status', __('Company deleted.'));
    }

    protected function validateCompany(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('companies', 'code')->ignore($request->route('company'))],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'settings_json' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,svg', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
        ]);

        return Arr::only($validated, [
            'name',
            'code',
            'email',
            'phone',
            'address',
            'settings_json',
            'is_active',
        ]);
    }

    protected function syncBrandingUploads(Request $request, Company $company): void
    {
        $dirty = false;

        if ($request->boolean('remove_logo')) {
            $this->assets->delete($company->logo);
            $company->logo = null;
            $dirty = true;
        }

        if ($request->boolean('remove_favicon')) {
            $this->assets->delete($company->favicon_path);
            $company->favicon_path = null;
            $dirty = true;
        }

        if ($request->hasFile('logo')) {
            $company->logo = $this->assets->storeCompanyLogo($company, $request->file('logo'));
            $dirty = true;
        }

        if ($request->hasFile('favicon')) {
            $company->favicon_path = $this->assets->storeCompanyFavicon($company, $request->file('favicon'));
            $dirty = true;
        }

        if ($dirty) {
            $company->save();
        }
    }
}
