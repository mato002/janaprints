<turbo-frame id="module-workspace-content">
    <div class="module-workspace-embedded w-full min-w-0">
        @if (! empty($statusMessage))
            <div
                class="mb-4 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-erp-success"
                role="status"
                data-erp-flash-status
            >
                <x-admin.icon name="badge-check" class="h-5 w-5 shrink-0" />
                <span>{{ $statusMessage }}</span>
            </div>
        @endif

        @if (! empty($errorMessage))
            <div
                class="mb-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-erp-danger"
                role="alert"
                data-erp-flash-error
                data-erp-validation-errors
            >
                <x-admin.icon name="exclamation" class="h-5 w-5 shrink-0" />
                <div>
                    <p class="font-medium">{{ $errorMessage }}</p>
                    @if (! empty($validationErrors) && $validationErrors->any())
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($validationErrors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endif

        @include('admin.partials.alerts')

        @include('admin.settings.partials.scope-selector', [
            'action' => route('admin.settings.forms.index', ['form' => $activeFormKey, 'embedded' => '1'] + $scopeQuery),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
            'compact' => true,
            'activeFormKey' => $activeFormKey,
        ])

        @include('admin.settings.forms.partials.workspace', [
            'form' => $activeForm,
            'canManage' => $canManage,
            'companyId' => $companyId,
            'branchId' => $branchId,
        ])
    </div>
</turbo-frame>
