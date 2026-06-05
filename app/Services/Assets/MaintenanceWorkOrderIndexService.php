<?php

namespace App\Services\Assets;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Models\Assets\MaintenanceWorkOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class MaintenanceWorkOrderIndexService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        return [
            'work_orders' => $this->paginate($request),
            'filters' => $this->filters($request),
            'statuses' => MaintenanceWorkOrderStatus::cases(),
            'priorities' => MaintenancePriority::cases(),
        ];
    }

    public function paginate(Request $request): LengthAwarePaginator
    {
        $filters = $this->filters($request);

        $query = MaintenanceWorkOrder::query()
            ->forTenant()
            ->with([
                'asset:id,asset_name,asset_number',
                'assignee:id,name',
                'vendor:id,vendor_name',
                'branch:id,name',
            ]);

        if (filled($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('work_order_no', 'like', "%{$search}%")
                    ->orWhereHas('asset', fn ($aq) => $aq
                        ->where('asset_name', 'like', "%{$search}%")
                        ->orWhere('asset_number', 'like', "%{$search}%"));
            });
        }

        if (filled($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (filled($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->latest('opened_at')->latest('id')
            ->paginate(config('platform.pagination.default', 15))
            ->withQueryString();
    }

    /**
     * @return array{search: ?string, status: ?string, priority: ?string}
     */
    protected function filters(Request $request): array
    {
        return [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'priority' => $request->query('priority'),
        ];
    }
}
