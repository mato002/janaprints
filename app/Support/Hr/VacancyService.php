<?php

namespace App\Support\Hr;

use App\Enums\VacancyStatus;
use App\Models\Hr\Vacancy;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class VacancyService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Vacancy::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['department', 'jobTitle', 'requisition'])
            ->withCount('applications');

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
     * @return list<Vacancy>
     */
    public function openVacancies(int $companyId): array
    {
        return Vacancy::query()
            ->where('company_id', $companyId)
            ->open()
            ->orderBy('title')
            ->get()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data, User $user): Vacancy
    {
        return Vacancy::query()->create([
            'company_id' => $companyId,
            'job_requisition_id' => $data['job_requisition_id'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'job_title_id' => $data['job_title_id'] ?? null,
            'reference' => $this->nextReference($companyId),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'positions' => (int) ($data['positions'] ?? 1),
            'status' => VacancyStatus::Draft,
            'closing_date' => $data['closing_date'] ?? null,
            'created_by_user_id' => $user->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Vacancy $vacancy, array $data): Vacancy
    {
        if (! in_array($vacancy->status, [VacancyStatus::Draft, VacancyStatus::Open], true)) {
            throw ValidationException::withMessages([
                'status' => __('This vacancy cannot be edited.'),
            ]);
        }

        $vacancy->update([
            'branch_id' => $data['branch_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'job_title_id' => $data['job_title_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'positions' => (int) ($data['positions'] ?? 1),
            'closing_date' => $data['closing_date'] ?? null,
        ]);

        return $vacancy->fresh();
    }

    public function publish(Vacancy $vacancy): Vacancy
    {
        if ($vacancy->status !== VacancyStatus::Draft) {
            throw ValidationException::withMessages(['status' => __('Only draft vacancies can be published.')]);
        }

        $vacancy->update([
            'status' => VacancyStatus::Open,
            'published_at' => now(),
        ]);

        return $vacancy->fresh();
    }

    public function close(Vacancy $vacancy): Vacancy
    {
        $vacancy->update(['status' => VacancyStatus::Closed]);

        return $vacancy->fresh();
    }

    protected function nextReference(int $companyId): string
    {
        $count = Vacancy::query()->where('company_id', $companyId)->count() + 1;

        return sprintf('VAC-%04d', $count);
    }
}
