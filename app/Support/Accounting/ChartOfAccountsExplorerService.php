<?php

namespace App\Support\Accounting;

use App\Enums\GlAccountStatus;
use App\Enums\JournalStatus;
use App\Enums\NormalBalance;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\GlAccountGroup;
use App\Models\Accounting\GlAccountType;
use App\Support\Accounting\Reports\PostedJournalQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsExplorerService
{
    /**
     * @return array{total: int, active: int, locked: int, groups: int}
     */
    public function summaryStats(): array
    {
        $base = GlAccount::query()->forTenant();

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', GlAccountStatus::Active)->count(),
            'locked' => (clone $base)->where('status', GlAccountStatus::Locked)->count(),
            'groups' => GlAccountGroup::query()->forTenant()->count(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function typeRail(): array
    {
        $types = GlAccountType::query()->orderBy('sort_order')->get();

        $counts = GlAccount::query()
            ->forTenant()
            ->selectRaw('gl_account_type_id, COUNT(*) as aggregate')
            ->groupBy('gl_account_type_id')
            ->pluck('aggregate', 'gl_account_type_id');

        return $types->map(fn (GlAccountType $type) => [
            'id' => $type->id,
            'code' => $type->code->value,
            'name' => $type->name,
            'normal_balance' => $type->normal_balance->label(),
            'account_count' => (int) ($counts[$type->id] ?? 0),
        ])->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function groupsForType(int $typeId): array
    {
        $type = GlAccountType::query()->findOrFail($typeId);

        $rootGroup = GlAccountGroup::query()
            ->forTenant()
            ->where('gl_account_type_id', $type->id)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->first();

        if (! $rootGroup) {
            return $this->flatGroupsForType($type->id);
        }

        $childGroups = GlAccountGroup::query()
            ->forTenant()
            ->where('gl_account_type_id', $type->id)
            ->where('parent_id', $rootGroup->id)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        $groupIds = $childGroups->pluck('id')->push($rootGroup->id)->all();
        $accountCounts = $this->accountCountsByGroup($groupIds);

        $cards = $childGroups->map(fn (GlAccountGroup $group) => [
            'id' => $group->id,
            'code' => $group->code,
            'name' => $group->name,
            'account_count' => (int) ($accountCounts[$group->id] ?? 0),
            'is_direct' => false,
        ])->values()->all();

        $directCount = (int) ($accountCounts[$rootGroup->id] ?? 0);

        if ($directCount > 0) {
            $cards[] = [
                'id' => $rootGroup->id,
                'code' => $rootGroup->code,
                'name' => __('Direct accounts'),
                'account_count' => $directCount,
                'is_direct' => true,
            ];
        }

        return $cards;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function accountsForGroup(int $groupId): array
    {
        $group = GlAccountGroup::query()->forTenant()->findOrFail($groupId);

        $accounts = GlAccount::query()
            ->forTenant()
            ->where('gl_account_group_id', $group->id)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'normal_balance', 'status', 'parent_id', 'is_postable', 'is_system', 'gl_account_type_id']);

        if ($accounts->isEmpty()) {
            return [];
        }

        $txnCounts = $this->transactionCountsForAccounts($accounts->pluck('id')->all());

        return $this->flattenAccountHierarchy($accounts, $txnCounts);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function search(string $query, int $limit = 50): array
    {
        $term = trim($query);

        if ($term === '') {
            return [];
        }

        $like = '%'.$term.'%';

        $accounts = GlAccount::query()
            ->forTenant()
            ->with(['accountType:id,name,code', 'accountGroup:id,name,code,gl_account_type_id'])
            ->where(function ($q) use ($like) {
                $q->where('code', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhereHas('accountGroup', fn ($g) => $g
                        ->where('name', 'like', $like)
                        ->orWhere('code', 'like', $like));
            })
            ->orderBy('code')
            ->limit($limit)
            ->get();

        return $accounts->map(fn (GlAccount $account) => [
            'account_id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'type_id' => $account->gl_account_type_id,
            'type_name' => $account->accountType->name,
            'group_id' => $account->gl_account_group_id,
            'group_name' => $account->accountGroup?->name,
            'status' => $account->status->value,
        ])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function accountPanel(GlAccount $account): array
    {
        $account->load(['accountType', 'accountGroup', 'parent', 'branch']);

        $balance = $this->currentBalance($account);
        $recentTransactions = $this->recentTransactions($account->id, 8);

        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'type' => $account->accountType->name,
            'group' => $account->accountGroup?->name,
            'parent' => $account->parent
                ? ['code' => $account->parent->code, 'name' => $account->parent->name]
                : null,
            'branch' => $account->branch?->name,
            'normal_balance' => $account->normal_balance->label(),
            'current_balance' => $balance,
            'current_balance_formatted' => number_format($balance, 2),
            'status' => $account->status->value,
            'status_label' => $account->status->label(),
            'is_postable' => $account->is_postable,
            'is_system' => $account->is_system,
            'recent_transactions' => $recentTransactions,
            'urls' => [
                'show' => route('admin.accounting.accounts.show', $account),
                'edit' => route('admin.accounting.accounts.edit', $account),
                'create_child' => route('admin.accounting.accounts.create', [
                    'parent_id' => $account->id,
                    'type_id' => $account->gl_account_type_id,
                ]),
                'ledger' => route('admin.accounting.ledger.index', ['account_id' => $account->id]),
            ],
        ];
    }

    public function currentBalance(GlAccount $account): float
    {
        $row = PostedJournalQuery::aggregateByAccount([
            'company_id' => $account->company_id,
            'account_id' => $account->id,
        ])->first();

        if (! $row) {
            return 0.0;
        }

        $debit = (float) $row->total_debit;
        $credit = (float) $row->total_credit;

        return $account->normal_balance === NormalBalance::Debit
            ? round($debit - $credit, 2)
            : round($credit - $debit, 2);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function recentTransactions(int $accountId, int $limit): array
    {
        $lines = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->where('journal_lines.gl_account_id', $accountId)
            ->whereIn('journals.status', [
                JournalStatus::Posted->value,
                JournalStatus::Reversed->value,
            ])
            ->when($companyId = tenant()->companyId(), fn ($q) => $q->where('journals.company_id', $companyId))
            ->orderByDesc('journals.journal_date')
            ->orderByDesc('journals.journal_number')
            ->limit($limit)
            ->get([
                'journals.journal_number',
                'journals.journal_date',
                'journal_lines.debit',
                'journal_lines.credit',
                'journals.status',
            ]);

        return $lines->map(fn ($line) => [
            'journal_number' => $line->journal_number,
            'journal_date' => $line->journal_date,
            'debit' => (float) $line->debit,
            'credit' => (float) $line->credit,
            'status' => $line->status,
        ])->all();
    }

    /**
     * @param  list<int>  $groupIds
     * @return array<int, int>
     */
    protected function accountCountsByGroup(array $groupIds): array
    {
        if ($groupIds === []) {
            return [];
        }

        return GlAccount::query()
            ->forTenant()
            ->whereIn('gl_account_group_id', $groupIds)
            ->selectRaw('gl_account_group_id, COUNT(*) as aggregate')
            ->groupBy('gl_account_group_id')
            ->pluck('aggregate', 'gl_account_group_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    /**
     * @param  list<int>  $accountIds
     * @return array<int, int>
     */
    protected function transactionCountsForAccounts(array $accountIds): array
    {
        if ($accountIds === []) {
            return [];
        }

        $companyId = tenant()->companyId();

        return DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->whereIn('journal_lines.gl_account_id', $accountIds)
            ->whereIn('journals.status', [
                JournalStatus::Posted->value,
                JournalStatus::Reversed->value,
            ])
            ->when($companyId, fn ($q) => $q->where('journals.company_id', $companyId))
            ->selectRaw('journal_lines.gl_account_id, COUNT(*) as aggregate')
            ->groupBy('journal_lines.gl_account_id')
            ->pluck('aggregate', 'gl_account_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    /**
     * @param  Collection<int, GlAccount>  $accounts
     * @param  array<int, int>  $txnCounts
     * @return list<array<string, mixed>>
     */
    protected function flattenAccountHierarchy(Collection $accounts, array $txnCounts, ?int $parentId = null, int $depth = 0): array
    {
        $rows = [];

        foreach ($accounts->where('parent_id', $parentId) as $account) {
            $rows[] = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'normal_balance' => $account->normal_balance->label(),
                'status' => $account->status->value,
                'status_label' => $account->status->label(),
                'transactions_count' => $txnCounts[$account->id] ?? 0,
                'is_postable' => $account->is_postable,
                'is_system' => $account->is_system,
                'depth' => $depth,
                'urls' => [
                    'edit' => route('admin.accounting.accounts.edit', $account),
                    'create_child' => route('admin.accounting.accounts.create', [
                        'parent_id' => $account->id,
                        'type_id' => $account->gl_account_type_id,
                    ]),
                    'ledger' => route('admin.accounting.ledger.index', ['account_id' => $account->id]),
                ],
            ];

            foreach ($this->flattenAccountHierarchy($accounts, $txnCounts, $account->id, $depth + 1) as $child) {
                $rows[] = $child;
            }
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function flatGroupsForType(int $typeId): array
    {
        $groups = GlAccountGroup::query()
            ->forTenant()
            ->where('gl_account_type_id', $typeId)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        $counts = $this->accountCountsByGroup($groups->pluck('id')->all());

        return $groups->map(fn (GlAccountGroup $group) => [
            'id' => $group->id,
            'code' => $group->code,
            'name' => $group->name,
            'account_count' => (int) ($counts[$group->id] ?? 0),
            'is_direct' => false,
        ])->values()->all();
    }
}
