<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceType;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MaintenanceTechnician;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Services\Assets\MaintenanceNotificationService;
use App\Services\Assets\MaintenanceWorkOrderIndexService;
use App\Services\Assets\MaintenanceWorkOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceWorkOrderController extends Controller
{
    public function __construct(
        protected MaintenanceWorkOrderIndexService $index,
        protected MaintenanceWorkOrderService $workOrders,
        protected MaintenanceNotificationService $notifications,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MaintenanceWorkOrder::class);

        return view('admin.assets.maintenance.work-orders.index', $this->index->build($request));
    }

    public function create(): View
    {
        $this->authorize('create', MaintenanceWorkOrder::class);

        return view('admin.assets.maintenance.work-orders.create', [
            'assets' => FixedAsset::query()->forTenant()->notArchived()->orderBy('asset_name')->get(['id', 'asset_name', 'asset_number']),
            'vendors' => Vendor::query()->forTenant()->orderBy('vendor_name')->get(['id', 'vendor_name']),
            'types' => MaintenanceType::cases(),
            'priorities' => MaintenancePriority::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MaintenanceWorkOrder::class);

        $validated = $request->validate([
            'fixed_asset_id' => ['required', 'exists:fixed_assets,id'],
            'maintenance_type' => ['required', Rule::enum(MaintenanceType::class)],
            'priority' => ['required', Rule::enum(MaintenancePriority::class)],
            'description' => ['nullable', 'string'],
            'scheduled_for' => ['nullable', 'date'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'create_incident' => ['nullable', 'boolean'],
            'incident_title' => ['nullable', 'string', 'max:255'],
        ]);

        $order = $this->workOrders->create(
            $validated,
            (int) tenant()->companyId(),
            tenant()->branchId(),
            (int) auth()->id(),
        );

        if ($order->priority->isCritical() || $order->maintenance_type === MaintenanceType::Emergency) {
            $this->notifications->notifyCriticalBreakdown($order, (int) auth()->id());
        }

        return redirect()
            ->route('admin.assets.maintenance.work-orders.show', $order)
            ->with('status', __('Maintenance work order created.'));
    }

    public function show(MaintenanceWorkOrder $workOrder): View
    {
        $this->authorize('view', $workOrder);

        $workOrder->load([
            'asset.category',
            'asset.branch',
            'requester:id,name',
            'assignee:id,name',
            'technician',
            'vendor',
            'plan',
            'incident',
            'statusHistories.changer:id,name',
            'logs.logger:id,name',
            'downtimeRecords',
        ]);

        return view('admin.assets.maintenance.work-orders.show', [
            'workOrder' => $workOrder,
            'timeline' => $workOrder->asset->maintenanceTimelineEntries()
                ->where('maintenance_work_order_id', $workOrder->id)
                ->with('user:id,name')
                ->limit(20)
                ->get(),
            'technicians' => MaintenanceTechnician::query()->forTenant()->where('status', 'active')->orderBy('name')->get(),
            'users' => User::query()->where('company_id', $workOrder->company_id)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'vendors' => Vendor::query()->forTenant()->orderBy('vendor_name')->get(['id', 'vendor_name']),
            'statuses' => MaintenanceWorkOrderStatus::cases(),
        ]);
    }

    public function open(MaintenanceWorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('manage', $workOrder);
        $this->workOrders->open($workOrder, (int) auth()->id());

        return back()->with('status', __('Work order opened.'));
    }

    public function assign(Request $request, MaintenanceWorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('assign', $workOrder);

        $validated = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
            'assigned_technician_id' => ['nullable', 'exists:maintenance_technicians,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
        ]);

        $this->workOrders->assign(
            $workOrder,
            (int) auth()->id(),
            isset($validated['assigned_to']) ? (int) $validated['assigned_to'] : null,
            isset($validated['assigned_technician_id']) ? (int) $validated['assigned_technician_id'] : null,
            isset($validated['vendor_id']) ? (int) $validated['vendor_id'] : null,
        );

        return back()->with('status', __('Work order assigned.'));
    }

    public function start(MaintenanceWorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('manage', $workOrder);
        $this->workOrders->start($workOrder, (int) auth()->id());

        return back()->with('status', __('Maintenance started.'));
    }

    public function complete(Request $request, MaintenanceWorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('complete', $workOrder);

        $validated = $request->validate([
            'findings' => ['nullable', 'string'],
            'resolution' => ['nullable', 'string'],
        ]);

        $this->workOrders->complete($workOrder, (int) auth()->id(), $validated['findings'] ?? null, $validated['resolution'] ?? null);

        return back()->with('status', __('Work order completed.'));
    }

    public function close(MaintenanceWorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('close', $workOrder);
        $this->workOrders->close($workOrder, (int) auth()->id());

        return back()->with('status', __('Work order closed.'));
    }

    public function updateStatus(Request $request, MaintenanceWorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('manage', $workOrder);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(MaintenanceWorkOrderStatus::class)],
            'notes' => ['nullable', 'string'],
        ]);

        $this->workOrders->changeStatus(
            $workOrder,
            MaintenanceWorkOrderStatus::from($validated['status']),
            (int) auth()->id(),
            $validated['notes'] ?? null,
        );

        return back()->with('status', __('Status updated.'));
    }
}
