<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\CycleCountFrequency;
use App\Enums\CycleCountScheduleStatus;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\CycleCountSchedule;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\StockCount;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Support\Inventory\CycleCountService;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CycleCountController extends Controller
{
    use HandlesFormCustomFields, ResolvesInventoryTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', CycleCountSchedule::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $schedules = $this->scopeToTenant(
            CycleCountSchedule::query()->with(['warehouse', 'category', 'responsibleUser'])->latest()
        )->paginate(config('platform.pagination.default', 15));

        $overdue = CycleCountService::overdueSchedules($companyId, $branchId);

        $completedCounts = $this->scopeToTenant(
            StockCount::query()
                ->whereNotNull('cycle_count_schedule_id')
                ->with(['warehouse', 'cycleCountSchedule'])
                ->latest('count_date')
                ->limit(10)
        )->get();

        return view('admin.inventory.control.cycle-counts.index', compact('schedules', 'overdue', 'completedCounts'));
    }

    public function create(): View
    {
        $this->authorize('create', CycleCountSchedule::class);

        return view('admin.inventory.control.cycle-counts.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CycleCountSchedule::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $data = $this->formSettings->validateRequest($request, 'cycle_count_schedule.create', [
            'warehouse_id' => [Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)->where('is_active', true)],
            'frequency' => [Rule::enum(CycleCountFrequency::class)],
            'next_count_date' => ['date'],
            'inventory_category_id' => [Rule::exists('inventory_categories', 'id')->where('company_id', $companyId)],
            'responsible_user_id' => [Rule::exists('users', 'id')->where('company_id', $companyId)],
            'notes' => ['string', 'max:2000'],
        ], $companyId, $branchId);
        [$data, $customData] = $this->partitionCustomFields('cycle_count_schedule.create', $data, $companyId, $branchId);

        $schedule = CycleCountService::createSchedule(
            companyId: $companyId,
            branchId: $branchId,
            warehouseId: (int) $data['warehouse_id'],
            frequency: $data['frequency'],
            nextCountDate: $data['next_count_date'],
            responsibleUserId: (int) $data['responsible_user_id'],
            categoryId: $data['inventory_category_id'] ?? null,
            notes: $data['notes'] ?? null,
        );

        $this->syncCustomFields($schedule, 'cycle_count_schedule.create', $customData, $companyId);

        return redirect()->route('admin.inventory.cycle-counts.index')
            ->with('status', __('Cycle count schedule created.'));
    }

    public function show(CycleCountSchedule $cycleCount): View
    {
        $this->authorize('view', $cycleCount);

        $cycleCount->load(['warehouse', 'category', 'responsibleUser', 'stockCounts']);

        return view('admin.inventory.control.cycle-counts.show', ['schedule' => $cycleCount]);
    }

    public function edit(CycleCountSchedule $cycleCount): View
    {
        $this->authorize('update', $cycleCount);

        return view('admin.inventory.control.cycle-counts.edit', array_merge(
            ['schedule' => $cycleCount],
            $this->formMeta(),
        ));
    }

    public function update(Request $request, CycleCountSchedule $cycleCount): RedirectResponse
    {
        $this->authorize('update', $cycleCount);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $data = $request->validate([
            'warehouse_id' => [Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)->where('is_active', true)],
            'frequency' => ['required', Rule::enum(CycleCountFrequency::class)],
            'next_count_date' => ['required', 'date'],
            'inventory_category_id' => ['nullable', Rule::exists('inventory_categories', 'id')->where('company_id', $companyId)],
            'responsible_user_id' => ['required', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $cycleCount->update($data);

        return redirect()->route('admin.inventory.cycle-counts.show', $cycleCount)
            ->with('status', __('Schedule updated.'));
    }

    public function generate(CycleCountSchedule $cycleCount): RedirectResponse
    {
        $this->authorize('generate', $cycleCount);

        try {
            $count = CycleCountService::generateCount($cycleCount, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('admin.inventory.stock-counts.worksheet', $count)
            ->with('status', __('Stock count generated from schedule.'));
    }

    public function deactivate(CycleCountSchedule $cycleCount): RedirectResponse
    {
        $this->authorize('deactivate', $cycleCount);

        CycleCountService::deactivate($cycleCount, (int) auth()->id());

        return back()->with('status', __('Schedule deactivated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        return [
            'warehouses' => Warehouse::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'categories' => InventoryCategory::query()->forTenant()->orderBy('name')->get(),
            'users' => User::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'frequencies' => CycleCountFrequency::cases(),
            'formFields' => $this->formSettings->resolvedFields('cycle_count_schedule.create', $companyId, $branchId),
        ];
    }
}
