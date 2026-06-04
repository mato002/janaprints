<?php

namespace App\View\Composers;

use App\Support\Navigation\WorkspaceNavigationResolver;
use Illuminate\View\View;

class WorkspaceNavigationComposer
{
    public function __construct(
        protected WorkspaceNavigationResolver $navigation,
    ) {}

    public function compose(View $view): void
    {
        $useAuto = $view->getData()['useWorkspaceNavigation'] ?? true;

        if ($useAuto === false) {
            return;
        }

        $title = (string) ($view->getData()['title'] ?? '');
        $tail = $view->getData()['breadcrumbTail'] ?? [];
        $legacy = $view->getData()['breadcrumbs'] ?? [];

        if ($tail === [] && $legacy !== []) {
            $tail = $this->extractTailFromLegacy($legacy);
        }

        $context = $this->navigation->resolve(pageTitle: $title !== '' ? $title : null);

        if (! $context) {
            if ($legacy !== []) {
                $view->with('breadcrumbs', $legacy);

                return;
            }

            return;
        }

        $breadcrumbs = $context['breadcrumbs'];

        foreach ($tail as $crumb) {
            if (! empty($breadcrumbs)) {
                $last = array_pop($breadcrumbs);
                if (! empty($last['label'])) {
                    $breadcrumbs[] = $last;
                }
            }

            $breadcrumbs[] = [
                'label' => $crumb['label'] ?? '',
                'url' => $crumb['url'] ?? null,
            ];
        }

        if ($breadcrumbs !== [] && ! empty($title)) {
            $lastIndex = array_key_last($breadcrumbs);
            $breadcrumbs[$lastIndex] = ['label' => $title];
        }

        $view->with([
            'workspaceNavigation' => $context,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @param  list<array{label: string, url?: string}>  $legacy
     * @return list<array{label: string, url?: string}>
     */
    protected function extractTailFromLegacy(array $legacy): array
    {
        if (count($legacy) <= 1) {
            return [];
        }

        $tail = array_slice($legacy, 0, -1);

        return array_values(array_filter($tail, fn ($crumb) => empty($crumb['url'])));
    }
}
