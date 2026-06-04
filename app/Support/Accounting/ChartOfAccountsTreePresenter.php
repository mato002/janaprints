<?php

namespace App\Support\Accounting;

use App\Models\Accounting\GlAccount;
use App\Models\Accounting\GlAccountGroup;
use App\Models\Accounting\GlAccountType;
use Illuminate\Support\Collection;

class ChartOfAccountsTreePresenter
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function build(): Collection
    {
        $types = GlAccountType::query()->orderBy('sort_order')->get();
        $groups = GlAccountGroup::query()->forTenant()->orderBy('sort_order')->orderBy('code')->get();
        $accounts = GlAccount::query()
            ->forTenant()
            ->with(['accountType', 'accountGroup', 'branch'])
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        return $types->map(function (GlAccountType $type) use ($groups, $accounts) {
            $typeGroups = $groups->where('gl_account_type_id', $type->id);
            $typeAccounts = $accounts->where('gl_account_type_id', $type->id);

            return [
                'type' => $type,
                'groups' => $this->nestGroups($typeGroups),
                'accounts' => $this->nestAccounts($typeAccounts),
                'account_count' => $typeAccounts->count(),
            ];
        });
    }

    /**
     * @param  Collection<int, GlAccountGroup>  $groups
     * @return list<array<string, mixed>>
     */
    protected function nestGroups(Collection $groups, ?int $parentId = null): array
    {
        return $groups
            ->where('parent_id', $parentId)
            ->map(fn (GlAccountGroup $group) => [
                'group' => $group,
                'children' => $this->nestGroups($groups, $group->id),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, GlAccount>  $accounts
     * @return list<array<string, mixed>>
     */
    protected function nestAccounts(Collection $accounts, ?int $parentId = null): array
    {
        return $accounts
            ->where('parent_id', $parentId)
            ->map(fn (GlAccount $account) => [
                'account' => $account,
                'children' => $this->nestAccounts($accounts, $account->id),
            ])
            ->values()
            ->all();
    }
}
