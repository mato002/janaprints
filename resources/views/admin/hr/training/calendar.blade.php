<x-admin-layout :title="__('Training Calendar')">
    <x-admin.page-header :title="__('Training Calendar')" :description="__('Scheduled training programs.')">
        <x-slot name="secondary">
            <a href="{{ route('admin.hr.training.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.hr.training.calendar')" :reset-url="route('admin.hr.training.calendar')">
            <input type="number" name="month" min="1" max="12" value="{{ $month }}" class="erp-toolbar-input w-20" aria-label="{{ __('Month') }}">
            <input type="number" name="year" value="{{ $year }}" class="erp-toolbar-input w-24" aria-label="{{ __('Year') }}">
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (\App\Enums\TrainingProgramStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <select name="type" class="erp-toolbar-select" aria-label="{{ __('Type') }}">
                <option value="">{{ __('All types') }}</option>
                @foreach (\App\Enums\TrainingType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.card>
        <div class="space-y-3">
            @forelse ($programs as $program)
                <div class="rounded-lg border border-erp-border/70 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <a href="{{ route('admin.hr.training.programs.show', $program) }}" class="font-medium text-erp-primary hover:underline">{{ $program->title }}</a>
                            <p class="text-xs text-slate-500">{{ $program->code }} · {{ $program->type->label() }} · {{ $program->status->label() }}</p>
                        </div>
                        <span class="text-sm tabular-nums text-slate-600">
                            {{ $program->scheduled_start_date?->format('M j') }}
                            @if ($program->scheduled_end_date)
                                — {{ $program->scheduled_end_date->format('M j, Y') }}
                            @endif
                        </span>
                    </div>
                    @if ($program->budget_amount)
                        <p class="mt-1 text-xs text-slate-500">{{ __('Budget') }}: {{ number_format($program->budget_amount, 2) }}</p>
                    @endif
                </div>
            @empty
                <x-admin.empty-state icon="calendar" :title="__('No scheduled programs')" :description="__('No scheduled programs for this period.')" />
            @endforelse
        </div>
    </x-admin.card>
</x-admin-layout>
