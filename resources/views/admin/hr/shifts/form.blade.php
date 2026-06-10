@props(['shift' => null, 'shiftTypes', 'companies' => collect(), 'action', 'method' => 'POST'])

<div class="bg-white shadow rounded-lg p-6 max-w-3xl">
    <x-admin.form-shell :action="$action" :method="$method">
        @include('admin.hr.shifts.partials.form-fields', [
            'shift' => $shift,
            'shiftTypes' => $shiftTypes,
            'companies' => $companies,
        ])
        <div class="mt-6">
            <x-primary-button>{{ __('Save shift') }}</x-primary-button>
        </div>
    </x-admin.form-shell>
</div>
