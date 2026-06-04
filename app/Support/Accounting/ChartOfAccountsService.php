<?php

namespace App\Support\Accounting;

use App\Enums\GlAccountStatus;
use App\Enums\GlAccountTypeCode;
use App\Enums\NormalBalance;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\GlAccountGroup;
use App\Models\Accounting\GlAccountType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChartOfAccountsService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createAccount(array $attributes): GlAccount
    {
        return DB::transaction(function () use ($attributes) {
            $parent = isset($attributes['parent_id'])
                ? GlAccount::query()->find($attributes['parent_id'])
                : null;

            $this->assertParentCompatible($parent, $attributes);
            $this->assertUniqueCode(
                (int) $attributes['company_id'],
                $attributes['branch_id'] ?? null,
                $attributes['code'],
            );

            $account = GlAccount::query()->create($attributes);

            if ($parent) {
                $parent->update(['is_postable' => false]);
            }

            return $account->load(['accountType', 'accountGroup', 'parent', 'branch']);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateAccount(GlAccount $account, array $attributes): GlAccount
    {
        $this->assertEditable($account);

        if (isset($attributes['parent_id']) && (int) $attributes['parent_id'] !== (int) $account->parent_id) {
            $parent = $attributes['parent_id']
                ? GlAccount::query()->find($attributes['parent_id'])
                : null;
            $this->assertParentCompatible($parent, array_merge($account->toArray(), $attributes));
            $this->assertNotCircular($account, $parent);
        }

        if (isset($attributes['code']) && $attributes['code'] !== $account->code) {
            $this->assertUniqueCode(
                $account->company_id,
                $attributes['branch_id'] ?? $account->branch_id,
                $attributes['code'],
                $account->id,
            );
        }

        $account->update($attributes);

        return $account->fresh(['accountType', 'accountGroup', 'parent', 'branch']);
    }

    public function deleteAccount(GlAccount $account): void
    {
        $this->assertEditable($account);

        if ($account->is_system) {
            throw ValidationException::withMessages([
                'account' => __('System accounts cannot be deleted.'),
            ]);
        }

        if ($account->children()->exists()) {
            throw ValidationException::withMessages([
                'account' => __('Remove child accounts before deleting this account.'),
            ]);
        }

        DB::transaction(function () use ($account) {
            $parentId = $account->parent_id;
            $account->delete();

            if ($parentId) {
                $parent = GlAccount::query()->find($parentId);
                if ($parent && ! $parent->children()->exists()) {
                    $parent->update(['is_postable' => true]);
                }
            }
        });
    }

    public function lockAccount(GlAccount $account): GlAccount
    {
        if ($account->status === GlAccountStatus::Locked) {
            return $account;
        }

        $account->update(['status' => GlAccountStatus::Locked]);

        return $account->fresh();
    }

    public function unlockAccount(GlAccount $account): GlAccount
    {
        if ($account->status !== GlAccountStatus::Locked) {
            return $account;
        }

        $account->update(['status' => GlAccountStatus::Active]);

        return $account->fresh();
    }

    public function resolveType(int $typeId): GlAccountType
    {
        return GlAccountType::query()->findOrFail($typeId);
    }

    public function defaultNormalBalanceForType(GlAccountType $type): NormalBalance
    {
        return $type->normal_balance;
    }

    public function assertUniqueCode(int $companyId, ?int $branchId, string $code, ?int $ignoreId = null): void
    {
        $exists = GlAccount::query()
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->when(
                $branchId === null,
                fn ($q) => $q->whereNull('branch_id'),
                fn ($q) => $q->where('branch_id', $branchId),
            )
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'code' => __('An account with this code already exists for this scope.'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function assertParentCompatible(?GlAccount $parent, array $attributes): void
    {
        if (! $parent) {
            return;
        }

        if ((int) $parent->company_id !== (int) $attributes['company_id']) {
            throw ValidationException::withMessages([
                'parent_id' => __('Parent account must belong to the same company.'),
            ]);
        }

        $parentBranch = $parent->branch_id;
        $childBranch = $attributes['branch_id'] ?? null;
        if ($parentBranch !== $childBranch) {
            throw ValidationException::withMessages([
                'parent_id' => __('Parent and child must share the same branch scope.'),
            ]);
        }

        if ((int) $parent->gl_account_type_id !== (int) $attributes['gl_account_type_id']) {
            throw ValidationException::withMessages([
                'parent_id' => __('Parent account must be the same account type.'),
            ]);
        }

        if ($parent->isLocked()) {
            throw ValidationException::withMessages([
                'parent_id' => __('Cannot attach to a locked parent account.'),
            ]);
        }
    }

    protected function assertNotCircular(GlAccount $account, ?GlAccount $parent): void
    {
        if (! $parent) {
            return;
        }

        $ancestor = $parent;
        while ($ancestor) {
            if ($ancestor->id === $account->id) {
                throw ValidationException::withMessages([
                    'parent_id' => __('An account cannot be its own ancestor.'),
                ]);
            }
            $ancestor = $ancestor->parent;
        }
    }

    protected function assertEditable(GlAccount $account): void
    {
        if ($account->isLocked()) {
            throw ValidationException::withMessages([
                'account' => __('Locked accounts cannot be modified.'),
            ]);
        }
    }

    /**
     * @return list<GlAccountTypeCode>
     */
    public static function typeCodes(): array
    {
        return GlAccountTypeCode::cases();
    }
}
