<?php

namespace App\Support\Platform;

use App\Enums\DocumentType;
use App\Models\Platform\NumberingSequence;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class NumberingSequenceManager
{
    public function __construct(
        protected NumberGenerator $generator,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function rows(int $companyId, ?int $branchId): Collection
    {
        $this->ensureSequences($companyId, $branchId);

        $types = config('numbering_registry.document_types', []);

        return collect($types)->map(function (array $meta, string $slug) use ($companyId, $branchId) {
            $documentType = DocumentType::from($slug);
            $sequence = $this->findSequence($companyId, $branchId, $slug);
            $parsed = $this->generator->parseTemplate($sequence);

            return [
                'document_type' => $slug,
                'label' => $meta['label'],
                'type_code' => $meta['type_code'],
                'prefix' => $parsed['prefix'] ?? $this->defaultPrefix($companyId),
                'include_branch' => $parsed['include_branch'],
                'include_year' => $parsed['include_year'],
                'include_month' => $parsed['include_month'],
                'padding' => $sequence->padding,
                'next_number' => $sequence->next_number,
                'active' => $this->generator->isActive($documentType, $companyId, $branchId),
                'preview' => $this->generator->preview($sequence, $documentType, $companyId, $branchId),
                'sequence_id' => $sequence->id,
            ];
        })->values();
    }

    /**
     * @param  array<string, array<string, mixed>>  $payload
     */
    public function save(int $companyId, ?int $branchId, array $payload): void
    {
        foreach (config('numbering_registry.document_types', []) as $slug => $meta) {
            if (! isset($payload[$slug])) {
                continue;
            }

            $documentType = DocumentType::from($slug);
            $input = $payload[$slug];
            $sequence = $this->findSequence($companyId, $branchId, $slug);

            $includeBranch = filter_var($input['include_branch'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $includeYear = filter_var($input['include_year'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $includeMonth = filter_var($input['include_month'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $prefix = filled($input['prefix'] ?? null) ? (string) $input['prefix'] : null;

            $sequence->update([
                'format_template' => $this->generator->buildTemplate($prefix, $includeBranch, $includeYear, $includeMonth),
                'padding' => (int) ($input['padding'] ?? $sequence->padding),
                'next_number' => max(1, (int) ($input['next_number'] ?? $sequence->next_number)),
                'include_year' => $includeYear,
                'include_branch_code' => $includeBranch,
            ]);

            $this->generator->setActive(
                $documentType,
                filter_var($input['active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                $companyId,
                $branchId,
            );
        }
    }

    public function ensureSequences(int $companyId, ?int $branchId): void
    {
        foreach (array_keys(config('numbering_registry.document_types', [])) as $slug) {
            NumberingSequence::query()->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'document_type' => $slug,
                ],
                [
                    'format_template' => $this->generator->buildTemplate(null, true, true, false),
                    'next_number' => 1,
                    'padding' => config('platform.numbering.default_padding', 5),
                    'include_year' => true,
                    'include_branch_code' => true,
                ],
            );
        }
    }

    protected function findSequence(int $companyId, ?int $branchId, string $documentType): NumberingSequence
    {
        $sequence = NumberingSequence::query()
            ->where('company_id', $companyId)
            ->where('document_type', $documentType)
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->first();

        if (! $sequence) {
            throw new InvalidArgumentException("Numbering sequence missing for [{$documentType}].");
        }

        return $sequence;
    }

    protected function defaultPrefix(int $companyId): string
    {
        return \App\Models\Company::query()->whereKey($companyId)->value('code') ?? 'CO';
    }
}
