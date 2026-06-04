<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Controller;
use App\Models\Tax\TaxAuditLog;
use App\Models\Tax\TaxCode;
use Illuminate\View\View;

class TaxAuditLogController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAudit', TaxCode::class);

        $logs = TaxAuditLog::query()
            ->forTenant()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return view('admin.tax.audit.index', compact('logs'));
    }
}
