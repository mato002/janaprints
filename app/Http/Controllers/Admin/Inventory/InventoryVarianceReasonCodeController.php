<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\InventoryVarianceReasonCategory;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryVarianceReasonCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryVarianceReasonCodeController extends Controller
{
    use ResolvesInventoryTenant, ScopesToTenant;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryVarianceReasonCode::class);

        $status = $request->string('status')->toString() ?: 'active';
        $search = $request->string('search')->toString() ?: null;

        $query = $this->scopeToTenant(InventoryVarianceReasonCode::query());

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($search) {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(function ($q) use ($like) {
                $q->where('code', 'like', $like)->orWhere('name', 'like', $like);
            });
        }

        $codes = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.inventory.control.variance-reason-codes.index', [
            'codes' => $codes,
            'status' => $status,
            'search' => $search,
            'categories' => InventoryVarianceReasonCategory::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', InventoryVarianceReasonCode::class);

        return view('admin.inventory.control.variance-reason-codes.create', [
            'categories' => InventoryVarianceReasonCategory::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InventoryVarianceReasonCode::class);

        ['companyId' => $companyId] = $this->tenantIds();
        $data = $this->validated($request, $companyId);

        InventoryVarianceReasonCode::query()->create([
            ...$data,
            'requires_comment' => $request->boolean('requires_comment'),
            'is_active' => $request->boolean('is_active'),
            'company_id' => $companyId,
        ]);

        return redirect()->route('admin.inventory.variance-reason-codes.index')
            ->with('status', __('Variance reason code created.'));
    }

    public function edit(InventoryVarianceReasonCode $varianceReasonCode): View
    {
        $this->authorize('update', $varianceReasonCode);

        return view('admin.inventory.control.variance-reason-codes.edit', [
            'code' => $varianceReasonCode,
            'categories' => InventoryVarianceReasonCategory::cases(),
        ]);
    }

    public function update(Request $request, InventoryVarianceReasonCode $varianceReasonCode): RedirectResponse
    {
        $this->authorize('update', $varianceReasonCode);

        $data = $this->validated($request, (int) $varianceReasonCode->company_id, $varianceReasonCode);
        $varianceReasonCode->update([
            ...$data,
            'requires_comment' => $request->boolean('requires_comment'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.inventory.variance-reason-codes.index')
            ->with('status', __('Variance reason code updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, int $companyId, ?InventoryVarianceReasonCode $existing = null): array
    {
        return $request->validate([
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('inventory_variance_reason_codes', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($existing?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(InventoryVarianceReasonCategory::class)],
            'requires_comment' => ['boolean'],
            'is_active' => ['boolean'],
        ]);
    }
}
