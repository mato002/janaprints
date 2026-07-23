<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Production\WorkCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkCenterSetupController extends Controller
{
    use HandlesModalFormResponses, ScopesToTenant;

    public function create(): View
    {
        $this->authorize('create', WorkCenter::class);

        return view('admin.production.work-centers.create');
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', WorkCenter::class);

        $companyId = (int) tenant()->companyId();
        $branchId = (int) tenant()->branchId();
        $data = $this->validateWorkCenter($request, $companyId, $branchId);

        $workCenter = WorkCenter::query()->create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        return $this->modalOrRedirect(
            __('Work center created.'),
            redirect()->route('admin.production.work-centers.show', $workCenter),
        );
    }

    public function edit(WorkCenter $workCenter): View
    {
        $this->authorize('update', $workCenter);

        return view('admin.production.work-centers.edit', compact('workCenter'));
    }

    public function update(Request $request, WorkCenter $workCenter): RedirectResponse|Response
    {
        $this->authorize('update', $workCenter);

        $data = $this->validateWorkCenter($request, $workCenter->company_id, $workCenter->branch_id, $workCenter);
        $workCenter->update($data);

        return $this->modalOrRedirect(
            __('Work center updated.'),
            redirect()->route('admin.production.work-centers.show', $workCenter),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateWorkCenter(Request $request, int $companyId, int $branchId, ?WorkCenter $workCenter = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string', 'max:30',
                Rule::unique('work_centers', 'code')
                    ->where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->ignore($workCenter?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
            'requires_machine' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['requires_machine'] = $request->boolean('requires_machine', false);
        $data['code'] = strtoupper((string) $data['code']);

        return $data;
    }
}
