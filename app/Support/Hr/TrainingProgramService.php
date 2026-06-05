<?php

namespace App\Support\Hr;

use App\Models\Hr\TrainingProgram;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TrainingProgramService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = TrainingProgram::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->where('is_active', true);

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['search'])) {
            $query->where('title', 'like', '%'.$filters['search'].'%');
        }

        return $query->orderBy('title')->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data): TrainingProgram
    {
        return TrainingProgram::query()->create([
            'company_id' => $companyId,
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'duration_hours' => $data['duration_hours'] ?? 0,
            'requires_certification' => (bool) ($data['requires_certification'] ?? false),
            'certificate_validity_days' => $data['certificate_validity_days'] ?? null,
            'skill_tags' => $this->parseSkillTags($data['skill_tags'] ?? null),
        ]);
    }

    /**
     * @return list<string>
     */
    protected function parseSkillTags(?string $tags): array
    {
        if (! $tags) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $tags))));
    }
}
