<?php

namespace App\Support\Platform;

use App\Models\Company;
use App\Models\Department;
use App\Models\Platform\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SettingsControlCenterPresenter
{
    public function __construct(
        protected SettingsRegistry $registry,
        protected SystemSettingsManager $manager,
    ) {}

    /**
     * @return array{
     *     summary: array<string, mixed>,
     *     filters: list<array<string, mixed>>,
     *     cards: list<array<string, mixed>>,
     *     domains: list<array<string, mixed>>
     * }
     */
    public function hub(int $companyId, ?int $branchId): array
    {
        $scopeQuery = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $domains = [];
        $allCards = [];

        foreach (config('settings_control_center.domains', []) as $slug => $domain) {
            $cards = [];

            foreach ($domain['cards'] as $definition) {
                $cards[] = $this->buildCard(
                    $definition,
                    $scopeQuery,
                    $companyId,
                    $branchId,
                    $slug,
                    __($domain['label']),
                );
            }

            $domains[] = [
                'slug' => $slug,
                'label' => __($domain['label']),
                'description' => __($domain['description'] ?? ''),
                'cards' => $cards,
            ];

            array_push($allCards, ...$cards);
        }

        $filters = [
            [
                'slug' => 'all',
                'label' => __('All'),
                'count' => count($allCards),
            ],
            ...collect($domains)->map(fn (array $domain) => [
                'slug' => $domain['slug'],
                'label' => $domain['label'],
                'count' => count($domain['cards']),
            ])->all(),
        ];

        return [
            'summary' => $this->buildSummary($allCards, $companyId, $branchId),
            'filters' => $filters,
            'cards' => $allCards,
            'domains' => $domains,
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, int>  $scopeQuery
     * @return array<string, mixed>
     */
    protected function buildCard(
        array $definition,
        array $scopeQuery,
        int $companyId,
        ?int $branchId,
        string $domainSlug,
        string $domainLabel,
    ): array {
        $comingSoon = (bool) ($definition['coming_soon'] ?? false);
        $href = $comingSoon ? null : $this->resolveHref($definition['link'] ?? null, $scopeQuery);
        $status = $comingSoon
            ? $this->pendingSetupStatus()
            : $this->resolveStatus($definition['status'] ?? null, $companyId, $branchId);

        $searchText = Str::lower(implode(' ', array_filter([
            $definition['title'],
            $definition['description'] ?? '',
            $domainLabel,
            implode(' ', $definition['keywords'] ?? []),
        ])));

        return [
            'id' => $definition['id'],
            'domain_slug' => $domainSlug,
            'domain_label' => $domainLabel,
            'title' => __($definition['title']),
            'description' => __($definition['description'] ?? ''),
            'icon' => $definition['icon'] ?? 'cog',
            'href' => $href,
            'comingSoon' => $comingSoon || empty($href),
            'search_text' => $searchText,
            ...$status,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $link
     * @param  array<string, int>  $scopeQuery
     */
    protected function resolveHref(?array $link, array $scopeQuery): ?string
    {
        if ($link === null) {
            return null;
        }

        return match ($link['type']) {
            'settings' => route('admin.settings.show', ['section' => $link['section']] + $scopeQuery),
            'route' => route($link['name'], $link['params'] ?? $scopeQuery),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>|null  $statusDefinition
     * @return array<string, mixed>
     */
    protected function resolveStatus(?array $statusDefinition, int $companyId, ?int $branchId): array
    {
        if ($statusDefinition === null) {
            return $this->buildStatus(1, 1);
        }

        return match ($statusDefinition['type']) {
            'registry' => $this->statusForRegistrySection($statusDefinition['section'], $companyId, $branchId),
            'forms' => $this->buildStatus(count(config('form_registry.forms', [])), count(config('form_registry.forms', []))),
            'approvals' => $this->buildStatus(count(config('approval_registry.rule_types', [])), count(config('approval_registry.rule_types', []))),
            'numbering' => $this->buildStatus(count(config('numbering_registry.document_types', [])), count(config('numbering_registry.document_types', []))),
            'users' => $this->buildStatus(
                User::query()->where('company_id', $companyId)->count() > 0 ? 1 : 0,
                1,
            ),
            'departments' => $this->buildStatus(
                Department::query()->where('company_id', $companyId)->count() > 0 ? 1 : 0,
                1,
            ),
            'roles' => $this->buildStatus(
                Role::query()->where('guard_name', 'web')->count() > 0 ? 1 : 0,
                1,
            ),
            'vendors' => $this->buildStatus(
                \App\Models\Procurement\Vendor::query()->where('company_id', $companyId)->count() > 0 ? 1 : 0,
                1,
            ),
            'branding' => $this->buildStatus(
                $this->companyHasBrandingAssets($companyId) ? 1 : 0,
                1,
            ),
            'company_email' => $this->buildStatus(
                app(\App\Services\EmailIdentity\CpanelApiClient::class)->isConfigured() ? 1 : 0,
                1,
            ),
            default => $this->buildStatus(1, 1),
        };
    }

    protected function companyHasBrandingAssets(int $companyId): bool
    {
        $company = Company::query()->find($companyId);

        if ($company === null) {
            return false;
        }

        return filled($company->logo) || filled($company->favicon_path);
    }

    /**
     * @return array<string, mixed>
     */
    protected function statusForRegistrySection(string $sectionSlug, int $companyId, ?int $branchId): array
    {
        $rows = $this->manager->rowsForSection($sectionSlug, $companyId, $branchId);
        $total = $rows->count();

        if ($total === 0) {
            return $this->buildStatus(0, 0);
        }

        $completed = $rows->filter(fn (array $row) => $this->rowIsConfigured($row))->count();

        return $this->buildStatus($completed, $total);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function rowIsConfigured(array $row): bool
    {
        $type = $row['type'] ?? 'string';
        $value = $row['effective_value'];

        if ($type === 'boolean') {
            return $value !== null;
        }

        if ($value === null || $value === '') {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildStatus(int $completed, int $total): array
    {
        if ($total === 0) {
            return [
                'status' => 'configured',
                'statusLabel' => __('Configured'),
                'statusVariant' => 'success',
            ];
        }

        if ($completed >= $total) {
            return [
                'status' => 'configured',
                'statusLabel' => __('Configured'),
                'statusVariant' => 'success',
            ];
        }

        if ($completed === 0) {
            return [
                'status' => 'incomplete',
                'statusLabel' => __('Incomplete'),
                'statusVariant' => 'danger',
            ];
        }

        return [
            'status' => 'needs_attention',
            'statusLabel' => __('Needs Attention'),
            'statusVariant' => 'warning',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function pendingSetupStatus(): array
    {
        return [
            'status' => 'pending_setup',
            'statusLabel' => __('Pending Setup'),
            'statusVariant' => 'neutral',
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     * @return array<string, mixed>
     */
    protected function buildSummary(array $cards, int $companyId, ?int $branchId): array
    {
        $actionable = collect($cards)->reject(fn (array $card) => $card['status'] === 'pending_setup');

        return [
            'total_areas' => count($cards),
            'configured' => $actionable->where('status', 'configured')->count(),
            'needs_attention' => $actionable->where('status', 'needs_attention')->count(),
            'incomplete' => $actionable->where('status', 'incomplete')->count(),
            'pending_setup' => collect($cards)->where('status', 'pending_setup')->count(),
            'last_reviewed' => $this->lastReviewedLabel($companyId, $branchId),
        ];
    }

    protected function lastReviewedLabel(int $companyId, ?int $branchId): string
    {
        $timestamp = SystemSetting::query()
            ->where('company_id', $companyId)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($scoped) use ($branchId) {
                    $scoped->whereNull('branch_id')->orWhere('branch_id', $branchId);
                });
            })
            ->max('updated_at');

        if ($timestamp === null) {
            return __('Not yet reviewed');
        }

        $reviewedAt = Carbon::parse($timestamp);

        if ($reviewedAt->isToday()) {
            return __('Today');
        }

        if ($reviewedAt->isYesterday()) {
            return __('Yesterday');
        }

        return $reviewedAt->translatedFormat('M j, Y');
    }
}
