<x-admin-layout :title="__('Attendance Correction')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Attendance'), 'url' => route('admin.hr.attendance.index')], ['label' => __('Adjust')]]">
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-erp-primary mb-2">{{ $record->employee?->full_name }}</h2>
            <p class="text-sm text-slate-600 mb-4">{{ $record->attendance_date?->format('l, M j, Y') }}</p>

            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-slate-500">{{ __('Clock In') }}</dt><dd class="font-medium">{{ $record->clock_in_at?->format('Y-m-d H:i') ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Clock Out') }}</dt><dd class="font-medium">{{ $record->clock_out_at?->format('Y-m-d H:i') ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Hours Worked') }}</dt><dd class="font-medium">{{ $record->actual_hours ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Overtime') }}</dt><dd class="font-medium">{{ $record->overtime_hours }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Late (min)') }}</dt><dd class="font-medium">{{ $record->late_minutes }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd class="font-medium">{{ $record->status?->label() }}</dd></div>
            </dl>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-erp-primary mb-4">{{ __('Attendance Correction') }}</h3>
            <form method="POST" action="{{ route('admin.hr.attendance.adjust.store', $record) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <x-input-label for="correction_type" :value="__('Correction type')" />
                        <select name="correction_type" id="correction_type" class="erp-select mt-1 w-full" required>
                            @foreach ($correctionTypes as $type)
                                <option value="{{ $type->value }}" @selected(old('correction_type') === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="reason" :value="__('Reason')" />
                        <textarea name="reason" id="reason" rows="3" class="erp-input mt-1 w-full" required>{{ old('reason') }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="clock_in_at" :value="__('New clock in')" />
                        <x-text-input id="clock_in_at" name="clock_in_at" type="datetime-local" class="block mt-1 w-full" :value="old('clock_in_at', $record->clock_in_at?->format('Y-m-d\TH:i'))" />
                    </div>
                    <div>
                        <x-input-label for="clock_out_at" :value="__('New clock out')" />
                        <x-text-input id="clock_out_at" name="clock_out_at" type="datetime-local" class="block mt-1 w-full" :value="old('clock_out_at', $record->clock_out_at?->format('Y-m-d\TH:i'))" />
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Status override')" />
                        <select name="status" id="status" class="erp-select mt-1 w-full">
                            <option value="">{{ __('Auto-calculate') }}</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $record->status?->value) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-6">
                    <x-primary-button>{{ __('Submit correction') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    @if ($record->corrections->isNotEmpty())
        <x-admin.card class="mt-6">
            <h3 class="text-sm font-semibold text-erp-primary mb-3">{{ __('Correction history') }}</h3>
            <div class="space-y-3">
                @foreach ($record->corrections as $correction)
                    <div class="rounded border border-slate-200 p-3 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-medium">{{ $correction->correction_type->label() }}</span>
                            <span class="text-slate-500">{{ $correction->created_at?->format('Y-m-d H:i') }}</span>
                        </div>
                        <p class="mt-1 text-slate-600">{{ $correction->reason }}</p>
                        @if ($correction->approved_at === null && auth()->user()->can('approve', $record))
                            <form method="POST" action="{{ route('admin.hr.attendance.corrections.approve', $correction) }}" class="mt-2">
                                @csrf
                                <button type="submit" class="erp-btn-secondary text-xs">{{ __('Approve correction') }}</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-admin.card>
    @endif
</x-admin-layout>
