@php
    $checks = $readinessChecks ?? [];
    $staffWarning = collect($checks)->firstWhere('key', 'staff_role');
    $defaultRoleWarning = collect($checks)->firstWhere('key', 'default_role');
@endphp

@if (($staffWarning['status'] ?? null) === 'warning')
    <p class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
        {{ __('Staff role is not seeded. New activations will fall back to Viewer until Staff is created.') }}
    </p>
@endif

@if (($defaultRoleWarning['status'] ?? null) === 'warning')
    <p class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
        {{ __('No default activation role is available. Activations may complete without an ERP role assignment.') }}
    </p>
@endif

@if (filled($readinessChecks ?? null))
    <div class="mt-4">
        <a href="{{ route('admin.email-identity.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
            {{ __('View full email identity readiness checklist') }} →
        </a>
    </div>
@endif
