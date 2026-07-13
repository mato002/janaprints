<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Hr\AttendanceRecord;
use App\Models\Hr\Shift;
use App\Support\Hr\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AttendanceDashboardController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected AttendanceService $attendance,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $canViewShifts = $request->user()?->can('viewAny', Shift::class) ?? false;
        $tab = $this->resolveTab($request, $canViewShifts);
        $filters = $request->only([
            'date', 'date_from', 'date_to', 'employee_id', 'branch_id',
            'department_id', 'shift_id', 'status',
        ]);
        $date = $request->input('date', now()->toDateString());
        $metricFilters = $request->only(['branch_id', 'department_id', 'shift_id', 'employee_id']);

        $payload = [
            'stats' => $this->attendance->dashboardMetrics($companyId, Carbon::parse($date), $metricFilters),
            'filters' => array_merge($filters, ['date' => $filters['date'] ?? $date]),
            'formData' => $this->attendance->formData($companyId),
            'tab' => $tab,
            'canViewShifts' => $canViewShifts,
            'statuses' => AttendanceStatus::cases(),
        ];

        return view('admin.hr.attendance.dashboard', match ($tab) {
            'shifts' => array_merge($payload, $this->shiftsPayload()),
            default => array_merge($payload, $this->registerPayload($companyId, $payload['filters'])),
        });
    }

    protected function resolveTab(Request $request, bool $canViewShifts): string
    {
        $tab = $request->string('tab')->toString();

        if ($tab === 'shifts' && $canViewShifts) {
            return 'shifts';
        }

        return 'register';
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function registerPayload(int $companyId, array $filters): array
    {
        if (empty($filters['date']) && empty($filters['date_from'])) {
            $filters['date'] = now()->toDateString();
        }

        return [
            'records' => $this->attendance->paginateRegister($companyId, $filters),
            'filters' => $filters,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function shiftsPayload(): array
    {
        $this->authorize('viewAny', Shift::class);

        return [
            'shifts' => $this->scopeToTenant(Shift::query())
                ->orderBy('name')
                ->get(),
        ];
    }
}
