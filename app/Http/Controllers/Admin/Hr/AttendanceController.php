<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\AttendanceCorrectionType;
use App\Enums\AttendanceStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Hr\AttendanceCorrection;
use App\Models\Hr\AttendanceRecord;
use App\Support\Hr\AttendanceExportService;
use App\Support\Hr\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected AttendanceService $attendance,
        protected AttendanceExportService $exports,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $filters = $request->only([
            'date', 'date_from', 'date_to', 'employee_id', 'branch_id',
            'department_id', 'shift_id', 'status',
        ]);

        if (empty($filters['date']) && empty($filters['date_from'])) {
            $filters['date'] = now()->toDateString();
        }

        return view('admin.hr.attendance.index', [
            'records' => $this->attendance->paginateRegister($companyId, $filters),
            'filters' => $filters,
            'formData' => $this->attendance->formData($companyId),
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', AttendanceRecord::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.attendance.create', [
            'formData' => $this->attendance->formData($companyId),
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', AttendanceRecord::class);

        $data = $this->validateManual($request);
        $this->attendance->createManual($data, $request->user());

        return redirect()
            ->route('admin.hr.attendance.index', ['date' => $data['attendance_date']])
            ->with('status', __('Attendance record saved.'));
    }

    public function adjustForm(AttendanceRecord $attendanceRecord): View
    {
        $this->authorize('adjust', $attendanceRecord);

        $attendanceRecord->load(['employee', 'shift', 'corrections.correctedBy']);

        return view('admin.hr.attendance.adjust', [
            'record' => $attendanceRecord,
            'correctionTypes' => AttendanceCorrectionType::cases(),
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function adjust(Request $request, AttendanceRecord $attendanceRecord): RedirectResponse
    {
        $this->authorize('adjust', $attendanceRecord);

        $data = $request->validate([
            'correction_type' => ['required', Rule::enum(AttendanceCorrectionType::class)],
            'reason' => ['required', 'string', 'max:2000'],
            'clock_in_at' => ['nullable', 'date'],
            'clock_out_at' => ['nullable', 'date', 'after:clock_in_at'],
            'status' => ['nullable', Rule::enum(AttendanceStatus::class)],
        ]);

        $requiresApproval = ! $request->user()->can('hr.attendance.approve');

        $this->attendance->adjust($attendanceRecord, $data, $request->user(), $requiresApproval);

        $message = $requiresApproval
            ? __('Attendance correction submitted for approval.')
            : __('Attendance record updated.');

        return redirect()
            ->route('admin.hr.attendance.index', ['date' => $attendanceRecord->attendance_date->toDateString()])
            ->with('status', $message);
    }

    public function approveCorrection(Request $request, AttendanceCorrection $correction): RedirectResponse
    {
        $record = $correction->attendanceRecord;
        $this->authorize('approve', $record);

        $this->attendance->approveCorrection($correction, $request->user());

        return redirect()
            ->route('admin.hr.attendance.index', ['date' => $record->attendance_date->toDateString()])
            ->with('status', __('Attendance correction approved.'));
    }

    public function clockIn(Request $request): RedirectResponse
    {
        $this->authorize('clock', AttendanceRecord::class);

        $employee = $request->user()->employee;

        if ($employee === null) {
            abort(403);
        }

        $this->attendance->clockIn($employee, $request->user(), $request);

        return back()->with('status', __('Clocked in successfully.'));
    }

    public function clockOut(Request $request): RedirectResponse
    {
        $this->authorize('clock', AttendanceRecord::class);

        $employee = $request->user()->employee;

        if ($employee === null) {
            abort(403);
        }

        $this->attendance->clockOut($employee, $request->user(), $request);

        return back()->with('status', __('Clocked out successfully.'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', AttendanceRecord::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $format = $request->input('format', 'csv');
        $filters = $request->only([
            'date', 'date_from', 'date_to', 'employee_id', 'branch_id',
            'department_id', 'shift_id', 'status',
        ]);

        return $this->exports->export($format, $companyId, $filters);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateManual(Request $request): array
    {
        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return $request->validate([
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'shift_id' => [
                'nullable',
                Rule::exists('shifts', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'attendance_date' => ['required', 'date'],
            'clock_in_at' => ['nullable', 'date'],
            'clock_out_at' => ['nullable', 'date', 'after:clock_in_at'],
            'status' => ['required', Rule::enum(AttendanceStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
