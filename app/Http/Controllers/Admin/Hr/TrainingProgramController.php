<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\TrainingType;
use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Support\Hr\TrainingProgramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TrainingProgramController extends Controller
{
    public function __construct(
        protected TrainingProgramService $programs,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmployeeTrainingAssignment::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.training.programs.index', [
            'programs' => $this->programs->paginate($companyId, $request->only(['type', 'search'])),
            'filters' => $request->only(['type', 'search']),
            'types' => TrainingType::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', EmployeeTrainingAssignment::class);

        return view('admin.hr.training.programs.create', [
            'types' => TrainingType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', EmployeeTrainingAssignment::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        $validated = $request->validate([
            'type' => ['required', Rule::enum(TrainingType::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_hours' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'requires_certification' => ['nullable', 'boolean'],
            'certificate_validity_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'skill_tags' => ['nullable', 'string', 'max:500'],
        ]);

        $this->programs->create($companyId, $validated);

        return redirect()
            ->route('admin.hr.training.programs.index')
            ->with('status', __('Training program created.'));
    }
}
