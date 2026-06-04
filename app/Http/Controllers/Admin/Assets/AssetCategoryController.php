<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetCategoryController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->can('assets.view'), 403);

        $categories = AssetCategory::query()
            ->forTenant()
            ->withCount('assets')
            ->orderBy('name')
            ->get();

        return view('admin.assets.categories.index', compact('categories'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->can('assets.create'), 403);

        return view('admin.assets.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('assets.create'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:30'],
            'useful_life_months' => ['required', 'integer', 'min:1'],
            'default_gl_code' => ['nullable', 'string', 'max:20'],
            'depreciation_method' => ['nullable', 'string', 'max:30'],
        ]);

        AssetCategory::query()->create([
            ...$validated,
            'company_id' => tenant()->companyId(),
            'depreciation_method' => $validated['depreciation_method'] ?? 'straight_line',
            'is_active' => true,
        ]);

        return redirect()->route('admin.assets.categories.index')->with('status', __('Category created.'));
    }
}
