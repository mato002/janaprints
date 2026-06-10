<turbo-frame id="module-workspace-content">
    <div class="module-workspace-embedded w-full min-w-0">
        @include('admin.partials.alerts')

        @include('admin.settings.partials.scope-selector', [
            'action' => route('admin.settings.forms.index', $scopeQuery),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
        ])

        @include('admin.settings.forms.partials.landing', [
            'controlCenter' => $controlCenter,
            'canManage' => $canManage,
        ])
    </div>
</turbo-frame>
