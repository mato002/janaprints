<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ActivityLog::class);

        $logs = ActivityLog::query()
            ->forTenant()
            ->with('user')
            ->latest('created_at')
            ->paginate(config('platform.pagination.admin', 20));

        return view('admin.activity-logs.index', compact('logs'));
    }
}
