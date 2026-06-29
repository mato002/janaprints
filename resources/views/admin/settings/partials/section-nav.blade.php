@php
    use App\Support\Navigation\WorkspaceEmbed;

    $turboFrame = WorkspaceEmbed::turboFrame();
    $linkClass = fn (bool $active) => [
        'block px-4 py-3 text-sm font-medium transition-colors',
        'bg-erp-accent/10 text-erp-accent' => $active,
        'text-slate-600 hover:bg-erp-page hover:text-erp-primary' => ! $active,
    ];
@endphp

<nav class="lg:col-span-1" aria-label="{{ __('Settings sections') }}">
    <x-admin.card class="p-0 overflow-hidden">
        <ul class="divide-y divide-erp-border">
            @foreach ($sections as $slug => $meta)
                <li>
                    <a
                        href="{{ WorkspaceEmbed::url(route('admin.settings.show', ['section' => $slug, 'company_id' => $companyId, 'branch_id' => $branchId])) }}"
                        data-turbo-frame="{{ $turboFrame }}"
                        data-turbo-action="advance"
                        @class($linkClass(($current ?? null) === $slug))
                    >
                        {{ __($meta['label']) }}
                    </a>
                </li>
            @endforeach
            <li>
                <a
                    href="{{ WorkspaceEmbed::url(route('admin.settings.numbering.index', ['company_id' => $companyId, 'branch_id' => $branchId])) }}"
                    data-turbo-frame="{{ $turboFrame }}"
                    data-turbo-action="advance"
                    @class($linkClass(($current ?? null) === 'numbering'))
                >
                    {{ __('Numbering') }}
                </a>
            </li>
            <li>
                <a
                    href="{{ WorkspaceEmbed::url(route('admin.settings.approvals.index', ['company_id' => $companyId, 'branch_id' => $branchId])) }}"
                    data-turbo-frame="{{ $turboFrame }}"
                    data-turbo-action="advance"
                    @class($linkClass(($current ?? null) === 'approvals'))
                >
                    {{ __('Approvals') }}
                </a>
            </li>
            <li>
                <a
                    href="{{ WorkspaceEmbed::url(route('admin.settings.forms.index', ['company_id' => $companyId, 'branch_id' => $branchId])) }}"
                    data-turbo-frame="{{ $turboFrame }}"
                    data-turbo-action="advance"
                    @class($linkClass(($current ?? null) === 'forms'))
                >
                    {{ __('Forms') }}
                </a>
            </li>
        </ul>
    </x-admin.card>
</nav>
