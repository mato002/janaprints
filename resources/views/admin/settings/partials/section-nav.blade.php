<nav class="lg:col-span-1" aria-label="{{ __('Settings sections') }}">
    <x-admin.card class="p-0 overflow-hidden">
        <ul class="divide-y divide-erp-border">
            @foreach ($sections as $slug => $meta)
                <li>
                    <a
                        href="{{ route('admin.settings.show', ['section' => $slug, 'company_id' => $companyId, 'branch_id' => $branchId]) }}"
                        data-turbo-action="advance"
                        @class([
                            'block px-4 py-3 text-sm font-medium transition-colors',
                            'bg-erp-accent/10 text-erp-accent' => ($current ?? null) === $slug,
                            'text-slate-600 hover:bg-erp-page hover:text-erp-primary' => ($current ?? null) !== $slug,
                        ])
                    >
                        {{ __($meta['label']) }}
                    </a>
                </li>
            @endforeach
            <li>
                <a
                    href="{{ route('admin.settings.numbering.index', ['company_id' => $companyId, 'branch_id' => $branchId]) }}"
                    data-turbo-action="advance"
                    @class([
                        'block px-4 py-3 text-sm font-medium transition-colors',
                        'bg-erp-accent/10 text-erp-accent' => ($current ?? null) === 'numbering',
                        'text-slate-600 hover:bg-erp-page hover:text-erp-primary' => ($current ?? null) !== 'numbering',
                    ])
                >
                    {{ __('Numbering') }}
                </a>
            </li>
            <li>
                <a
                    href="{{ route('admin.settings.approvals.index', ['company_id' => $companyId, 'branch_id' => $branchId]) }}"
                    data-turbo-action="advance"
                    @class([
                        'block px-4 py-3 text-sm font-medium transition-colors',
                        'bg-erp-accent/10 text-erp-accent' => ($current ?? null) === 'approvals',
                        'text-slate-600 hover:bg-erp-page hover:text-erp-primary' => ($current ?? null) !== 'approvals',
                    ])
                >
                    {{ __('Approvals') }}
                </a>
            </li>
            <li>
                <a
                    href="{{ route('admin.settings.forms.index', ['company_id' => $companyId, 'branch_id' => $branchId]) }}"
                    data-turbo-action="advance"
                    @class([
                        'block px-4 py-3 text-sm font-medium transition-colors',
                        'bg-erp-accent/10 text-erp-accent' => ($current ?? null) === 'forms',
                        'text-slate-600 hover:bg-erp-page hover:text-erp-primary' => ($current ?? null) !== 'forms',
                    ])
                >
                    {{ __('Forms') }}
                </a>
            </li>
        </ul>
    </x-admin.card>
</nav>
