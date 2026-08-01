<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\ShiftType;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Concerns\ResolvesEntityCode;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Hr\Shift;
use App\Support\Hr\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShiftController extends Controller
{
    use ResolvesEntityCode;
    use ScopesToTenant;

    public function __construct(
        protected ShiftService $shifts,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Shift::class);

        $shifts = $this->scopeToTenant(Shift::query())
            ->orderBy('name')
            ->get();

        return view('admin.hr.shifts.index', [
            'shifts' => $shifts,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Shift::class);

        return view('admin.hr.shifts.create', [
            'shiftTypes' => ShiftType::cases(),
            'companies' => tenant()->isSuperAdmin && ! tenant()->hasCompany()
                ? Company::query()->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Shift::class);

        $data = $this->validateShift($request);
        Shift::query()->create($data);

        return redirect()
            ->route('admin.hr.attendance.dashboard', ['tab' => 'shifts', 'embedded' => request('embedded')])
            ->with('status', __('Shift created.'));
    }

    public function edit(Shift $shift): View
    {
        $this->authorize('update', $shift);

        return view('admin.hr.shifts.edit', [
            'shift' => $shift,
            'shiftTypes' => ShiftType::cases(),
            'companies' => tenant()->isSuperAdmin && ! tenant()->hasCompany()
                ? Company::query()->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorize('update', $shift);

        $shift->update($this->validateShift($request, $shift));

        return redirect()
            ->route('admin.hr.attendance.dashboard', ['tab' => 'shifts', 'embedded' => request('embedded')])
            ->with('status', __('Shift updated.'));
    }

    public function deactivate(Shift $shift): RedirectResponse
    {
        $this->authorize('update', $shift);

        $this->shifts->assertCanDeactivate($shift);
        $shift->update(['is_active' => false]);

        return redirect()
            ->route('admin.hr.attendance.dashboard', ['tab' => 'shifts', 'embedded' => request('embedded')])
            ->with('status', __('Shift deactivated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateShift(Request $request, ?Shift $shift = null): array
    {
        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        $rules = [
            'code' => array_merge(
                $this->nullableCodeRules(30),
                [
                    Rule::unique('shifts', 'code')
                        ->where(fn ($q) => $q->where('company_id', $request->input('company_id', $companyId)))
                        ->ignore($shift?->id),
                ],
            ),
            'name' => ['required', 'string', 'max:120'],
            'shift_type' => ['required', Rule::enum(ShiftType::class)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'grace_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'break_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if (tenant()->isSuperAdmin && ! tenant()->hasCompany()) {
            $rules['company_id'] = ['required', 'exists:companies,id'];
        }

        $data = $request->validate($rules);
        $data['company_id'] = $data['company_id'] ?? $companyId;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['code'] = $this->resolveCompanyScopedCode(
            $request,
            'name',
            Shift::class,
            (int) $data['company_id'],
            $shift?->id,
            30,
        );

        return $data;
    }
}
