<?php

namespace App\Support\Hr;

use App\Enums\JobRequisitionStatus;
use App\Models\Hr\JobRequisition;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class JobRequisitionService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = JobRequisition::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['department', 'jobTitle', 'requestedBy']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('reference', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->orderByDesc('updated_at')->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data, User $user): JobRequisition
    {
        return JobRequisition::query()->create([
            'company_id' => $companyId,
            'branch_id' => $data['branch_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'job_title_id' => $data['job_title_id'] ?? null,
            'reference' => $this->nextReference($companyId),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'headcount' => (int) ($data['headcount'] ?? 1),
            'justification' => $data['justification'] ?? null,
            'status' => JobRequisitionStatus::Draft,
            'requested_by_user_id' => $user->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(JobRequisition $requisition, array $data): JobRequisition
    {
        if ($requisition->status !== JobRequisitionStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => __('Only draft requisitions can be edited.'),
            ]);
        }

        $requisition->update([
            'branch_id' => $data['branch_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'job_title_id' => $data['job_title_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'headcount' => (int) ($data['headcount'] ?? 1),
            'justification' => $data['justification'] ?? null,
        ]);

        return $requisition->fresh();
    }

    public function submit(JobRequisition $requisition): JobRequisition
    {
        if ($requisition->status !== JobRequisitionStatus::Draft) {
            throw ValidationException::withMessages(['status' => __('Only draft requisitions can be submitted.')]);
        }

        $requisition->update(['status' => JobRequisitionStatus::Submitted]);

        return $requisition->fresh();
    }

    public function approve(JobRequisition $requisition, User $user): JobRequisition
    {
        if ($requisition->status !== JobRequisitionStatus::Submitted) {
            throw ValidationException::withMessages(['status' => __('Only submitted requisitions can be approved.')]);
        }

        $requisition->update([
            'status' => JobRequisitionStatus::Approved,
            'approved_by_user_id' => $user->id,
            'approved_at' => now(),
        ]);

        return $requisition->fresh();
    }

    protected function nextReference(int $companyId): string
    {
        $count = JobRequisition::query()->where('company_id', $companyId)->count() + 1;

        return sprintf('REQ-%04d', $count);
    }
}
