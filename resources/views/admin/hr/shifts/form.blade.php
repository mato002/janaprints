@props(['shift' => null, 'shiftTypes', 'companies' => collect(), 'action', 'method' => 'POST'])

<div class="bg-white shadow rounded-lg p-6 max-w-3xl">
    <form method="POST" action="{{ $action }}">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        @if ($companies->isNotEmpty())
            <div class="mb-4">
                <x-input-label for="company_id" :value="__('Company')" />
                <select name="company_id" class="erp-select mt-1 w-full" required>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected(old('company_id', $shift?->company_id) == $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="code" :value="__('Code')" />
                <x-text-input id="code" name="code" class="block mt-1 w-full" :value="old('code', $shift?->code)" required />
            </div>
            <div>
                <x-input-label for="name" :value="__('Shift Name')" />
                <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $shift?->name)" required />
            </div>
            <div>
                <x-input-label for="shift_type" :value="__('Shift Type')" />
                <select name="shift_type" id="shift_type" class="erp-select mt-1 w-full" required>
                    @foreach ($shiftTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('shift_type', $shift?->shift_type?->value) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="start_time" :value="__('Start Time')" />
                <x-text-input id="start_time" name="start_time" type="time" class="block mt-1 w-full" :value="old('start_time', $shift?->start_time ? substr($shift->start_time, 0, 5) : null)" required />
            </div>
            <div>
                <x-input-label for="end_time" :value="__('End Time')" />
                <x-text-input id="end_time" name="end_time" type="time" class="block mt-1 w-full" :value="old('end_time', $shift?->end_time ? substr($shift->end_time, 0, 5) : null)" required />
            </div>
            <div>
                <x-input-label for="grace_minutes" :value="__('Grace Minutes')" />
                <x-text-input id="grace_minutes" name="grace_minutes" type="number" min="0" class="block mt-1 w-full" :value="old('grace_minutes', $shift?->grace_minutes ?? 0)" required />
            </div>
            <div>
                <x-input-label for="break_minutes" :value="__('Break Minutes')" />
                <x-text-input id="break_minutes" name="break_minutes" type="number" min="0" class="block mt-1 w-full" :value="old('break_minutes', $shift?->break_minutes ?? 0)" required />
            </div>
        </div>

        <label class="flex gap-2 mt-4">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $shift?->is_active ?? true))>
            {{ __('Active') }}
        </label>

        <div class="mt-6">
            <x-primary-button>{{ __('Save shift') }}</x-primary-button>
        </div>
    </form>
</div>
