<?php

namespace App\Support\Accounting;

use App\Enums\PostingAmountSource;
use App\Enums\PostingLineSide;
use App\Models\Accounting\PostingTemplate;
use App\Support\Accounting\Dto\PostingContext;
use Illuminate\Validation\ValidationException;

class PostingTemplateBuilderService
{
    public function __construct(
        protected PostingAccountResolverService $accountResolver,
    ) {}

    /**
     * @return list<array{gl_account_id: int, description: ?string, debit: float, credit: float}>
     */
    public function buildJournalLines(PostingTemplate $template, PostingContext $context): array
    {
        $template->loadMissing('lines');

        if ($template->lines->isEmpty()) {
            throw ValidationException::withMessages([
                'template' => __('Posting template :code has no lines.', ['code' => $template->code]),
            ]);
        }

        $journalLines = [];

        foreach ($template->lines as $line) {
            $amount = $this->resolveAmount($line->amount_source, $line->amount_field, $context);

            if ($amount <= 0) {
                continue;
            }

            $debit = $line->entry_side === PostingLineSide::Debit ? $amount : 0.0;
            $credit = $line->entry_side === PostingLineSide::Credit ? $amount : 0.0;

            $journalLines[] = [
                'gl_account_id' => $this->accountResolver->resolve($line, $context),
                'description' => $this->formatDescription($line->line_description, $context),
                'debit' => round($debit, 2),
                'credit' => round($credit, 2),
            ];
        }

        if ($journalLines === []) {
            throw ValidationException::withMessages([
                'amount' => __('Posting produced no journal lines. Check amounts in context.'),
            ]);
        }

        return $journalLines;
    }

    protected function resolveAmount(PostingAmountSource $source, ?string $field, PostingContext $context): float
    {
        return match ($source) {
            PostingAmountSource::Amount => $context->amount('amount'),
            PostingAmountSource::Subtotal => $context->amount('subtotal'),
            PostingAmountSource::TaxAmount => $context->amount('tax_amount'),
            PostingAmountSource::TotalAmount => $context->amount('total_amount'),
            PostingAmountSource::AllocatedAmount => $context->amount('allocated_amount'),
            PostingAmountSource::UnallocatedAmount => $context->amount('unallocated_amount'),
            PostingAmountSource::ContextField => $context->amount((string) $field),
        };
    }

    protected function formatDescription(?string $template, PostingContext $context): ?string
    {
        if ($template === null || $template === '') {
            return $context->description;
        }

        $replacements = [
            ':reference' => $context->reference ?? '',
            ':description' => $context->description ?? '',
            ':event' => $context->event->value,
            ':source_type' => $context->sourceType,
            ':source_id' => (string) $context->sourceId,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
