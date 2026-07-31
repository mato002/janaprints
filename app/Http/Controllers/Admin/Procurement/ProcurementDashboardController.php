<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Vendor;
use Illuminate\Http\RedirectResponse;

class ProcurementDashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $this->authorize('viewAny', Vendor::class);

        return redirect()->route('admin.procurement.desk');
    }
}
