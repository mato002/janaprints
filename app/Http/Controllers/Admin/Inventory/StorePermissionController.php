<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Warehouse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class StorePermissionController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('viewAny', Warehouse::class);

        $permissions = [
            'inventory.view' => __('View stores and balances'),
            'inventory.create' => __('Create stores and items'),
            'inventory.edit' => __('Edit stores and assign managers'),
            'inventory.receive' => __('Receive stock'),
            'inventory.issue' => __('Issue stock'),
            'inventory.adjust' => __('Adjust stock'),
            'inventory.transfer' => __('Transfer stock between stores'),
            'inventory.delete' => __('Delete inventory records'),
        ];

        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return view('admin.inventory.store.permissions', compact('permissions', 'roles'));
    }
}
