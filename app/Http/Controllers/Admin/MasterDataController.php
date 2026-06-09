<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesSettingsScope;
use App\Http\Controllers\Controller;
use App\Models\MasterDataValue;
use App\Services\MasterData\MasterDataDependencyService;
use App\Services\MasterData\MasterDataImportExportService;
use App\Services\MasterData\MasterDataService;
use App\Support\MasterData\MasterDataRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    use ResolvesSettingsScope;

    public function __construct(
        protected MasterDataService $masterData,
        protected MasterDataImportExportService $importExport,
        protected MasterDataDependencyService $dependencies,
        protected MasterDataRegistry $registry,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MasterDataValue::class);

        $category = $request->string('category')->toString() ?: 'all';
        $status = $request->string('status')->toString() ?: 'all';
        $search = $request->string('search')->toString() ?: null;

        return view('admin.master-data.index', [
            'values' => $this->masterData->paginate(
                $category !== 'all' ? $category : null,
                $status !== 'all' ? $status : null,
                $search,
            ),
            'categories' => $this->registry->categoryOptions(),
            'category' => $category,
            'status' => $status,
            'search' => $search,
            'canCreate' => $request->user()->can('create', MasterDataValue::class),
            'canImport' => $request->user()->can('import', MasterDataValue::class),
            'canExport' => $request->user()->can('export', MasterDataValue::class),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', MasterDataValue::class);

        return view('admin.master-data.create', [
            'categories' => $this->registry->categoryOptions(),
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany((int) (tenant()->companyId() ?: auth()->user()->company_id)),
            'selectedCategory' => $request->string('category')->toString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MasterDataValue::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        $data = $request->validate([
            'category_key' => ['required', 'string', 'max:80'],
            'code' => [
                'required', 'string', 'max:80',
                Rule::unique('master_data_values', 'code')
                    ->where('company_id', $companyId)
                    ->where('category_key', $request->input('category_key')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
        ]);

        $this->masterData->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'category_key' => $data['category_key'],
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ], $request->user());

        return redirect()
            ->route('admin.master-data.index', ['category' => $data['category_key']])
            ->with('status', __('Master data value created.'));
    }

    public function edit(MasterDataValue $masterDataValue): View
    {
        $this->authorize('update', $masterDataValue);

        return view('admin.master-data.edit', [
            'value' => $masterDataValue,
            'categories' => $this->registry->categoryOptions(),
        ]);
    }

    public function update(Request $request, MasterDataValue $masterDataValue): RedirectResponse
    {
        $this->authorize('update', $masterDataValue);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
        ]);

        $this->masterData->update($masterDataValue, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ], $request->user());

        return redirect()
            ->route('admin.master-data.index', ['category' => $masterDataValue->category_key])
            ->with('status', __('Master data value updated.'));
    }

    public function deactivate(Request $request, MasterDataValue $masterDataValue): RedirectResponse
    {
        $this->authorize('deactivate', $masterDataValue);

        $this->masterData->deactivate($masterDataValue, $request->user());

        return back()->with('status', __('Master data value deactivated.'));
    }

    public function reactivate(MasterDataValue $masterDataValue): RedirectResponse
    {
        $this->authorize('deactivate', $masterDataValue);

        $this->masterData->reactivate($masterDataValue);

        return back()->with('status', __('Master data value reactivated.'));
    }

    public function dependencies(MasterDataValue $masterDataValue): JsonResponse
    {
        $this->authorize('view', $masterDataValue);

        return response()->json($this->dependencies->check($masterDataValue));
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', MasterDataValue::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $result = $this->importExport->import($request->file('file'), $companyId, $branchId, $request->user());

        return back()->with('status', __(':count master data row(s) imported.', ['count' => $result['imported']]));
    }

    public function export(Request $request, string $format = 'csv')
    {
        $this->authorize('export', MasterDataValue::class);

        $category = $request->string('category')->toString() ?: null;

        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        return $this->importExport->export($category !== 'all' ? $category : null, $format);
    }
}
