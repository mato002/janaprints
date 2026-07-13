<?php

namespace App\Support\Accounting;

use App\Enums\BudgetStatus;
use App\Enums\NormalBalance;
use App\Models\Accounting\Budget;
use App\Models\Accounting\BudgetLine;
use App\Models\Accounting\GlAccount;
use App\Support\Accounting\Reports\PostedJournalQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BudgetService
{
    /**
     * @param  array{
     *     name: string,
     *     fiscal_year_id?: ?int,
     *     from_date: string,
     *     to_date: string,
     *     lines?: list<array{gl_account_id: int, period_month?: ?string, amount: float}>
     * }  $data
     */
    public function create(int $companyId, int $userId, array $data): Budget
    {
        return DB::transaction(function () use ($companyId, $userId, $data) {
            $budget = Budget::query()->create([
                'company_id' => $companyId,
                'name' => $data['name'],
                'fiscal_year_id' => $data['fiscal_year_id'] ?? null,
                'from_date' => $data['from_date'],
                'to_date' => $data['to_date'],
                'status' => BudgetStatus::Draft,
                'created_by' => $userId,
            ]);

            foreach ($data['lines'] ?? [] as $row) {
                $this->assertAccountBelongsToCompany($companyId, (int) $row['gl_account_id']);

                BudgetLine::query()->create([
                    'budget_id' => $budget->id,
                    'gl_account_id' => (int) $row['gl_account_id'],
                    'period_month' => $row['period_month'] ?? null,
                    'amount' => round((float) $row['amount'], 2),
                ]);
            }

            return $budget->fresh(['lines.glAccount', 'fiscalYear']);
        });
    }

    public function activate(Budget $budget): Budget
    {
        if ($budget->status === BudgetStatus::Active) {
            return $budget;
        }

        if ($budget->status === BudgetStatus::Closed) {
            throw ValidationException::withMessages([
                'status' => __('Closed budgets cannot be activated.'),
            ]);
        }

        if ($budget->lines()->count() === 0) {
            throw ValidationException::withMessages([
                'lines' => __('Add at least one budget line before activating.'),
            ]);
        }

        $budget->update(['status' => BudgetStatus::Active]);

        return $budget->fresh(['lines.glAccount']);
    }

    /**
     * Compare budget lines to posted journal aggregates for the budget period.
     *
     * @return array{budget: Budget, rows: list<array<string, mixed>>, totals: array<string, float>}
     */
    public function vsActual(Budget $budget): array
    {
        $budget->loadMissing(['lines.glAccount']);

        $accountIds = $budget->lines->pluck('gl_account_id')->unique()->values()->all();

        $actualByAccount = collect();
        if ($accountIds !== []) {
            $actualByAccount = PostedJournalQuery::aggregateByAccount([
                'company_id' => $budget->company_id,
                'from_date' => $budget->from_date->toDateString(),
                'to_date' => $budget->to_date->toDateString(),
            ])
                ->whereIn('journal_lines.gl_account_id', $accountIds)
                ->get()
                ->keyBy('gl_account_id');
        }

        $budgetByAccount = $budget->lines
            ->groupBy('gl_account_id')
            ->map(fn ($lines) => round($lines->sum(fn ($l) => (float) $l->amount), 2));

        $rows = [];
        $totalBudget = 0.0;
        $totalActual = 0.0;

        foreach ($budgetByAccount as $accountId => $budgetAmount) {
            $account = $budget->lines->firstWhere('gl_account_id', $accountId)?->glAccount;
            $actualRow = $actualByAccount->get($accountId);
            $actual = 0.0;

            if ($actualRow && $account) {
                $actual = match ($account->normal_balance) {
                    NormalBalance::Debit => round((float) $actualRow->total_debit - (float) $actualRow->total_credit, 2),
                    NormalBalance::Credit => round((float) $actualRow->total_credit - (float) $actualRow->total_debit, 2),
                };
            }

            $variance = round($budgetAmount - $actual, 2);
            $totalBudget += $budgetAmount;
            $totalActual += $actual;

            $rows[] = [
                'gl_account_id' => (int) $accountId,
                'account_code' => $account?->code,
                'account_name' => $account?->name,
                'budget' => $budgetAmount,
                'actual' => $actual,
                'variance' => $variance,
            ];
        }

        usort($rows, fn ($a, $b) => strcmp((string) $a['account_code'], (string) $b['account_code']));

        return [
            'budget' => $budget,
            'rows' => $rows,
            'totals' => [
                'budget' => round($totalBudget, 2),
                'actual' => round($totalActual, 2),
                'variance' => round($totalBudget - $totalActual, 2),
            ],
        ];
    }

    protected function assertAccountBelongsToCompany(int $companyId, int $accountId): void
    {
        $exists = GlAccount::query()
            ->where('company_id', $companyId)
            ->where('id', $accountId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'gl_account_id' => __('Invalid GL account for this company.'),
            ]);
        }
    }
}
