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
    ) {}

    public function render(): View
    {
        return view('layouts.admin');
    }
}
