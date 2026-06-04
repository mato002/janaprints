<?php

namespace App\Support\Accounting;

use App\Enums\GlAccountStatus;
use App\Enums\GlAccountTypeCode;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\GlAccountGroup;
use App\Models\Accounting\GlAccountType;
use App\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JanaPrintsChartOfAccountsSeedService
{
    /** @var array<string, int> */
    protected array $groupIds = [];

    /** @var array<string, int> */
    protected array $accountIds = [];

    /** @var array<string, 'group'|'account'> */
    protected array $nodeKinds = [];

    /**
     * @return array<string, mixed>
     */
    public function seedCompany(Company $company, bool $force = false): array
    {
        $definition = config('jana_prints_chart_of_accounts');
        $nodes = $definition['nodes'] ?? [];
        $expected = $definition['expected'] ?? [];

        foreach ($nodes as $node) {
            $this->nodeKinds[$node['code']] = $node['kind'];
        }

        if (! $force && $this->isComplete($company->id, $expected)) {
            return $this->buildReport($company, 'skipped', [
                'message' => __('Chart of accounts already complete for :company.', ['company' => $company->code]),
            ]);
        }

        $types = GlAccountType::query()->get()->keyBy(fn (GlAccountType $t) => $t->code->value);
        $counts = ['groups_created' => 0, 'groups_updated' => 0, 'accounts_created' => 0, 'accounts_updated' => 0];

        DB::transaction(function () use ($company, $nodes, $types, &$counts) {
            $this->groupIds = [];
            $this->accountIds = [];

            foreach ($nodes as $node) {
                $type = $types[$node['type']] ?? null;
                if (! $type) {
                    continue;
                }

                if ($node['kind'] === 'group') {
                    $result = $this->upsertGroup($company->id, $type->id, $node);
                    $counts[$result === 'created' ? 'groups_created' : 'groups_updated']++;
                    continue;
                }

                $result = $this->upsertAccount($company->id, $type, $node);
                $counts[$result === 'created' ? 'accounts_created' : 'accounts_updated']++;
            }
        });

        return $this->buildReport($company, 'seeded', $counts);
    }

    public function seedByCompanyCode(string $companyCode, bool $force = false): array
    {
        $company = Company::query()->where('code', $companyCode)->first();

        if (! $company) {
            return [
                'status' => 'error',
                'message' => __('Company :code not found.', ['code' => $companyCode]),
            ];
        }

        return $this->seedCompany($company, $force);
    }

    /**
     * @param  array{groups?: int, accounts?: int}  $expected
     */
    protected function isComplete(int $companyId, array $expected): bool
    {
        $groupCount = GlAccountGroup::query()->where('company_id', $companyId)->count();
        $accountCount = GlAccount::query()
            ->where('company_id', $companyId)
            ->whereNull('branch_id')
            ->count();

        return $groupCount >= ($expected['groups'] ?? 0)
            && $accountCount >= ($expected['accounts'] ?? 0);
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected function upsertGroup(int $companyId, int $typeId, array $node): string
    {
        $parentGroupId = $node['parent_code']
            ? ($this->groupIds[$node['parent_code']] ?? null)
            : null;

        $existing = GlAccountGroup::query()
            ->where('company_id', $companyId)
            ->where('code', $node['code'])
            ->first();

        $group = GlAccountGroup::query()->updateOrCreate(
            ['company_id' => $companyId, 'code' => $node['code']],
            [
                'gl_account_type_id' => $typeId,
                'parent_id' => $parentGroupId,
                'name' => $node['name'],
                'sort_order' => $node['sort'] ?? 0,
            ],
        );

        $this->groupIds[$node['code']] = $group->id;

        return $existing ? 'updated' : 'created';
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected function upsertAccount(int $companyId, GlAccountType $type, array $node): string
    {
        $parentCode = $node['parent_code'] ?? null;
        $groupId = null;
        $parentAccountId = null;

        if ($parentCode) {
            if (($this->nodeKinds[$parentCode] ?? null) === 'group') {
                $groupId = $this->groupIds[$parentCode] ?? null;
            } else {
                $parentAccountId = $this->accountIds[$parentCode] ?? null;
                $parentAccount = $parentAccountId
                    ? GlAccount::query()->find($parentAccountId)
                    : null;
                $groupId = $parentAccount?->gl_account_group_id;
            }
        }

        $existing = GlAccount::query()
            ->where('company_id', $companyId)
            ->whereNull('branch_id')
            ->where('code', $node['code'])
            ->first();

        $account = GlAccount::query()->updateOrCreate(
            [
                'company_id' => $companyId,
                'branch_id' => null,
                'code' => $node['code'],
            ],
            [
                'gl_account_type_id' => $type->id,
                'gl_account_group_id' => $groupId,
                'parent_id' => $parentAccountId,
                'name' => $node['name'],
                'description' => null,
                'normal_balance' => $type->normal_balance->value,
                'status' => GlAccountStatus::Active,
                'is_system' => true,
                'is_postable' => (bool) ($node['postable'] ?? true),
                'sort_order' => $node['sort'] ?? 0,
            ],
        );

        $this->accountIds[$node['code']] = $account->id;

        return $existing ? 'updated' : 'created';
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function buildReport(Company $company, string $status, array $extra = []): array
    {
        $accounts = GlAccount::query()
            ->where('company_id', $company->id)
            ->whereNull('branch_id')
            ->with('accountType')
            ->get();

        $groups = GlAccountGroup::query()->where('company_id', $company->id)->get();
        $verification = $this->verifyHierarchy($accounts, $groups);
        $byType = $this->countsByType($accounts);

        return array_merge([
            'status' => $status,
            'company_id' => $company->id,
            'company_code' => $company->code,
            'version' => config('jana_prints_chart_of_accounts.version'),
            'account_counts' => [
                'total_accounts' => $accounts->count(),
                'active_accounts' => $accounts->where('status', GlAccountStatus::Active)->count(),
                'locked_accounts' => $accounts->where('status', GlAccountStatus::Locked)->count(),
                'postable_accounts' => $accounts->where('is_postable', true)->count(),
                'groups' => $groups->count(),
                'by_type' => $byType,
            ],
            'hierarchy' => $verification,
        ], $extra);
    }

    /**
     * @param  Collection<int, GlAccount>  $accounts
     * @param  Collection<int, GlAccountGroup>  $groups
     * @return array<string, mixed>
     */
    protected function verifyHierarchy(Collection $accounts, Collection $groups): array
    {
        $expectedCodes = collect(config('jana_prints_chart_of_accounts.nodes', []))
            ->pluck('code')
            ->all();

        $existingCodes = $accounts->pluck('code')
            ->merge($groups->pluck('code'))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $missing = array_values(array_diff($expectedCodes, $existingCodes));
        $brokenParents = [];

        foreach ($accounts as $account) {
            if ($account->parent_id && ! $accounts->contains(fn (GlAccount $a) => $a->id === $account->parent_id)) {
                $brokenParents[] = $account->code;
            }
        }

        foreach ($groups as $group) {
            if ($group->parent_id && ! $groups->contains(fn (GlAccountGroup $g) => $g->id === $group->parent_id)) {
                $brokenParents[] = 'group:'.$group->code;
            }
        }

        return [
            'valid' => $missing === [] && $brokenParents === [],
            'missing_codes' => $missing,
            'broken_parent_links' => $brokenParents,
            'expected_node_count' => count($expectedCodes),
            'present_node_count' => count($existingCodes),
        ];
    }

    /**
     * @param  Collection<int, GlAccount>  $accounts
     * @return array<string, int>
     */
    protected function countsByType(Collection $accounts): array
    {
        $counts = [];

        foreach (GlAccountTypeCode::cases() as $typeCode) {
            $counts[$typeCode->value] = $accounts
                ->filter(fn (GlAccount $a) => $a->accountType?->code === $typeCode)
                ->count();
        }

        return $counts;
    }

    /**
     * @param  array<string, mixed>  $report
     */
    public function printReport(array $report, callable $line): void
    {
        $line(__('Chart of Accounts seed report'));
        $line(str_repeat('─', 40));

        if (($report['status'] ?? '') === 'error') {
            $line($report['message'] ?? __('Unknown error.'));

            return;
        }

        $line(__('Company').': '.($report['company_code'] ?? '—'));
        $line(__('Status').': '.($report['status'] ?? '—'));
        $line(__('Version').': '.($report['version'] ?? '—'));

        if (! empty($report['message'])) {
            $line($report['message']);
        }

        $counts = $report['account_counts'] ?? [];
        $line('');
        $line(__('Account counts'));
        $line('  '.__('Total').': '.($counts['total_accounts'] ?? 0));
        $line('  '.__('Active').': '.($counts['active_accounts'] ?? 0));
        $line('  '.__('Locked').': '.($counts['locked_accounts'] ?? 0));
        $line('  '.__('Postable').': '.($counts['postable_accounts'] ?? 0));
        $line('  '.__('Groups').': '.($counts['groups'] ?? 0));

        if (! empty($counts['by_type'])) {
            $line('');
            $line(__('By account type'));
            foreach ($counts['by_type'] as $type => $count) {
                $line('  '.$type.': '.$count);
            }
        }

        $hierarchy = $report['hierarchy'] ?? [];
        $line('');
        $line(__('Hierarchy verification').': '.(($hierarchy['valid'] ?? false) ? __('OK') : __('FAILED')));

        if (! empty($hierarchy['missing_codes'])) {
            $line('  '.__('Missing').': '.implode(', ', $hierarchy['missing_codes']));
        }

        if (! empty($hierarchy['broken_parent_links'])) {
            $line('  '.__('Broken parents').': '.implode(', ', $hierarchy['broken_parent_links']));
        }
    }
}
