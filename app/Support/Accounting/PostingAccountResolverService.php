<?php

namespace App\Support\Accounting;

use App\Enums\PostingAccountResolver;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\PostingAccountMapping;
use App\Models\Accounting\PostingTemplateLine;
use App\Support\Accounting\Dto\PostingContext;
use Illuminate\Validation\ValidationException;

class PostingAccountResolverService
{
    public function resolve(PostingTemplateLine $line, PostingContext $context): int
    {
        return match ($line->account_resolver) {
            PostingAccountResolver::FixedAccount => $this->fixedAccount($line),
            PostingAccountResolver::AccountKey => $this->fromAccountKey($context->companyId, (string) $line->account_key, $context),
            PostingAccountResolver::ContextAccount => $this->fromContextField($context, (string) $line->context_account_field),
        };
    }

    protected function fixedAccount(PostingTemplateLine $line): int
    {
        if (! $line->gl_account_id) {
            throw ValidationException::withMessages([
                'account' => __('Template line :line requires a fixed GL account.', ['line' => $line->line_number]),
            ]);
        }

        return (int) $line->gl_account_id;
    }

    protected function fromAccountKey(int $companyId, string $accountKey, PostingContext $context): int
    {
        if ($override = $context->accountId($accountKey)) {
            return $override;
        }

        $mapping = PostingAccountMapping::query()
            ->where('company_id', $companyId)
            ->where('account_key', $accountKey)
            ->first();

        if ($mapping) {
            return (int) $mapping->gl_account_id;
        }

        $config = config("posting_account_keys.{$accountKey}");

        if (! $config || empty($config['default_code'])) {
            throw ValidationException::withMessages([
                'account_key' => __('Unknown posting account key :key.', ['key' => $accountKey]),
            ]);
        }

        $account = GlAccount::query()
            ->where('company_id', $companyId)
            ->where('code', $config['default_code'])
            ->first();

        if (! $account) {
            throw ValidationException::withMessages([
                'account_key' => __('GL account :code for key :key is not configured.', [
                    'code' => $config['default_code'],
                    'key' => $accountKey,
                ]),
            ]);
        }

        return (int) $account->id;
    }

    protected function fromContextField(PostingContext $context, string $field): int
    {
        $accountId = $context->accountId($field);

        if (! $accountId) {
            throw ValidationException::withMessages([
                'account' => __('Context account :field is required for posting.', ['field' => $field]),
            ]);
        }

        return $accountId;
    }
}
