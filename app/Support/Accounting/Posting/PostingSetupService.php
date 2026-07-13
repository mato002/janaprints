<?php

namespace App\Support\Accounting\Posting;

use App\Enums\PostingAccountResolver;
use App\Enums\PostingAmountSource;
use App\Enums\PostingEventCode;
use App\Enums\PostingLineSide;
use App\Enums\PostingModule;
use App\Models\Accounting\PostingRule;
use App\Models\Accounting\PostingTemplate;
use App\Models\Accounting\PostingTemplateLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PostingSetupService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $lines
     */
    public function createTemplate(int $companyId, array $data, array $lines): PostingTemplate
    {
        return DB::transaction(function () use ($companyId, $data, $lines) {
            $template = PostingTemplate::query()->create([
                'company_id' => $companyId,
                'code' => $data['code'],
                'name' => $data['name'],
                'module' => PostingModule::from($data['module']),
                'description' => $data['description'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'is_system' => false,
            ]);

            $this->syncLines($template, $lines);

            return $template->fresh('lines');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $lines
     */
    public function updateTemplate(PostingTemplate $template, array $data, array $lines): PostingTemplate
    {
        if ($template->is_system) {
            throw ValidationException::withMessages([
                'template' => __('System posting templates cannot be edited. Duplicate them instead.'),
            ]);
        }

        return DB::transaction(function () use ($template, $data, $lines) {
            $template->update([
                'name' => $data['name'],
                'module' => PostingModule::from($data['module']),
                'description' => $data['description'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? $template->is_active),
            ]);

            $template->lines()->delete();
            $this->syncLines($template, $lines);

            return $template->fresh('lines');
        });
    }

    public function toggleTemplate(PostingTemplate $template): PostingTemplate
    {
        $template->update(['is_active' => ! $template->is_active]);

        return $template->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRule(int $companyId, array $data): PostingRule
    {
        $event = PostingEventCode::from($data['event_code']);

        return PostingRule::query()->create([
            'company_id' => $companyId,
            'event_code' => $event->value,
            'module' => $event->module(),
            'posting_template_id' => (int) $data['posting_template_id'],
            'name' => $data['name'] ?: $event->label(),
            'description' => $data['description'] ?? null,
            'priority' => (int) ($data['priority'] ?? 100),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'auto_post' => (bool) ($data['auto_post'] ?? true),
            'is_system' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateRule(PostingRule $rule, array $data): PostingRule
    {
        if ($rule->is_system && array_key_exists('event_code', $data) && $data['event_code'] !== $rule->event_code) {
            throw ValidationException::withMessages([
                'event_code' => __('System posting rules cannot change event codes.'),
            ]);
        }

        $event = PostingEventCode::from($data['event_code'] ?? $rule->event_code);

        $rule->update([
            'event_code' => $event->value,
            'module' => $event->module(),
            'posting_template_id' => (int) ($data['posting_template_id'] ?? $rule->posting_template_id),
            'name' => $data['name'] ?? $rule->name,
            'description' => $data['description'] ?? $rule->description,
            'priority' => (int) ($data['priority'] ?? $rule->priority),
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $rule->is_active,
            'auto_post' => array_key_exists('auto_post', $data) ? (bool) $data['auto_post'] : $rule->auto_post,
        ]);

        return $rule->fresh('template');
    }

    public function toggleRule(PostingRule $rule): PostingRule
    {
        $rule->update(['is_active' => ! $rule->is_active]);

        return $rule->fresh();
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    protected function syncLines(PostingTemplate $template, array $lines): void
    {
        if ($lines === []) {
            throw ValidationException::withMessages([
                'lines' => __('Add at least one template line.'),
            ]);
        }

        foreach ($lines as $index => $line) {
            $resolver = PostingAccountResolver::from($line['account_resolver']);
            $amountSource = PostingAmountSource::from($line['amount_source']);

            PostingTemplateLine::query()->create([
                'posting_template_id' => $template->id,
                'line_number' => $index + 1,
                'entry_side' => PostingLineSide::from($line['entry_side']),
                'account_resolver' => $resolver,
                'gl_account_id' => $resolver === PostingAccountResolver::FixedAccount
                    ? ($line['gl_account_id'] ?? null)
                    : null,
                'account_key' => $resolver === PostingAccountResolver::AccountKey
                    ? ($line['account_key'] ?? null)
                    : null,
                'context_account_field' => $resolver === PostingAccountResolver::ContextAccount
                    ? ($line['context_account_field'] ?? null)
                    : null,
                'amount_source' => $amountSource,
                'amount_field' => $amountSource === PostingAmountSource::ContextField
                    ? ($line['amount_field'] ?? null)
                    : null,
                'line_description' => $line['line_description'] ?? ':description',
            ]);
        }
    }
}
