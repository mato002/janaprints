<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\TrainingProgramStatus;
use App\Enums\TrainingType;
use App\Http\Controllers\Controller;
use App\Models\Hr\TrainingProgram;
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
        $this->authorize('viewAny', TrainingProgram::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.training.programs.index', [
            'programs' => $this->programs->paginate($companyId, $request->only(['type', 'search', 'status'])),
            'filters' => $request->only(['type', 'search', 'status']),
            'types' => TrainingType::cases(),
            'statuses' => TrainingProgramStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', TrainingProgram::class);

        return view('admin.hr.training.programs.create', [
            'types' => TrainingType::cases(),
            'statuses' => TrainingProgramStatus::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TrainingProgram::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $validated = $this->validateProgram($request);

        $program = $this->programs->create($companyId, $validated);

        return redirect()
            ->route('admin.hr.training.programs.show', $program)
            ->with('status', __('Training program created.'));
    }

    public function show(TrainingProgram $program): View
    {
        $this->authorize('view', $program);

        $program = $this->programs->findForShow($program);
        $stats = $this->programs->programStats($program);

        return view('admin.hr.training.programs.show', [
            'program' => $program,
            'stats' => $stats,
        ]);
    }

    public function edit(TrainingProgram $program): View
    {
        $this->authorize('update', $program);

        return view('admin.hr.training.programs.edit', [
            'program' => $program,
            'types' => TrainingType::cases(),
            'statuses' => TrainingProgramStatus::cases(),
        ]);
    }

    public function update(Request $request, TrainingProgram $program): RedirectResponse
    {
        $this->authorize('update', $program);

        $validated = $this->validateProgram($request, $program);
        $this->programs->update($program, $validated);

        return redirect()
            ->route('admin.hr.training.programs.show', $program)
            ->with('status', __('Training program updated.'));
    }

    public function activate(TrainingProgram $program): RedirectResponse
    {
        $this->authorize('update', $program);

        try {
            $this->programs->activate($program);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Program activated.'));
    }

    public function deactivate(TrainingProgram $program): RedirectResponse
    {
        $this->authorize('update', $program);

        try {
            $this->programs->deactivate($program);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Program deactivated.'));
    }

    public function complete(TrainingProgram $program): RedirectResponse
    {
        $this->authorize('update', $program);

        try {
            $this->programs->complete($program);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Program marked as completed.'));
    }

    public function reopen(TrainingProgram $program): RedirectResponse
    {
        $this->authorize('update', $program);

        try {
            $this->programs->reopen($program);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Program reopened.'));
    }

    public function duplicate(TrainingProgram $program): RedirectResponse
    {
        $this->authorize('update', $program);

        $copy = $this->programs->duplicate($program);

        return redirect()
            ->route('admin.hr.training.programs.show', $copy)
            ->with('status', __('Program duplicated.'));
    }

    public function archive(TrainingProgram $program): RedirectResponse
    {
        $this->authorize('archive', $program);

        try {
            $this->programs->archive($program);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Program archived.'));
    }

    public function evaluate(Request $request, TrainingProgram $program): RedirectResponse
    {
        $this->authorize('update', $program);

        $validated = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string', 'max:2000'],
            'employee_training_assignment_id' => [
                'nullable',
                Rule::exists('employee_training_assignments', 'id')
                    ->where('company_id', $program->company_id)
                    ->where('training_program_id', $program->id),
            ],
        ]);

        $this->programs->recordEvaluation($program, $validated, $request->user());

        return back()->with('status', __('Evaluation recorded.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateProgram(Request $request, ?TrainingProgram $program = null): array
    {
        return $request->validate([
            'type' => ['required', Rule::enum(TrainingType::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_hours' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'budget_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'scheduled_start_date' => ['nullable', 'date'],
            'scheduled_end_date' => ['nullable', 'date', 'after_or_equal:scheduled_start_date'],
            'requires_certification' => ['nullable', 'boolean'],
            'certificate_validity_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'skill_tags' => ['nullable', 'string', 'max:500'],
            'evaluation_instructions' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', Rule::enum(TrainingProgramStatus::class)],
        ]);
    }
}
