<x-admin-layout :title="__('Training Calendar')">
    <x-admin.page-header :title="__('Training Calendar')" :description="__('Scheduled training programs.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.training.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="text-[11px] text-slate-500" for="month">{{ __('Month') }}</label>
                <input type="number" id="month" name="month" min="1" max="12" value="{{ $month }}" class="erp-input mt-1 w-20">
            </div>
            <div>
                <label class="text-[11px] text-slate-500" for="year">{{ __('Year') }}</label>
                <input type="number" id="year" name="year" value="{{ $year }}" class="erp-input mt-1 w-24">
            </div>
            <button type="submit" class="erp-btn-primary">{{ __('Apply') }}</button>
        </form>

        <div class="space-y-3">
            @forelse ($programs as $program)
                <div class="rounded-lg border border-erp-border/70 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <a href="{{ route('admin.hr.training.programs.show', $program) }}" class="font-medium text-erp-primary hover:underline">{{ $program->title }}</a>
                            <p class="text-xs text-slate-500">{{ $program->code }} · {{ $program->status->label() }}</p>
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
