<?php

namespace App\View\Components;

use App\Support\Navigation\WorkspaceEmbed;
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
        /** Compact module workspace desk — reduced chrome padding and header height. */
        public bool $compactWorkspace = false,
        /** Embedded module workspace content — renders inside turbo-frame without app chrome. */
        public bool $embedded = false,
    ) {
        if (
            ! $embedded
            && WorkspaceEmbed::inWorkspaceContext()
            && ! $this->routeShouldPromoteToMainShell()
        ) {
            $this->embedded = true;
            $this->useWorkspaceNavigation = false;
            $this->compactPage = false;
        }
    }

    protected function routeShouldPromoteToMainShell(): bool
    {
        $routeName = request()->route()?->getName();

        if (! $routeName) {
            return false;
        }

        // Settings sections are workspace tab content, not entity detail pages.
        if ($routeName === 'admin.settings.show') {
            return false;
        }

        foreach (['.create', '.edit', '.show', '.document', '.pdf'] as $suffix) {
            if (str_ends_with($routeName, $suffix)) {
                return true;
            }
        }

        if (
            str_ends_with($routeName, '.receipt')
            && ! str_ends_with($routeName, '.receipt.pdf')
            && ! str_ends_with($routeName, '.receipt.email')
            && ! str_ends_with($routeName, '.receipt.sms')
        ) {
            return true;
        }

        return false;
    }

    public function render(): View
    {
        return view($this->embedded ? 'layouts.admin-embedded' : 'layouts.admin');
    }
}
