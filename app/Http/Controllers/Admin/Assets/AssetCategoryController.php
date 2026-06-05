<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\AssetType;
use App\Http\Controllers\Controller;
use App\Models\Assets\AssetCategory;
use App\Services\Assets\AssetCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetCategoryController extends Controller
{
    public function __construct(
        protected AssetCategoryService $categories,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', AssetCategory::class);

        $categories = AssetCategory::query()
            ->forTenant()
            ->notArchived()
            ->withCount('assets')
            ->orderBy('name')
            ->get();

        return view('admin.assets.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorize('create', AssetCategory::class);

        return view('admin.assets.categories.create', [
            'assetTypes' => AssetType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', AssetCategory::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:30'],
            'asset_type' => ['required', 'string'],
            'useful_life_years' => ['required', 'integer', 'min:1', 'max:100'],
            'default_gl_code' => ['nullable', 'string', 'max:20'],
            'depreciation_method' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
        ]);

        $this->categories->create($validated, (int) tenant()->companyId(), (int) auth()->id());

        return redirect()->route('admin.assets.categories.index')->with('status', __('Category created.'));
    }

    public function edit(AssetCategory $category): View
    {
        $this->authorize('update', $category);

        return view('admin.assets.categories.edit', [
            'category' => $category,
            'assetTypes' => AssetType::cases(),
        ]);
    }

    public function update(Request $request, AssetCategory $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:30'],
            'asset_type' => ['required', 'string'],
            'useful_life_years' => ['required', 'integer', 'min:1', 'max:100'],
            'default_gl_code' => ['nullable', 'string', 'max:20'],
            'depreciation_method' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->categories->update($category, $validated, (int) auth()->id());

        return redirect()->route('admin.assets.categories.index')->with('status', __('Category updated.'));
    }

    public function archive(AssetCategory $category): RedirectResponse
    {
        $this->authorize('archive', $category);

        $this->categories->archive($category, (int) auth()->id());

        return redirect()->route('admin.assets.categories.index')->with('status', __('Category archived.'));
    }
}
