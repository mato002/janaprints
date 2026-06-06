<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Services\Commercial\PublicQuoteRequestCountService;
use App\Support\Commercial\CommercialDashboardPresenter;
use App\Support\Commercial\PublicLeadsDashboardPresenter;
use App\Support\Navigation\CommercialWorkspacePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialWorkspaceController extends Controller
{
    public function __construct(
        protected CommercialWorkspacePresenter $presenter,
        protected CommercialDashboardPresenter $dashboard,
        protected PublicLeadsDashboardPresenter $publicLeads,
    ) {}

    public function hub(Request $request): View
    {
        $payload = $this->presenter->presentHub();

        abort_if($payload === null, 403);

        $quoteCounts = app(PublicQuoteRequestCountService::class);
        $quoteAlert = $quoteCounts->canView() ? $quoteCounts->alertPayload() : ['has_action' => false];

        return view('admin.commercial.workspaces.hub', [
            'workspace' => $payload,
            'quoteRequestsAlert' => $quoteAlert,
            'cards' => collect($payload['items'])->map(fn (array $item) => array_merge($item, [
                'group_label' => __('Workspaces'),
                'search_text' => strtolower(implode(' ', array_filter([
                    __('Workspaces'),
                    $item['label'],
                    $item['description'],
                ]))),
            ]))->all(),
        ]);
    }

    public function section(Request $request, string $section): View
    {
        abort_unless($this->presenter->sectionExists($section), 404);

        $payload = $this->presenter->presentSection($section);

        abort_if($payload === null, 403);

        $cards = collect($payload['groups'])
            ->flatMap(fn (array $group) => collect($group['items'])->map(fn (array $item) => array_merge($item, [
                'group_label' => $group['label'],
                'search_text' => strtolower(implode(' ', array_filter([
                    $group['label'],
                    $item['label'],
                    $item['description'],
                ]))),
            ])))
            ->values()
            ->all();

        return view('admin.commercial.workspaces.section', [
            'workspace' => $payload,
            'section' => $section,
            'cards' => $cards,
            'quickActions' => $payload['quick_actions'] ?? [],
            'sectionNote' => $payload['section_note'] ?? null,
            'widgets' => match ($section) {
                'crm' => $this->dashboard->widgets(),
                'customer-service' => $this->publicLeads->widgets(),
                default => [],
            },
        ]);
    }
}
