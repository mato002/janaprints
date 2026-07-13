<?php

namespace App\Support\Accounting;

use App\Enums\BankStatementStatus;
use App\Enums\JournalStatus;
use App\Enums\NormalBalance;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankReconciliation;
use App\Models\Accounting\BankStatement;
use App\Models\Accounting\BankStatementLine;
use App\Models\Accounting\JournalLine;
use App\Support\Accounting\Reports\PostedJournalQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BankReconciliationService
{
    /**
     * @param  array{
     *     bank_account_id: int,
     *     statement_date: string,
     *     opening_balance: float,
     *     closing_balance: float,
     *     notes?: ?string,
     *     lines?: list<array{line_date: string, description: string, reference?: ?string, amount: float}>
     * }  $data
     */
    public function createStatement(int $companyId, int $userId, array $data): BankStatement
    {
        $account = BankAccount::query()
            ->where('company_id', $companyId)
            ->where('id', $data['bank_account_id'])
            ->firstOrFail();

        return DB::transaction(function () use ($companyId, $userId, $data, $account) {
            $statement = BankStatement::query()->create([
                'company_id' => $companyId,
                'bank_account_id' => $account->id,
                'statement_date' => $data['statement_date'],
                'opening_balance' => round((float) $data['opening_balance'], 2),
                'closing_balance' => round((float) $data['closing_balance'], 2),
                'status' => BankStatementStatus::Draft,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            if (! empty($data['lines'])) {
                $this->importLines($statement, $data['lines']);
            }

            return $statement->fresh(['bankAccount', 'lines']);
        });
    }

    /**
     * @param  list<array{line_date: string, description: string, reference?: ?string, amount: float}>  $lines
     */
    public function importLines(BankStatement $statement, array $lines): BankStatement
    {
        if ($statement->status === BankStatementStatus::Reconciled) {
            throw ValidationException::withMessages([
                'statement' => __('Reconciled statements cannot be modified.'),
            ]);
        }

        return DB::transaction(function () use ($statement, $lines) {
            foreach ($lines as $row) {
                $amount = round((float) ($row['amount'] ?? 0), 2);
                if ($amount == 0.0) {
                    continue;
                }

                BankStatementLine::query()->create([
                    'bank_statement_id' => $statement->id,
                    'line_date' => $row['line_date'],
                    'description' => $row['description'],
                    'reference' => $row['reference'] ?? null,
                    'amount' => $amount,
                    'is_matched' => false,
                ]);
            }

            if ($statement->status === BankStatementStatus::Draft) {
                $statement->update(['status' => BankStatementStatus::InProgress]);
            }

            return $statement->fresh('lines');
        });
    }

    /**
     * Suggest GL journal line matches for unmatched statement lines (amount + date tolerance).
     *
     * @return list<array{statement_line_id: int, journal_line_id: int, score: float, journal_date: string, amount: float}>
     */
    public function suggestMatches(BankStatement $statement, int $dateToleranceDays = 3): array
    {
        $statement->loadMissing(['bankAccount', 'lines']);
        $glAccountId = (int) $statement->bankAccount->gl_account_id;

        $unmatched = $statement->lines->where('is_matched', false);
        if ($unmatched->isEmpty()) {
            return [];
        }

        $alreadyMatchedIds = BankStatementLine::query()
            ->whereNotNull('matched_journal_line_id')
            ->pluck('matched_journal_line_id')
            ->all();

        $candidates = JournalLine::query()
            ->where('gl_account_id', $glAccountId)
            ->whereHas('journal', function ($q) use ($statement) {
                $q->where('company_id', $statement->company_id)
                    ->whereIn('status', [JournalStatus::Posted->value, JournalStatus::Reversed->value]);
            })
            ->with('journal:id,journal_date,journal_number')
            ->when($alreadyMatchedIds !== [], fn ($q) => $q->whereNotIn('id', $alreadyMatchedIds))
            ->get();

        $suggestions = [];
        $usedCandidateIds = [];

        foreach ($unmatched as $line) {
            $signed = (float) $line->amount;
            $best = null;
            $bestScore = PHP_FLOAT_MAX;

            foreach ($candidates as $candidate) {
                if (isset($usedCandidateIds[$candidate->id])) {
                    continue;
                }

                $candidateSigned = round((float) $candidate->debit - (float) $candidate->credit, 2);
                if (abs($candidateSigned - $signed) > 0.01) {
                    continue;
                }

                $days = abs($line->line_date->diffInDays($candidate->journal->journal_date));
                if ($days > $dateToleranceDays) {
                    continue;
                }

                $score = (float) $days;
                if ($score < $bestScore) {
                    $bestScore = $score;
                    $best = $candidate;
                }
            }

            if ($best) {
                $usedCandidateIds[$best->id] = true;
                $suggestions[] = [
                    'statement_line_id' => $line->id,
                    'journal_line_id' => $best->id,
                    'score' => $bestScore,
                    'journal_date' => $best->journal->journal_date->toDateString(),
                    'amount' => round((float) $best->debit - (float) $best->credit, 2),
                ];
            }
        }

        return $suggestions;
    }

    public function match(BankStatementLine $line, JournalLine $journalLine): BankStatementLine
    {
        $line->loadMissing('statement.bankAccount');
        $statement = $line->statement;

        if ($statement->status === BankStatementStatus::Reconciled) {
            throw ValidationException::withMessages([
                'statement' => __('Reconciled statements cannot be modified.'),
            ]);
        }

        $journalLine->loadMissing('journal');

        if ((int) $journalLine->gl_account_id !== (int) $statement->bankAccount->gl_account_id) {
            throw ValidationException::withMessages([
                'journal_line' => __('Journal line is not on this bank GL account.'),
            ]);
        }

        if (! in_array($journalLine->journal->status, [JournalStatus::Posted, JournalStatus::Reversed], true)) {
            throw ValidationException::withMessages([
                'journal_line' => __('Only posted journal lines can be matched.'),
            ]);
        }

        $signed = round((float) $journalLine->debit - (float) $journalLine->credit, 2);
        if (abs($signed - (float) $line->amount) > 0.01) {
            throw ValidationException::withMessages([
                'amount' => __('Statement line amount does not match journal line.'),
            ]);
        }

        $already = BankStatementLine::query()
            ->where('matched_journal_line_id', $journalLine->id)
            ->where('id', '!=', $line->id)
            ->exists();

        if ($already) {
            throw ValidationException::withMessages([
                'journal_line' => __('This journal line is already matched.'),
            ]);
        }

        $line->update([
            'matched_journal_line_id' => $journalLine->id,
            'is_matched' => true,
        ]);

        if ($statement->status === BankStatementStatus::Draft) {
            $statement->update(['status' => BankStatementStatus::InProgress]);
        }

        return $line->fresh('matchedJournalLine');
    }

    public function unmatch(BankStatementLine $line): BankStatementLine
    {
        $line->loadMissing('statement');

        if ($line->statement->status === BankStatementStatus::Reconciled) {
            throw ValidationException::withMessages([
                'statement' => __('Reconciled statements cannot be modified.'),
            ]);
        }

        $line->update([
            'matched_journal_line_id' => null,
            'is_matched' => false,
        ]);

        return $line->fresh();
    }

    public function markReconciled(BankStatement $statement, int $userId): BankStatement
    {
        $statement->loadMissing(['bankAccount', 'lines']);

        if ($statement->status === BankStatementStatus::Reconciled) {
            return $statement;
        }

        $unmatched = $statement->lines->where('is_matched', false)->count();
        if ($unmatched > 0) {
            throw ValidationException::withMessages([
                'lines' => __('All statement lines must be matched before reconciling.'),
            ]);
        }

        $glBalance = $this->glBalanceAsOf(
            (int) $statement->company_id,
            (int) $statement->bankAccount->gl_account_id,
            $statement->statement_date->toDateString(),
        );

        $difference = round((float) $statement->closing_balance - $glBalance, 2);
        if (abs($difference) > 0.01) {
            throw ValidationException::withMessages([
                'closing_balance' => __('Statement closing balance (:statement) does not match GL balance (:gl).', [
                    'statement' => number_format((float) $statement->closing_balance, 2),
                    'gl' => number_format($glBalance, 2),
                ]),
            ]);
        }

        return DB::transaction(function () use ($statement, $userId, $glBalance, $difference) {
            $statement->update([
                'status' => BankStatementStatus::Reconciled,
                'reconciled_at' => now(),
            ]);

            BankReconciliation::query()->updateOrCreate(
                ['bank_statement_id' => $statement->id],
                [
                    'company_id' => $statement->company_id,
                    'statement_closing_balance' => $statement->closing_balance,
                    'gl_closing_balance' => $glBalance,
                    'difference' => $difference,
                    'reconciled_at' => now(),
                    'reconciled_by' => $userId,
                ],
            );

            return $statement->fresh(['bankAccount', 'lines', 'reconciliation']);
        });
    }

    public function glBalanceAsOf(int $companyId, int $glAccountId, string $asOfDate): float
    {
        $row = PostedJournalQuery::aggregateByAccount([
            'company_id' => $companyId,
            'account_id' => $glAccountId,
            'as_of_date' => $asOfDate,
        ])->first();

        if (! $row) {
            return 0.0;
        }

        return LedgerSignedBalance::balanceSheetAmount(
            (float) $row->total_debit,
            (float) $row->total_credit,
            NormalBalance::from($row->normal_balance),
        );
    }
}
