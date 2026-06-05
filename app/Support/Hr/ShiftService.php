<?php

namespace App\Support\Hr;

use App\Enums\ShiftType;
use App\Models\Company;
use App\Models\Hr\Shift;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class ShiftService
{
    /**
     * @return Collection<int, Shift>
     */
    public function forCompany(int $companyId, bool $activeOnly = true): Collection
    {
        $query = Shift::query()
            ->where('company_id', $companyId)
            ->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    public function seedDefaultsForCompany(Company $company): void
    {
        $defaults = [
            [
                'code' => 'MORNING',
                'name' => 'Morning Shift',
                'shift_type' => ShiftType::Morning,
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'grace_minutes' => 10,
                'break_minutes' => 60,
            ],
            [
                'code' => 'DAY',
                'name' => 'Day Shift',
                'shift_type' => ShiftType::Day,
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'grace_minutes' => 15,
                'break_minutes' => 60,
            ],
            [
                'code' => 'NIGHT',
                'name' => 'Night Shift',
                'shift_type' => ShiftType::Night,
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'grace_minutes' => 10,
                'break_minutes' => 30,
            ],
        ];

        foreach ($defaults as $shift) {
            Shift::query()->firstOrCreate(
                ['company_id' => $company->id, 'code' => $shift['code']],
                array_merge($shift, ['is_active' => true]),
            );
        }
    }

    public function assertCanDeactivate(Shift $shift): void
    {
        if ($shift->employees()->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'shift' => __('Cannot deactivate a shift assigned to active employees.'),
            ]);
        }
    }
}
