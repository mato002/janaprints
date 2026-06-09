<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    public function __construct(
        public string $title = '',
        public array $breadcrumbs = [],
        /** @var list<array{label: string, url?: string}> Extra crumbs before the current page (e.g. edit steps). */
        public array $breadcrumbTail = [],
        public bool $useWorkspaceNavigation = true,
        /** Full-height workspace pages (e.g. Shared Inbox) — minimal chrome, no breadcrumbs. */
        public bool $compactPage = false,
        /** Embedded module workspace content — renders inside turbo-frame without app chrome. */
        public bool $embedded = false,
    ) {
        if (! $embedded && request()->query('embedded') === '1') {
            // Embedded pages are turbo-frame fragments; only strip the admin shell
            // when the workspace frame is loading content (not on direct browser visits).
            $this->embedded = request()->header('Turbo-Frame') === 'module-workspace-content';

            if ($this->embedded) {
                $this->useWorkspaceNavigation = false;
                $this->compactPage = false;
            }
        }
    }

    public function render(): View
    {
        return view($this->embedded ? 'layouts.admin-embedded' : 'layouts.admin');
    }
}
