<x-admin-layout :title="__('Training Calendar')">
    <x-admin.page-header :title="__('Training Calendar')" :description="__('Scheduled training programs.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.training.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <form method="GET" action="{{ route('admin.hr.training.calendar') }}" class="mb-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="erp-label text-xs" for="month">{{ __('Month') }}</label>
                <input type="number" id="month" name="month" min="1" max="12" value="{{ $month }}" class="erp-toolbar-input w-20">
            </div>
            <div>
                <label class="erp-label text-xs" for="year">{{ __('Year') }}</label>
                <input type="number" id="year" name="year" value="{{ $year }}" class="erp-toolbar-input w-24">
            </div>
            <div>
                <label class="erp-label text-xs" for="status">{{ __('Status') }}</label>
                <select id="status" name="status" class="erp-toolbar-input">
                    <option value="">{{ __('All') }}</option>
                    @foreach (\App\Enums\TrainingProgramStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label text-xs" for="type">{{ __('Type') }}</label>
                <select id="type" name="type" class="erp-toolbar-input">
                    <option value="">{{ __('All') }}</option>
                    @foreach (\App\Enums\TrainingType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="erp-btn-secondary text-xs">{{ __('Filter') }}</button>
        </form>

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
                <p class="text-sm text-slate-500">{{ __('No scheduled programs for this period.') }}</p>
            @endforelse
        </div>
    </x-admin.card>
</x-admin-layout>
