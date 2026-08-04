<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\Platform\EntityCodeGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ResolvesEntityCode
{
    /**
     * @return list<string|object>
     */
    protected function nullableCodeRules(int $maxLength = 50): array
    {
        return ['nullable', 'string', 'max:'.$maxLength];
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @param  callable(Builder): void  $scope
     */
    protected function resolveEntityCode(
        Request $request,
        string $sourceField,
        string $modelClass,
        callable $scope,
        ?int $ignoreId = null,
        int $maxLength = 50,
    ): string {
        return app(EntityCodeGenerator::class)->unique(
            $modelClass,
            (string) $request->input($sourceField, ''),
            $scope,
            $maxLength,
            $ignoreId,
            $request->input('code'),
        );
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     */
    protected function resolveCompanyScopedCode(
        Request $request,
        string $sourceField,
        string $modelClass,
        int $companyId,
        ?int $ignoreId = null,
        int $maxLength = 50,
    ): string {
        return $this->resolveEntityCode(
            $request,
            $sourceField,
            $modelClass,
            fn (Builder $query) => $query->where('company_id', $companyId),
            $ignoreId,
            $maxLength,
        );
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @param  array<string, mixed>  $extraWhere
     */
    protected function resolveBranchScopedCode(
        Request $request,
        string $sourceField,
        string $modelClass,
        int $companyId,
        int $branchId,
        ?int $ignoreId = null,
        int $maxLength = 50,
        array $extraWhere = [],
    ): string {
        return $this->resolveEntityCode(
            $request,
            $sourceField,
            $modelClass,
            function (Builder $query) use ($companyId, $branchId, $extraWhere): void {
                $query->where('company_id', $companyId)->where('branch_id', $branchId);

                foreach ($extraWhere as $column => $value) {
                    $query->where($column, $value);
                }
            },
            $ignoreId,
            $maxLength,
        );
    }

    /**
     * @param  array<string, array<int, mixed>>  $rules
     * @return array<string, array<int, mixed>>
     */
    protected function relaxCodeRulesForCreate(array $rules, int $maxLength = 50): array
    {
        if (! isset($rules['code'])) {
            return $rules;
        }

        $codeRules = array_values(array_filter(
            (array) $rules['code'],
            fn ($rule) => $rule !== 'required' && ! (is_string($rule) && str_starts_with($rule, 'required:')),
        ));

        if (! in_array('nullable', $codeRules, true)) {
            array_unshift($codeRules, 'nullable');
        }

        if (! in_array('string', $codeRules, true)) {
            $codeRules[] = 'string';
        }

        $hasMaxRule = false;
        foreach ($codeRules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'max:')) {
                $hasMaxRule = true;
                break;
            }
        }

        if (! $hasMaxRule) {
            $codeRules[] = 'max:'.$maxLength;
        }

        $rules['code'] = $codeRules;

        return $rules;
    }
}
