<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class EssLayout extends Component
{
    public function __construct(
        public string $title = '',
        public string $activeTab = 'overview',
        /** @var list<array{id: string, label: string}> */
        public array $tabs = [],
    ) {}

    public function render(): View
    {
        return view('layouts.ess');
    }
}
