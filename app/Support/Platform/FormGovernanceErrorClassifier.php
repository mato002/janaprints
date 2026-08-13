<?php

namespace App\Support\Platform;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class FormGovernanceErrorClassifier
{
    /**
     * @return array{
     *     category: string,
     *     category_label: string,
     *     message: string,
     *     detail: ?string
     * }
     */
    public function present(Throwable $exception, bool $debug = false): array
    {
        $category = $this->categoryFor($exception);

        return [
            'category' => $category,
            'category_label' => $this->categoryLabel($category),
            'message' => $this->messageFor($exception, $category),
            'detail' => $debug ? $exception->getMessage() : null,
        ];
    }

    protected function categoryFor(Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            return 'validation';
        }

        if ($exception instanceof AuthorizationException) {
            return 'authorization';
        }

        if ($exception instanceof QueryException) {
            return 'database';
        }

        if ($exception instanceof HttpException) {
            return match (true) {
                $exception->getStatusCode() === 403 => 'authorization',
                $exception->getStatusCode() === 404 => 'not_found',
                $exception->getStatusCode() === 422 => 'validation',
                default => 'system',
            };
        }

        return 'system';
    }

    protected function categoryLabel(string $category): string
    {
        return match ($category) {
            'validation' => __('Validation Errors'),
            'authorization' => __('Access Denied'),
            'database' => __('Database Errors'),
            'not_found' => __('Not Found'),
            default => __('System Errors'),
        };
    }

    protected function messageFor(Throwable $exception, string $category): string
    {
        if ($exception instanceof ValidationException) {
            $messages = collect($exception->errors())->flatten()->filter()->unique()->values();

            if ($messages->count() === 1) {
                return (string) $messages->first();
            }

            if ($messages->count() > 1) {
                return $messages->implode("\n");
            }

            return __('Please fix the errors below and try again.');
        }

        if ($category === 'authorization') {
            return __('You do not have permission to complete this action.');
        }

        if ($category === 'database') {
            return __('A database error prevented this form from saving.');
        }

        if ($category === 'not_found') {
            return __('The requested record could not be found.');
        }

        $raw = trim($exception->getMessage());

        if ($raw !== '') {
            if ($exception instanceof HttpException && $exception->getStatusCode() < 500) {
                return $raw;
            }

            if ($exception instanceof \InvalidArgumentException || $exception instanceof \RuntimeException) {
                return $raw;
            }

            // Prefer a concise exception message over a generic system fallback so operators can troubleshoot.
            if (
                strlen($raw) <= 400
                && ! str_contains($raw, 'SQLSTATE')
                && ! str_contains($raw, 'Stack trace')
                && ! str_contains($raw, 'vendor/')
            ) {
                return $raw;
            }
        }

        return __('Something went wrong while processing this form. Please try again.');
    }
}
