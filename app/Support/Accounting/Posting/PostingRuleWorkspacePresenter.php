<?php

namespace App\Support\Accounting\Posting;

use App\Enums\JournalStatus;
use App\Enums\PostingAccountResolver;
use App\Enums\PostingEventCode;
use App\Enums\PostingModule;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Models\Accounting\PostingAccountMapping;
use App\Models\Accounting\PostingRule;
use App\Models\Accounting\PostingTemplateLine;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PostingRuleWorkspacePresenter
{
    public function __construct(
        protected PostingRuleValidationService $validation,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildIndex(Request $request): array
    {
        $allRules = PostingRule::query()
            ->forTenant()
            ->with(['template.lines.glAccount.accountType'])
            ->orderBy('module')
            ->orderBy('event_code')
            ->get();

        $validations = $this->validation->validateMany($allRules);
        $filtered = $this->applyFilters($allRules, $validations, $request);

        return [
            'summary' => $this->buildSummary($allRules, $validations),
            'moduleSummary' => $this->buildModuleSummary($allRules, $validations),
            'rules' => $filtered,
            'validations' => $validations,
            'filters' => $this->activeFilters($request),
            'filterOptions' => $this->filterOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildRuleDetail(PostingRule $rule, bool $includeAudit = true): array
    {
        $rule->load(['template.lines.glAccount.accountType']);
        $validation = $this->validation->validate($rule);
        $template = $rule->template;
        $eventLabel = $this->eventLabel($rule->event_code);

        $detail = [
            'id' => $rule->id,
            'name' => $rule->name,
            'description' => $rule->description,
            'event_code' => $rule->event_code,
            'event_label' => $eventLabel,
            'module' => $rule->module->value,
            'module_label' => $rule->module->label(),
            'template' => $template ? [
                'id' => $template->id,
                'code' => $template->code,
                'name' => $template->name,
                'url' => route('admin.accounting.posting.templates.show', $template),
            ] : null,
            'auto_post' => $rule->auto_post,
            'auto_post_label' => $rule->auto_post ? __('Yes') : __('No'),
            'is_active' => $rule->is_active,
            'status_label' => $rule->is_active ? __('Active') : __('Inactive'),
            'is_system' => $rule->is_system,
            'rule_type_label' => $rule->is_system ? __('System') : __('Custom'),
            'priority' => $rule->priority,
            'created_at' => $rule->created_at?->toIso8601String(),
            'updated_at' => $rule->updated_at?->toIso8601String(),
            'created_by' => $this->resolveActor($rule, 'created'),
            'updated_by' => $this->resolveActor($rule, 'updated'),
            'validation' => [
                'status' => $validation->status,
                'label' => $validation->label(),
                'badge_variant' => $validation->badgeVariant(),
                'issues' => $validation->issues,
                'validated_at' => now()->toIso8601String(),
            ],
            'account_mappings' => $template ? $this->buildAccountMappings($rule, $template->lines) : [],
            'journal_preview' => $template ? $this->buildJournalPreview($rule, $template->lines) : [],
            'workflow' => $this->buildWorkflow($rule, $eventLabel, $template),
        ];

        if ($includeAudit) {
            $detail['audit'] = $this->buildAuditStats($rule);
        }

        return $detail;
    }

    /**
     * @param  Collection<int, PostingRule>  $rules
     * @param  array<int, PostingRuleValidationResult>  $validations
     * @return array<string, int>
     */
    protected function buildSummary(Collection $rules, array $validations): array
    {
        $validationErrors = collect($validations)->filter(fn ($result) => $result->isBroken())->count();

        return [
            'total' => $rules->count(),
            'active' => $rules->where('is_active', true)->count(),
            'auto_post' => $rules->where('auto_post', true)->count(),
            'manual' => $rules->where('auto_post', false)->count(),
            'disabled' => $rules->where('is_active', false)->count(),
            'validation_errors' => $validationErrors,
        ];
    }

    /**
     * @param  Collection<int, PostingRule>  $rules
     * @param  array<int, PostingRuleValidationResult>  $validations
     * @return list<array<string, mixed>>
     */
    protected function buildModuleSummary(Collection $rules, array $validations): array
    {
        $modules = collect(PostingModule::cases());

        return $modules->map(function (PostingModule $module) use ($rules, $validations) {
            $moduleRules = $rules->where(fn (PostingRule $rule) => $rule->module === $module);
            $errors = $moduleRules->filter(fn (PostingRule $rule) => ($validations[$rule->id] ?? null)?->isBroken())->count();

            return [
                'module' => $module->value,
                'label' => $module->label(),
                'active' => $moduleRules->where('is_active', true)->count(),
                'disabled' => $moduleRules->where('is_active', false)->count(),
                'validation_errors' => $errors,
                'total' => $moduleRules->count(),
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, PostingRule>  $rules
     * @param  array<int, PostingRuleValidationResult>  $validations
     * @return Collection<int, PostingRule>
     */
    protected function applyFilters(Collection $rules, array $validations, Request $request): Collection
    {
        $query = $rules;

        if ($module = $request->string('module')->toString()) {
            $query = $query->filter(fn (PostingRule $rule) => $rule->module->value === $module);
        }

        if ($status = $request->string('status')->toString()) {
            $query = $query->filter(fn (PostingRule $rule) => $status === 'active' ? $rule->is_active : ! $rule->is_active);
        }

        if ($request->filled('auto_post')) {
            $autoPost = $request->string('auto_post')->toString() === '1';
            $query = $query->filter(fn (PostingRule $rule) => $rule->auto_post === $autoPost);
        }

        if ($validationStatus = $request->string('validation_status')->toString()) {
            $query = $query->filter(fn (PostingRule $rule) => ($validations[$rule->id]->status ?? '') === $validationStatus);
        }

        if ($ruleType = $request->string('rule_type')->toString()) {
            $isSystem = $ruleType === 'system';
            $query = $query->filter(fn (PostingRule $rule) => $rule->is_system === $isSystem);
        }

        if ($request->filled('created_from')) {
            $from = $request->string('created_from')->toString();
            $query = $query->filter(fn (PostingRule $rule) => $rule->created_at?->toDateString() >= $from);
        }

        if ($request->filled('created_to')) {
            $to = $request->string('created_to')->toString();
            $query = $query->filter(fn (PostingRule $rule) => $rule->created_at?->toDateString() <= $to);
        }

        if ($request->filled('updated_from')) {
            $from = $request->string('updated_from')->toString();
            $query = $query->filter(fn (PostingRule $rule) => $rule->updated_at?->toDateString() >= $from);
        }

        if ($request->filled('updated_to')) {
            $to = $request->string('updated_to')->toString();
            $query = $query->filter(fn (PostingRule $rule) => $rule->updated_at?->toDateString() <= $to);
        }

        if ($search = trim($request->string('q')->toString())) {
            $needle = strtolower($search);
            $query = $query->filter(function (PostingRule $rule) use ($needle) {
                $haystack = strtolower(implode(' ', [
                    $rule->event_code,
                    $rule->name,
                    $rule->module->value,
                    $rule->template?->code ?? '',
                ]));

                return str_contains($haystack, $needle);
            });
        }

        return $query->values();
    }

    /**
     * @return array<string, mixed>
     */
    protected function activeFilters(Request $request): array
    {
        return array_filter([
            'module' => $request->string('module')->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'auto_post' => $request->filled('auto_post') ? $request->string('auto_post')->toString() : null,
            'validation_status' => $request->string('validation_status')->toString() ?: null,
            'rule_type' => $request->string('rule_type')->toString() ?: null,
            'created_from' => $request->string('created_from')->toString() ?: null,
            'created_to' => $request->string('created_to')->toString() ?: null,
            'updated_from' => $request->string('updated_from')->toString() ?: null,
            'updated_to' => $request->string('updated_to')->toString() ?: null,
            'q' => trim($request->string('q')->toString()) ?: null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @return array<string, mixed>
     */
    protected function filterOptions(): array
    {
        return [
            'modules' => collect(PostingModule::cases())->map(fn (PostingModule $module) => [
                'value' => $module->value,
                'label' => $module->label(),
            ])->values()->all(),
            'statuses' => [
                ['value' => 'active', 'label' => __('Active')],
                ['value' => 'inactive', 'label' => __('Inactive')],
            ],
            'auto_post' => [
                ['value' => '1', 'label' => __('Auto post')],
                ['value' => '0', 'label' => __('Manual review')],
            ],
            'validation_statuses' => [
                ['value' => PostingRuleValidationResult::STATUS_VALID, 'label' => __('Valid')],
                ['value' => PostingRuleValidationResult::STATUS_WARNING, 'label' => __('Warning')],
                ['value' => PostingRuleValidationResult::STATUS_BROKEN, 'label' => __('Broken')],
            ],
            'rule_types' => [
                ['value' => 'system', 'label' => __('System')],
                ['value' => 'custom', 'label' => __('Custom')],
            ],
        ];
    }

    /**
     * @param  Collection<int, PostingTemplateLine>  $lines
     * @return list<array<string, mixed>>
     */
    protected function buildAccountMappings(PostingRule $rule, Collection $lines): array
    {
        $mappings = PostingAccountMapping::query()
            ->where('company_id', $rule->company_id)
            ->get();

        return $lines->map(function (PostingTemplateLine $line) use ($rule, $mappings) {
            $account = $this->resolveLineAccount($line, $rule->company_id, $mappings);

            return [
                'line_number' => $line->line_number,
                'side' => $line->entry_side->value,
                'side_label' => ucfirst($line->entry_side->value),
                'resolver' => $line->account_resolver->label(),
                'account_key' => $line->account_key,
                'account' => $account,
                'resolution_note' => $account ? null : $this->resolutionNote($line),
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, PostingTemplateLine>  $lines
     * @return array<string, mixed>
     */
    protected function buildJournalPreview(PostingRule $rule, Collection $lines): array
    {
        $mappings = PostingAccountMapping::query()
            ->where('company_id', $rule->company_id)
            ->get();

        $debitLines = [];
        $creditLines = [];

        foreach ($lines as $line) {
            $account = $this->resolveLineAccount($line, $rule->company_id, $mappings);
            $entry = [
                'line_number' => $line->line_number,
                'side' => $line->entry_side->value,
                'account_code' => $account['code'] ?? '—',
                'account_name' => $account['name'] ?? $this->resolutionNote($line),
                'amount_source' => $line->amount_source->label(),
                'description' => $line->line_description,
            ];

            if ($line->entry_side->value === 'debit') {
                $debitLines[] = $entry;
            } else {
                $creditLines[] = $entry;
            }
        }

        return [
            'event' => $rule->event_code,
            'event_label' => $this->eventLabel($rule->event_code),
            'produces' => __('Journal entry'),
            'debit_lines' => $debitLines,
            'credit_lines' => $creditLines,
            'structure' => array_merge(
                array_map(fn ($line) => __('Debit').': '.$line['account_name'], $debitLines),
                array_map(fn ($line) => __('Credit').': '.$line['account_name'], $creditLines),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildWorkflow(PostingRule $rule, string $eventLabel, $template): array
    {
        $steps = [
            [
                'key' => 'event',
                'label' => __('Business event'),
                'value' => $eventLabel,
                'code' => $rule->event_code,
            ],
            [
                'key' => 'rule',
                'label' => __('Posting rule'),
                'value' => $rule->name,
            ],
            [
                'key' => 'template',
                'label' => __('Posting template'),
                'value' => $template?->name ?? '—',
                'code' => $template?->code,
            ],
            [
                'key' => 'accounts',
                'label' => __('Debit / credit accounts'),
                'value' => $template
                    ? __(':debits debit · :credits credit lines', [
                        'debits' => $template->lines->filter(fn ($line) => $line->entry_side->value === 'debit')->count(),
                        'credits' => $template->lines->filter(fn ($line) => $line->entry_side->value === 'credit')->count(),
                    ])
                    : '—',
            ],
            [
                'key' => 'journal',
                'label' => __('Journal entry'),
                'value' => $rule->auto_post ? __('Auto-posted when event fires') : __('Draft journal for review'),
            ],
        ];

        return ['steps' => $steps];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildAuditStats(PostingRule $rule): array
    {
        $baseQuery = Journal::query()
            ->where('company_id', $rule->company_id)
            ->where('posting_rule_id', $rule->id);

        $totalJournals = (clone $baseQuery)->count();

        $postedQuery = (clone $baseQuery)->where('status', JournalStatus::Posted);

        $postedJournals = (clone $postedQuery)->count();
        $totalAmountPosted = (float) ((clone $postedQuery)->sum('total_debit'));
        $lastPosted = (clone $postedQuery)->max('posted_at');
        $lastCreated = (clone $baseQuery)->max('created_at');

        $lastPostedAt = $lastPosted ? \Illuminate\Support\Carbon::parse($lastPosted) : null;
        $lastCreatedAt = $lastCreated ? \Illuminate\Support\Carbon::parse($lastCreated) : null;
        $lastUsage = collect([$lastPostedAt, $lastCreatedAt])->filter()->max();

        return [
            'last_validation_at' => now()->toIso8601String(),
            'last_usage_at' => $lastUsage?->toIso8601String(),
            'last_journal_at' => $lastCreatedAt?->toIso8601String(),
            'last_posted_at' => $lastPostedAt?->toIso8601String(),
            'total_journals' => $totalJournals,
            'posted_journals' => $postedJournals,
            'total_amount_posted' => number_format($totalAmountPosted, 2),
        ];
    }

    protected function resolveActor(PostingRule $rule, string $action): ?array
    {
        $log = ActivityLog::query()
            ->where('model_type', PostingRule::class)
            ->where('model_id', $rule->id)
            ->where('action', $action)
            ->with('user')
            ->latest('created_at')
            ->first();

        if (! $log?->user) {
            return null;
        }

        return $this->formatUser($log->user, $log->created_at?->toIso8601String());
    }

    protected function formatUser(User $user, ?string $at = null): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'at' => $at,
        ];
    }

    protected function eventLabel(string $eventCode): string
    {
        $enum = PostingEventCode::tryFrom($eventCode);

        return $enum?->label() ?? $eventCode;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function formatAccount(GlAccount $account): array
    {
        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'type' => $account->accountType?->name,
            'normal_balance' => $account->normal_balance?->label(),
            'status' => $account->status->value,
            'status_label' => $account->status->label(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function resolveLineAccount(PostingTemplateLine $line, int $companyId, Collection $mappings): ?array
    {
        return match ($line->account_resolver) {
            PostingAccountResolver::FixedAccount => $line->glAccount ? $this->formatAccount($line->glAccount) : null,
            PostingAccountResolver::AccountKey => $this->resolveAccountKeyDisplay($line, $companyId, $mappings),
            PostingAccountResolver::ContextAccount => null,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function resolveAccountKeyDisplay(PostingTemplateLine $line, int $companyId, Collection $mappings): ?array
    {
        $accountKey = (string) $line->account_key;
        $mapping = $mappings->firstWhere('account_key', $accountKey);

        if ($mapping?->glAccount) {
            return $this->formatAccount($mapping->glAccount);
        }

        if ($mapping?->gl_account_id) {
            $account = GlAccount::query()->with('accountType')->find($mapping->gl_account_id);

            return $account ? $this->formatAccount($account) : null;
        }

        $config = config("posting_account_keys.{$accountKey}");

        if (! $config || empty($config['default_code'])) {
            return null;
        }

        $account = GlAccount::query()
            ->with('accountType')
            ->where('company_id', $companyId)
            ->where('code', $config['default_code'])
            ->first();

        return $account ? $this->formatAccount($account) : null;
    }

    protected function resolutionNote(PostingTemplateLine $line): string
    {
        return match ($line->account_resolver) {
            PostingAccountResolver::ContextAccount => __('Resolved at runtime from :field', ['field' => $line->context_account_field ?? 'context']),
            PostingAccountResolver::AccountKey => __('Key :key (see validation)', ['key' => $line->account_key ?? '—']),
            default => __('Account unresolved'),
        };
    }
}
