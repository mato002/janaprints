<?php

namespace App\Support\Accounting\Posting;

use App\Enums\GlAccountStatus;
use App\Enums\PostingAccountResolver;
use App\Enums\PostingLineSide;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\PostingAccountMapping;
use App\Models\Accounting\PostingRule;
use App\Models\Accounting\PostingTemplateLine;
use Illuminate\Support\Collection;

class PostingRuleValidationService
{
    /**
     * @param  Collection<int, PostingRule>  $rules
     * @return array<int, PostingRuleValidationResult>
     */
    public function validateMany(Collection $rules): array
    {
        $companyIds = $rules->pluck('company_id')->unique()->values();
        $mappings = PostingAccountMapping::query()
            ->whereIn('company_id', $companyIds)
            ->get()
            ->groupBy('company_id');

        $results = [];

        foreach ($rules as $rule) {
            $results[$rule->id] = $this->validate($rule, $mappings->get($rule->company_id, collect()));
        }

        return $results;
    }

    public function validate(PostingRule $rule, ?Collection $companyMappings = null): PostingRuleValidationResult
    {
        $issues = [];

        if (! $rule->is_active) {
            $issues[] = $this->issue('warning', 'inactive_rule', __('Rule is inactive.'));
        }

        $template = $rule->template;

        if (! $template) {
            return new PostingRuleValidationResult(
                PostingRuleValidationResult::STATUS_BROKEN,
                array_merge($issues, [$this->issue('error', 'template_missing', __('Posting template is missing.'))]),
            );
        }

        if (! $template->is_active) {
            $issues[] = $this->issue('warning', 'template_inactive', __('Posting template is inactive.'));
        }

        $template->loadMissing(['lines.glAccount.accountType']);

        if ($template->lines->isEmpty()) {
            $issues[] = $this->issue('error', 'template_no_lines', __('Posting template has no lines.'));
        }

        $hasDebit = false;
        $hasCredit = false;
        $companyMappings ??= PostingAccountMapping::query()
            ->where('company_id', $rule->company_id)
            ->get();

        foreach ($template->lines as $line) {
            if ($line->entry_side === PostingLineSide::Debit) {
                $hasDebit = true;
            }

            if ($line->entry_side === PostingLineSide::Credit) {
                $hasCredit = true;
            }

            $issues = array_merge($issues, $this->validateLine($line, $rule->company_id, $companyMappings));
        }

        if ($template->lines->isNotEmpty() && ! $hasDebit) {
            $issues[] = $this->issue('error', 'missing_debit', __('Template has no debit lines.'));
        }

        if ($template->lines->isNotEmpty() && ! $hasCredit) {
            $issues[] = $this->issue('error', 'missing_credit', __('Template has no credit lines.'));
        }

        return new PostingRuleValidationResult($this->resolveStatus($issues), $issues);
    }

    /**
     * @return list<array{level: string, code: string, message: string}>
     */
    protected function validateLine(PostingTemplateLine $line, int $companyId, Collection $companyMappings): array
    {
        return match ($line->account_resolver) {
            PostingAccountResolver::FixedAccount => $this->validateFixedAccount($line),
            PostingAccountResolver::AccountKey => $this->validateAccountKey($line, $companyId, $companyMappings),
            PostingAccountResolver::ContextAccount => [
                $this->issue('warning', 'context_account', __('Line :line uses a runtime context account (:field).', [
                    'line' => $line->line_number,
                    'field' => $line->context_account_field ?? '—',
                ])),
            ],
        };
    }

    /**
     * @return list<array{level: string, code: string, message: string}>
     */
    protected function validateFixedAccount(PostingTemplateLine $line): array
    {
        if (! $line->gl_account_id) {
            return [$this->issue('error', 'account_missing', __('Line :line requires a fixed GL account.', ['line' => $line->line_number]))];
        }

        $account = $line->glAccount ?? GlAccount::query()->find($line->gl_account_id);

        if (! $account) {
            return [$this->issue('error', 'account_missing', __('GL account on line :line does not exist.', ['line' => $line->line_number]))];
        }

        return $this->validateGlAccount($account, $line->line_number);
    }

    /**
     * @return list<array{level: string, code: string, message: string}>
     */
    protected function validateAccountKey(PostingTemplateLine $line, int $companyId, Collection $companyMappings): array
    {
        $accountKey = (string) $line->account_key;

        if ($accountKey === '') {
            return [$this->issue('error', 'account_key_missing', __('Line :line is missing an account key.', ['line' => $line->line_number]))];
        }

        $mapping = $companyMappings->firstWhere('account_key', $accountKey);

        if ($mapping?->gl_account_id) {
            $account = GlAccount::query()->find($mapping->gl_account_id);

            if (! $account) {
                return [$this->issue('error', 'account_missing', __('Mapped account for key :key does not exist.', ['key' => $accountKey]))];
            }

            return $this->validateGlAccount($account, $line->line_number);
        }

        $config = config("posting_account_keys.{$accountKey}");

        if (! $config || empty($config['default_code'])) {
            return [$this->issue('error', 'account_key_unknown', __('Unknown posting account key :key.', ['key' => $accountKey]))];
        }

        $account = GlAccount::query()
            ->where('company_id', $companyId)
            ->where('code', $config['default_code'])
            ->first();

        if (! $account) {
            return [$this->issue('error', 'account_missing', __('GL account :code for key :key is not configured.', [
                'code' => $config['default_code'],
                'key' => $accountKey,
            ]))];
        }

        return $this->validateGlAccount($account, $line->line_number);
    }

    /**
     * @return list<array{level: string, code: string, message: string}>
     */
    protected function validateGlAccount(GlAccount $account, int $lineNumber): array
    {
        $issues = [];

        if ($account->status === GlAccountStatus::Locked) {
            $issues[] = $this->issue('error', 'account_locked', __('Account :code on line :line is locked.', [
                'code' => $account->code,
                'line' => $lineNumber,
            ]));
        } elseif ($account->status === GlAccountStatus::Inactive) {
            $issues[] = $this->issue('warning', 'account_inactive', __('Account :code on line :line is inactive.', [
                'code' => $account->code,
                'line' => $lineNumber,
            ]));
        }

        if (! $account->is_postable) {
            $issues[] = $this->issue('warning', 'account_not_postable', __('Account :code on line :line is not postable.', [
                'code' => $account->code,
                'line' => $lineNumber,
            ]));
        }

        return $issues;
    }

    /**
     * @param  list<array{level: string, code: string, message: string}>  $issues
     */
    protected function resolveStatus(array $issues): string
    {
        if (collect($issues)->contains(fn ($issue) => $issue['level'] === 'error')) {
            return PostingRuleValidationResult::STATUS_BROKEN;
        }

        if ($issues !== []) {
            return PostingRuleValidationResult::STATUS_WARNING;
        }

        return PostingRuleValidationResult::STATUS_VALID;
    }

    /**
     * @return array{level: string, code: string, message: string}
     */
    protected function issue(string $level, string $code, string $message): array
    {
        return compact('level', 'code', 'message');
    }
}
