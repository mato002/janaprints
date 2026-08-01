<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandController extends Controller
{
    use ResolvesInventoryTenant;

    public function index(): View
    {
        abort_unless(auth()->user()?->can('catalogue.view'), 403);

        $brands = Brand::query()->forTenant()->withCount('items')->orderBy('name')->paginate(15);

        return view('admin.inventory.catalogue.brands.index', compact('brands'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        return view('admin.inventory.catalogue.brands.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        $data = $this->validateBrand($request, $companyId, $branchId);
        unset($data['logo']);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('catalogue/brands', 'public');
        }

        Brand::query()->create([...$data, 'company_id' => $companyId, 'branch_id' => $branchId]);

        return redirect()->route('admin.inventory.catalogue.brands.index')->with('status', __('Brand created.'));
    }

    public function edit(Brand $brand): View
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($brand);

        return view('admin.inventory.catalogue.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($brand);

        $data = $this->validateBrand($request, $brand->company_id, $brand->branch_id, $brand);
        unset($data['logo']);

        if ($request->hasFile('logo')) {
            if ($brand->logo_path) {
                Storage::disk('public')->delete($brand->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store('catalogue/brands', 'public');
        }

        $brand->update($data);

        return redirect()->route('admin.inventory.catalogue.brands.index')->with('status', __('Brand updated.'));
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.delete'), 403);
        $this->ensureTenant($brand);

        if ($brand->items()->exists()) {
            return back()->withErrors(['brand' => __('Brand is in use and cannot be deleted. Deactivate it instead.')]);
        }

        if ($brand->logo_path) {
            Storage::disk('public')->delete($brand->logo_path);
        }

        $brand->delete();

        return back()->with('status', __('Brand removed.'));
    }

    protected function validateBrand(Request $request, int $companyId, int $branchId, ?Brand $brand = null): array
    {
        $validated = $request->validate([
            'code' => array_merge(
                $this->nullableCodeRules(50),
                [Rule::unique('brands', 'code')->where('company_id', $companyId)->where('branch_id', $branchId)->ignore($brand)],
            ),
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        $validated['code'] = $this->resolveBranchScopedCode(
            $request,
            'name',
            Brand::class,
            $companyId,
            $branchId,
            $brand?->id,
        );

        return $validated;
    }

    protected function ensureTenant(Brand $brand): void
    {
        abort_unless($brand->company_id === tenant()->companyId() && $brand->branch_id === tenant()->branchId(), 404);
    }
}
