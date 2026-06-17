<?php

namespace App\Support\Hr;

use App\Enums\PayrollGroup;
use App\Models\Hr\PayrollGroupDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PayrollGroupService
{
    /**
     * @return Collection<int, PayrollGroupDefinition>
     */
    public function activeForCompany(int $companyId): Collection
    {
        $this->ensureDefaults($companyId);

        return PayrollGroupDefinition::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, PayrollGroupDefinition>
     */
    public function allForCompany(int $companyId): Collection
    {
        $this->ensureDefaults($companyId);

        return PayrollGroupDefinition::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();
    }

    public function ensureDefaults(int $companyId): void
    {
        foreach (PayrollGroup::cases() as $group) {
            PayrollGroupDefinition::query()->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'code' => $group->value,
                ],
                [
                    'name' => $group->label(),
                    'is_active' => true,
                ],
            );
        }
    }

    public function label(int $companyId, ?string $code): ?string
    {
        if (blank($code)) {
            return null;
        }

        $definition = PayrollGroupDefinition::query()
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->first();

        if ($definition) {
            return $definition->name;
        }

        return PayrollGroup::tryFrom($code)?->label() ?? $code;
    }

    public function create(int $companyId, string $name, ?string $code = null): PayrollGroupDefinition
    {
        $this->ensureDefaults($companyId);

        $code = $this->normalizeCode($code ?: $name);

        if (PayrollGroupDefinition::query()->where('company_id', $companyId)->where('code', $code)->exists()) {
            throw ValidationException::withMessages([
                'code' => __('A payroll group with this code already exists.'),
            ]);
        }

        return PayrollGroupDefinition::query()->create([
            'company_id' => $companyId,
            'code' => $code,
            'name' => trim($name),
            'is_active' => true,
        ]);
    }

    public function deactivate(PayrollGroupDefinition $definition): PayrollGroupDefinition
    {
        $definition->update(['is_active' => false]);

        return $definition->fresh();
    }

    public function reactivate(PayrollGroupDefinition $definition): PayrollGroupDefinition
    {
        $definition->update(['is_active' => true]);

        return $definition->fresh();
    }

    protected function normalizeCode(string $value): string
    {
        $code = Str::slug($value, '_');

        if ($code === '') {
            $code = 'group_'.Str::lower(Str::random(6));
        }

        return Str::limit($code, 30, '');
    }
}
