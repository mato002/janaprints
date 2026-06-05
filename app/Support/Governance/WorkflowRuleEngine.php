<?php

namespace App\Support\Governance;

use Illuminate\Database\Eloquent\Model;

class WorkflowRuleEngine
{
    /**
     * @param  list<array{field: string, operator: string, value: mixed}>|null  $conditions
     */
    public function matchesConditions(?array $conditions, Model $subject): bool
    {
        if ($conditions === null || $conditions === []) {
            return true;
        }

        foreach ($conditions as $condition) {
            if (! $this->matchesCondition($condition, $subject)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array{field: string, operator: string, value: mixed}  $condition
     */
    protected function matchesCondition(array $condition, Model $subject): bool
    {
        $field = (string) ($condition['field'] ?? '');
        $operator = (string) ($condition['operator'] ?? 'equals');
        $expected = $condition['value'] ?? null;

        if ($field === '') {
            return true;
        }

        $actual = $this->resolveFieldValue($subject, $field);

        return match ($operator) {
            'equals' => $this->normalizeValue($actual) === $this->normalizeValue($expected),
            'not_equals' => $this->normalizeValue($actual) !== $this->normalizeValue($expected),
            'gte' => is_numeric($actual) && is_numeric($expected) && (float) $actual >= (float) $expected,
            'lte' => is_numeric($actual) && is_numeric($expected) && (float) $actual <= (float) $expected,
            'gt' => is_numeric($actual) && is_numeric($expected) && (float) $actual > (float) $expected,
            'lt' => is_numeric($actual) && is_numeric($expected) && (float) $actual < (float) $expected,
            'in' => in_array($this->normalizeValue($actual), $this->normalizeList($expected), true),
            default => true,
        };
    }

    protected function resolveFieldValue(Model $subject, string $field): mixed
    {
        if ($field === 'status' && method_exists($subject, 'getAttribute')) {
            $status = $subject->getAttribute('status');

            return is_object($status) && property_exists($status, 'value') ? $status->value : $status;
        }

        if ($field === 'due_date' && $subject->getAttribute('due_date') !== null) {
            return $subject->getAttribute('due_date') instanceof \DateTimeInterface
                ? $subject->getAttribute('due_date')->format('Y-m-d')
                : (string) $subject->getAttribute('due_date');
        }

        return $subject->getAttribute($field);
    }

    protected function normalizeValue(mixed $value): string
    {
        if (is_object($value) && enum_exists($value::class)) {
            return (string) $value->value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    /**
     * @return list<string>
     */
    protected function normalizeList(mixed $value): array
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalizeValue($item), $value);
        }

        return array_map(
            fn (string $item) => trim($item),
            explode(',', (string) $value),
        );
    }
}
