<?php

namespace App\Support\Platform;

use App\Models\Platform\FormSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FormsControlCenterPresenter
{
    public function __construct(
        protected FormGovernanceInspector $governanceInspector,
    ) {}

    /**
     * @param  Collection<int, array<string, mixed>>  $forms
     * @return array<string, mixed>
     */
    public function hub(int $companyId, ?int $branchId, Collection $forms): array
    {
        $scopeQuery = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $timestamps = $this->loadTimestamps($companyId, $branchId);
        $cards = [];
        $activeCards = [];
        $plannedCards = [];

        foreach ($forms as $form) {
            $card = $this->buildRegistryCard($form, $scopeQuery, $timestamps);
            $cards[] = $card;

            if ($card['comingSoon']) {
                $plannedCards[] = $card;
            } else {
                $activeCards[] = $card;
            }
        }

        foreach (config('form_control_center.planned_forms', []) as $planned) {
            $card = $this->buildPlannedCard($planned);
            $cards[] = $card;
            $plannedCards[] = $card;
        }

        $categories = $this->buildCategories($cards);
        $health = $this->buildHealth($activeCards);
        $recentlyModified = $this->buildRecentlyModified($activeCards);

        $compliance = $this->governanceInspector->complianceSummary();

        return [
            'summary' => $this->buildSummary($cards, $activeCards, $plannedCards, $compliance),
            'categories' => $categories,
            'cards' => $cards,
            'active_cards' => $activeCards,
            'planned_cards' => $plannedCards,
            'health' => $health,
            'compliance' => $compliance,
            'recently_modified' => $recentlyModified,
            'export_payload' => $this->buildExportPayload($companyId, $branchId, $activeCards),
            'scope_query' => $scopeQuery,
        ];
    }

    /**
     * @param  array<string, mixed>  $form
     * @param  array<string, int>  $scopeQuery
     * @return array<string, mixed>
     */
    protected function buildRegistryCard(array $form, array $scopeQuery, Collection $timestamps): array
    {
        $formKey = $form['form_key'];
        $meta = config("form_control_center.form_meta.{$formKey}", []);
        $categorySlug = $meta['category'] ?? 'administration';
        $categoryLabel = __(config("form_control_center.categories.{$categorySlug}.label", 'Administration'));
        $fields = collect($form['fields']);
        $metrics = $this->fieldMetrics($fields);
        $governance = $this->governanceForForm($formKey, $fields, (bool) $form['is_active']);
        $updatedAt = $this->resolveUpdatedAt($formKey, $timestamps);

        $fieldLabels = $fields->pluck('label')->filter()->all();
        $searchText = Str::lower(implode(' ', array_filter([
            $form['label'],
            $form['description'],
            $categoryLabel,
            $formKey,
            implode(' ', $fieldLabels),
            implode(' ', $meta['keywords'] ?? []),
        ])));

        return [
            'id' => $formKey,
            'form_key' => $formKey,
            'category_slug' => $categorySlug,
            'category_label' => $categoryLabel,
            'title' => $form['label'],
            'description' => $form['description'],
            'icon' => $meta['icon'] ?? 'clipboard-list',
            'href' => route('admin.settings.forms.index', ['form' => $formKey] + $scopeQuery),
            'comingSoon' => false,
            'is_active' => (bool) $form['is_active'],
            'statusLabel' => $form['is_active'] ? __('Active') : __('Inactive'),
            'statusVariant' => $form['is_active'] ? 'success' : 'warning',
            'metrics' => $metrics,
            'governance' => $governance,
            'has_governance_issues' => $governance['issue_count'] > 0,
            'updated_at' => $updatedAt?->toIso8601String(),
            'updated_label' => $this->formatUpdatedLabel($updatedAt),
            'search_text' => $searchText,
            'field_labels' => $fieldLabels,
        ];
    }

    /**
     * @param  array<string, mixed>  $planned
     * @return array<string, mixed>
     */
    protected function buildPlannedCard(array $planned): array
    {
        $categorySlug = $planned['category'];
        $categoryLabel = __(config("form_control_center.categories.{$categorySlug}.label", 'Administration'));

        $searchText = Str::lower(implode(' ', array_filter([
            $planned['label'],
            $planned['description'],
            $categoryLabel,
            implode(' ', $planned['keywords'] ?? []),
        ])));

        return [
            'id' => $planned['id'],
            'form_key' => null,
            'category_slug' => $categorySlug,
            'category_label' => $categoryLabel,
            'title' => __($planned['label']),
            'description' => __($planned['description']),
            'icon' => $planned['icon'] ?? 'clipboard-list',
            'href' => null,
            'comingSoon' => true,
            'is_active' => false,
            'statusLabel' => __('Planned'),
            'statusVariant' => 'neutral',
            'metrics' => [
                'field_count' => 0,
                'required_count' => 0,
                'read_only_count' => 0,
                'hidden_count' => 0,
            ],
            'governance' => [
                'missing_required' => 0,
                'hidden_required' => 0,
                'inactive' => 0,
                'issue_count' => 0,
            ],
            'has_governance_issues' => false,
            'updated_at' => null,
            'updated_label' => __('Not configured'),
            'search_text' => $searchText,
            'field_labels' => [],
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $fields
     * @return array<string, int>
     */
    protected function fieldMetrics(Collection $fields): array
    {
        return [
            'field_count' => $fields->count(),
            'required_count' => $fields->where('required', true)->count(),
            'read_only_count' => $fields->where('read_only', true)->count(),
            'hidden_count' => $fields->filter(fn (array $field) => ($field['hidden'] ?? false) || ! ($field['visible'] ?? true))->count(),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $fields
     * @return array<string, int>
     */
    protected function governanceForForm(string $formKey, Collection $fields, bool $isActive): array
    {
        $registryFields = config('form_registry.forms')[$formKey]['fields'] ?? [];
        $missingRequired = 0;
        $hiddenRequired = 0;

        foreach ($fields as $field) {
            $fieldKey = $field['field_key'];
            $registryRequired = (bool) ($registryFields[$fieldKey]['required'] ?? false);

            if ($registryRequired && ! ($field['required'] ?? false)) {
                $missingRequired++;
            }

            $isHidden = ($field['hidden'] ?? false) || ! ($field['visible'] ?? true);

            if (($field['required'] ?? false) && $isHidden) {
                $hiddenRequired++;
            }
        }

        $inactive = $isActive ? 0 : 1;

        return [
            'missing_required' => $missingRequired,
            'hidden_required' => $hiddenRequired,
            'inactive' => $inactive,
            'issue_count' => $missingRequired + $hiddenRequired + $inactive,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     * @return list<array<string, mixed>>
     */
    protected function buildCategories(array $cards): array
    {
        $counts = collect($cards)->countBy('category_slug');

        return collect(config('form_control_center.categories', []))
            ->map(function (array $category, string $slug) use ($counts) {
                return [
                    'slug' => $slug,
                    'label' => __($category['label']),
                    'description' => __($category['description'] ?? ''),
                    'icon' => $category['icon'] ?? 'clipboard-list',
                    'count' => (int) ($counts[$slug] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $activeCards
     * @return array<string, mixed>
     */
    protected function buildHealth(array $activeCards): array
    {
        $missingRequired = 0;
        $hiddenRequired = 0;
        $inactiveForms = 0;
        $governanceIssues = 0;

        foreach ($activeCards as $card) {
            $missingRequired += $card['governance']['missing_required'];
            $hiddenRequired += $card['governance']['hidden_required'];
            $inactiveForms += $card['governance']['inactive'];
            $governanceIssues += $card['governance']['issue_count'];
        }

        return [
            'missing_required' => $missingRequired,
            'hidden_required' => $hiddenRequired,
            'inactive_forms' => $inactiveForms,
            'governance_issues' => $governanceIssues,
            'healthy' => $governanceIssues === 0,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $activeCards
     * @return list<array<string, mixed>>
     */
    protected function buildRecentlyModified(array $activeCards): array
    {
        return collect($activeCards)
            ->filter(fn (array $card) => filled($card['updated_at']))
            ->sortByDesc('updated_at')
            ->take(5)
            ->map(fn (array $card) => [
                'id' => $card['id'],
                'title' => $card['title'],
                'href' => $card['href'],
                'updated_label' => $card['updated_label'],
                'category_label' => $card['category_label'],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     * @param  list<array<string, mixed>>  $activeCards
     * @param  list<array<string, mixed>>  $plannedCards
     * @return array<string, int>
     */
    /**
     * @param  array<string, mixed>  $compliance
     */
    protected function buildSummary(array $cards, array $activeCards, array $plannedCards, array $compliance): array
    {
        $managedFields = collect($activeCards)->sum(fn (array $card) => $card['metrics']['field_count']);

        return [
            'total_forms' => count($cards),
            'active_forms' => count(array_filter($activeCards, fn (array $card) => $card['is_active'])),
            'planned_forms' => count($plannedCards),
            'managed_fields' => $managedFields,
            'operational_forms' => $compliance['total_forms'] ?? 0,
            'governed_forms' => $compliance['governed_forms'] ?? 0,
            'non_governed_forms' => $compliance['non_governed_forms'] ?? 0,
            'compliance_percent' => $compliance['compliance_percent'] ?? 0,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $activeCards
     * @return array<string, mixed>
     */
    protected function buildExportPayload(int $companyId, ?int $branchId, array $activeCards): array
    {
        return [
            'exported_at' => now()->toIso8601String(),
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'forms' => collect($activeCards)->map(fn (array $card) => [
                'form_key' => $card['form_key'],
                'label' => $card['title'],
                'category' => $card['category_slug'],
                'is_active' => $card['is_active'],
                'metrics' => $card['metrics'],
                'governance' => $card['governance'],
                'updated_at' => $card['updated_at'],
            ])->values()->all(),
        ];
    }

    protected function loadTimestamps(int $companyId, ?int $branchId): Collection
    {
        return FormSetting::query()
            ->where('company_id', $companyId)
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->with('fields:id,form_setting_id,updated_at')
            ->get(['id', 'form_key', 'updated_at'])
            ->keyBy('form_key');
    }

    protected function resolveUpdatedAt(string $formKey, Collection $timestamps): ?Carbon
    {
        $setting = $timestamps->get($formKey);

        if ($setting === null) {
            return null;
        }

        $latest = Carbon::parse($setting->updated_at);

        foreach ($setting->fields as $field) {
            $fieldUpdated = Carbon::parse($field->updated_at);

            if ($fieldUpdated->gt($latest)) {
                $latest = $fieldUpdated;
            }
        }

        return $latest;
    }

    protected function formatUpdatedLabel(?Carbon $updatedAt): string
    {
        if ($updatedAt === null) {
            return __('Not yet modified');
        }

        if ($updatedAt->isToday()) {
            return __('Today') . ', ' . $updatedAt->format('H:i');
        }

        if ($updatedAt->isYesterday()) {
            return __('Yesterday') . ', ' . $updatedAt->format('H:i');
        }

        return $updatedAt->translatedFormat('M j, Y H:i');
    }
}
