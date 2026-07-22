<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\MaintenanceTechnicianStatus;
use App\Enums\MaintenanceTechnicianType;
use App\Http\Controllers\Controller;
use App\Models\Assets\MaintenanceTechnician;
use App\Models\Procurement\Vendor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceTechnicianController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', MaintenanceTechnician::class);

        return redirect()->route('admin.assets.maintenance.dashboard', array_merge(
            $request->query(),
            ['tab' => 'technicians'],
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MaintenanceTechnician::class);

        $validated = $request->validate([
            'technician_type' => ['required', Rule::enum(MaintenanceTechnicianType::class)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'specialization' => ['nullable', 'string', 'max:120'],
            'user_id' => ['nullable', 'exists:users,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
        ]);

        MaintenanceTechnician::query()->create([
            'company_id' => tenant()->companyId(),
            'branch_id' => tenant()->branchId(),
            'technician_type' => $validated['technician_type'],
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'vendor_id' => $validated['vendor_id'] ?? null,
            'status' => MaintenanceTechnicianStatus::Active,
        ]);

        return redirect()
            ->route('admin.assets.maintenance.dashboard', ['tab' => 'technicians'])
            ->with('status', __('Technician registered.'));
    }
}
