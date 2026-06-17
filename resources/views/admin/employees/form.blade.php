<x-admin-layout :title="$employee ? __('Edit employee') : __('Create employee')" :breadcrumbs="[['label' => __('Employees'), 'url' => route('admin.employees.index')], ['label' => $employee ? __('Edit') : __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ $action }}">@csrf @if($method !== 'POST') @method($method) @endif
            @include('admin.employees.partials.form-fields', ['employee' => $employee ?? null])
            <div class="mt-6"><x-primary-button>{{ __('Save') }}</x-primary-button></div>
        </form>
    </div>

    @if ($employee && isset($communicationTimeline))
        @include('admin.employees.partials.compensation-panel', ['employee' => $employee])
    @endif

    @if ($employee && isset($communicationTimeline))
        @include('admin.employees.partials.email-identity-panel', [
            'employee' => $employee,
            'activationStatus' => $activationStatus ?? 'none',
            'latestActivation' => $latestActivation ?? null,
            'readinessChecks' => $readinessChecks ?? [],
        ])
    @endif

    @if ($employee && isset($communicationTimeline))
        @include('admin.communications.logs.partials.entity-timeline', ['logs' => $communicationTimeline, 'title' => __('Employee communication history')])
    @endif
    @if ($employee && isset($emailTimeline) && $emailTimeline->isNotEmpty())
        @include('admin.communications.logs.partials.entity-timeline', ['logs' => $emailTimeline, 'title' => __('Employee email history')])
    @endif
</x-admin-layout>
