<?php

namespace App\Services\Operations;

use App\Enums\RetentionPolicyDomain;
use App\Models\Operations\RetentionPolicy;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DataRetentionService
{
    public function ensurePolicies(int $companyId): void
    {
        foreach (RetentionPolicyDomain::cases() as $domain) {
            $defaults = $this->defaultPolicy($domain);

            RetentionPolicy::query()->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'domain' => $domain,
                ],
                [
                    'archive_after_days' => $defaults['archive_after_days'],
                    'delete_after_days' => $defaults['delete_after_days'],
                    'retention_period_days' => $defaults['retention_period_days'],
                    'legal_hold' => false,
                ],
            );
        }
    }

    /**
     * @return Collection<int, RetentionPolicy>
     */
    public function policiesForCompany(int $companyId): Collection
    {
        $this->ensurePolicies($companyId);

        return RetentionPolicy::query()
            ->where('company_id', $companyId)
            ->orderBy('domain')
            ->get()
            ->sortBy(fn (RetentionPolicy $policy) => $policy->domain->value)
            ->values();
    }

    /**
     * @return array<string, int>
     */
    public function summaryMetrics(int $companyId): array
    {
        $policies = $this->policiesForCompany($companyId);

        return [
            'total' => $policies->count(),
            'legal_holds' => $policies->where('legal_hold', true)->count(),
            'longest_retention' => (int) $policies->max('retention_period_days'),
            'shortest_retention' => (int) $policies->min('retention_period_days'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePolicy(RetentionPolicy $policy, array $data, User $user): RetentionPolicy
    {
        $validated = $this->validatePolicyData($data, $policy);

        $policy->update([
            ...$validated,
            'updated_by' => $user->id,
        ]);

        return $policy->fresh(['updatedBy']);
    }

    public function findForCompany(int $companyId, int $policyId): RetentionPolicy
    {
        $policy = RetentionPolicy::query()
            ->where('company_id', $companyId)
            ->whereKey($policyId)
            ->first();

        if ($policy === null) {
            throw (new ModelNotFoundException)->setModel(RetentionPolicy::class, [$policyId]);
        }

        return $policy;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function validatePolicyData(array $data, RetentionPolicy $policy): array
    {
        $archiveAfter = array_key_exists('archive_after_days', $data) && $data['archive_after_days'] !== ''
            ? (int) $data['archive_after_days']
            : null;
        $deleteAfter = array_key_exists('delete_after_days', $data) && $data['delete_after_days'] !== ''
            ? (int) $data['delete_after_days']
            : null;
        $retentionPeriod = (int) ($data['retention_period_days'] ?? $policy->retention_period_days);
        $legalHold = (bool) ($data['legal_hold'] ?? false);

        if ($retentionPeriod < 1) {
            throw ValidationException::withMessages([
                'retention_period_days' => __('Retention period must be at least 1 day.'),
            ]);
        }

        if ($archiveAfter !== null && $archiveAfter < 1) {
            throw ValidationException::withMessages([
                'archive_after_days' => __('Archive after must be at least 1 day when set.'),
            ]);
        }

        if ($deleteAfter !== null && $deleteAfter < 1) {
            throw ValidationException::withMessages([
                'delete_after_days' => __('Delete after must be at least 1 day when set.'),
            ]);
        }

        if ($archiveAfter !== null && $archiveAfter > $retentionPeriod) {
            throw ValidationException::withMessages([
                'archive_after_days' => __('Archive after cannot exceed the retention period.'),
            ]);
        }

        if ($deleteAfter !== null && $deleteAfter < $retentionPeriod) {
            throw ValidationException::withMessages([
                'delete_after_days' => __('Delete after must be greater than or equal to the retention period.'),
            ]);
        }

        if ($archiveAfter !== null && $deleteAfter !== null && $archiveAfter > $deleteAfter) {
            throw ValidationException::withMessages([
                'archive_after_days' => __('Archive after cannot exceed delete after.'),
            ]);
        }

        if ($policy->legal_hold && ! $legalHold && ! ($data['release_legal_hold'] ?? false)) {
            throw ValidationException::withMessages([
                'legal_hold' => __('Legal hold is active. Confirm release before disabling.'),
            ]);
        }

        return [
            'archive_after_days' => $archiveAfter,
            'delete_after_days' => $deleteAfter,
            'retention_period_days' => $retentionPeriod,
            'legal_hold' => $legalHold,
        ];
    }

    /**
     * @return array{archive_after_days: ?int, delete_after_days: ?int, retention_period_days: int}
     */
    protected function defaultPolicy(RetentionPolicyDomain $domain): array
    {
        $defaults = config('platform.retention.defaults.'.$domain->value, []);

        return [
            'archive_after_days' => isset($defaults['archive_after_days']) ? (int) $defaults['archive_after_days'] : null,
            'delete_after_days' => isset($defaults['delete_after_days']) ? (int) $defaults['delete_after_days'] : null,
            'retention_period_days' => (int) ($defaults['retention_period_days'] ?? 365),
        ];
    }
}
