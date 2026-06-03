<?php

namespace App\Support\Platform;

use App\Enums\DocumentType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Platform\NumberingSequence;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NumberGenerator
{
    public function __construct(
        protected SystemSettingsService $settings,
    ) {}

    public function generate(
        DocumentType $documentType,
        int $companyId,
        ?int $branchId = null,
    ): string {
        return DB::transaction(function () use ($documentType, $companyId, $branchId) {
            $sequence = $this->lockedSequence($documentType, $companyId, $branchId);

            if (! $this->isActive($documentType, $companyId, $branchId)) {
                throw ValidationException::withMessages([
                    'number' => __('Numbering is disabled for :type.', ['type' => $documentType->value]),
                ]);
            }

            $this->applyYearRollover($sequence, $documentType, $companyId, $branchId);

            $number = (int) $sequence->next_number;
            $sequence->increment('next_number');

            return $this->format($sequence, $documentType, $number, $companyId, $branchId);
        });
    }

    public function preview(
        NumberingSequence $sequence,
        DocumentType $documentType,
        int $companyId,
        ?int $branchId,
        ?int $number = null,
    ): string {
        return $this->format(
            $sequence,
            $documentType,
            $number ?? (int) $sequence->next_number,
            $companyId,
            $branchId,
        );
    }

    public function buildTemplate(
        ?string $prefix,
        bool $includeBranch,
        bool $includeYear,
        bool $includeMonth,
    ): string {
        $parts = array_filter([
            $prefix ?: '{company}',
            $includeBranch ? '{branch}' : null,
            '{type}',
            $includeYear ? '{year}' : null,
            $includeMonth ? '{month}' : null,
            '{number}',
        ]);

        return implode('-', $parts);
    }

    /**
     * @return array{prefix: string|null, include_branch: bool, include_year: bool, include_month: bool}
     */
    public function parseTemplate(NumberingSequence $sequence): array
    {
        $template = $sequence->format_template ?: config('platform.numbering.default_template');
        $segments = explode('-', $template);
        $prefix = ($segments[0] ?? '') !== '{company}' ? ($segments[0] ?? null) : null;

        return [
            'prefix' => $prefix,
            'include_branch' => (bool) $sequence->include_branch_code,
            'include_year' => (bool) $sequence->include_year,
            'include_month' => str_contains($template, '{month}'),
        ];
    }

    protected function lockedSequence(
        DocumentType $documentType,
        int $companyId,
        ?int $branchId,
    ): NumberingSequence {
        $sequence = NumberingSequence::query()
            ->where('company_id', $companyId)
            ->where('document_type', $documentType->value)
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->lockForUpdate()
            ->first();

        if ($sequence) {
            return $sequence;
        }

        $created = NumberingSequence::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'document_type' => $documentType->value,
            'format_template' => $this->buildTemplate(null, true, true, false),
            'next_number' => 1,
            'padding' => config('platform.numbering.default_padding', 5),
            'include_year' => true,
            'include_branch_code' => true,
        ]);

        return NumberingSequence::query()->lockForUpdate()->findOrFail($created->id);
    }

    protected function applyYearRollover(
        NumberingSequence $sequence,
        DocumentType $documentType,
        int $companyId,
        ?int $branchId,
    ): void {
        if (! $sequence->include_year) {
            return;
        }

        $currentYear = (int) now()->year;
        $key = "numbering.last_year.{$documentType->value}";
        $lastYear = (int) $this->settings->get($key, $currentYear, $companyId, $branchId);

        if ($lastYear < $currentYear) {
            $sequence->update(['next_number' => 1]);
            $this->settings->set($key, $currentYear, $companyId, $branchId, 'integer');
        }
    }

    public function isActive(
        DocumentType $documentType,
        int $companyId,
        ?int $branchId,
    ): bool {
        return (bool) $this->settings->get(
            "numbering.active.{$documentType->value}",
            true,
            $companyId,
            $branchId,
        );
    }

    public function setActive(
        DocumentType $documentType,
        bool $active,
        int $companyId,
        ?int $branchId,
    ): void {
        $this->settings->set(
            "numbering.active.{$documentType->value}",
            $active,
            $companyId,
            $branchId,
            'boolean',
        );
    }

    protected function format(
        NumberingSequence $sequence,
        DocumentType $documentType,
        int $number,
        int $companyId,
        ?int $branchId,
    ): string {
        $company = Company::query()->find($companyId);
        $branch = $branchId ? Branch::query()->find($branchId) : null;
        $parsed = $this->parseTemplate($sequence);

        $replacements = [
            '{company}' => $company?->code ?? 'CO',
            '{branch}' => $branch?->code ?? 'HQ',
            '{type}' => $documentType->typeCode(),
            '{year}' => (string) now()->year,
            '{month}' => now()->format('m'),
            '{number}' => str_pad((string) $number, $sequence->padding, '0', STR_PAD_LEFT),
        ];

        if ($parsed['prefix']) {
            $replacements['{company}'] = $parsed['prefix'];
        }

        $template = $sequence->format_template ?: config('platform.numbering.default_template');

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
