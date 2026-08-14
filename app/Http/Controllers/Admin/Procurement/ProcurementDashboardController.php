<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Vendor;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProcurementDashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        if (! $request->user()?->can('viewAny', Vendor::class)
            && ! $request->user()?->can('viewAny', PurchaseRequest::class)) {
            throw new AuthorizationException;
        }

        return redirect()->route('admin.procurement.desk');
    }
}
