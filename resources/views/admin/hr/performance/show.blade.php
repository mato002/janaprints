<x-admin-layout :title="$review->reference" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Performance'), 'url' => route('admin.hr.performance.dashboard')], ['label' => $review->reference]]">
    <x-admin.page-header :title="$review->reference" :description="$review->employee->full_name.' · '.$review->cycle->label()">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.performance.index') }}" class="erp-btn-secondary">{{ __('Back') }}</a>
            @can('update', $review)
                @if ($review->status->value === 'draft')
                    <form method="POST" action="{{ route('admin.hr.performance.submit', $review) }}">
                        @csrf
                        <button type="submit" class="erp-btn-primary">{{ __('Submit review') }}</button>
                    </form>
                @endif
            @endcan
            @can('delete', $review)
                <form method="POST" action="{{ route('admin.hr.performance.destroy', $review) }}" onsubmit="return confirm(@js(__('Delete this performance review?')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="erp-btn-secondary text-rose-700">{{ __('Delete') }}</button>
                </form>
            @endcan
        </x-slot>
    </x-admin.page-header>

<div class="grid gap-4 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-1" :title="__('Appraisal Summary')">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">{{ __('Employee') }}</dt>
                    <dd class="font-medium">{{ $review->employee->full_name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Period') }}</dt>
                    <dd>{{ $review->period_start->format('Y-m-d') }} – {{ $review->period_end->format('Y-m-d') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Composite Score') }}</dt>
                    <dd class="text-lg font-semibold">{{ number_format($review->composite_score, 1) }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Rating') }}</dt>
                    <dd>{{ $review->rating?->label() ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Status') }}</dt>
                    <dd>{{ $review->status->label() }}</dd>
                </div>
                @if ($review->reviewedBy)
                    <div>
                        <dt class="text-slate-500">{{ __('Reviewed By') }}</dt>
                        <dd>{{ $review->reviewedBy->name }} · {{ $review->reviewed_at?->format('Y-m-d') }}</dd>
                    </div>
                @endif
            </dl>
        </x-admin.card>

        <x-admin.card class="lg:col-span-2" :title="__('KPI Scorecard')">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ([
                    ['label' => __('Production Output'), 'value' => number_format($review->production_output, 0), 'hint' => __('Completed operations')],
                    ['label' => __('Sales Actual'), 'value' => number_format($review->sales_actual, 2), 'hint' => __('Revenue in period')],
                    ['label' => __('Sales Target'), 'value' => number_format($review->sales_target, 2), 'hint' => __('Target for period')],
                    ['label' => __('Attendance %'), 'value' => number_format($review->attendance_percent, 1).'%', 'hint' => __('Days worked vs expected')],
                    ['label' => __('Quality %'), 'value' => number_format($review->quality_percent, 1).'%', 'hint' => __('QC pass rate')],
                    ['label' => __('Job Completion'), 'value' => number_format($review->job_completion_percent, 1).'%', 'hint' => __('Assigned ops completed')],
                    ['label' => __('Customer Ratings'), 'value' => number_format($review->customer_rating, 1).'%', 'hint' => __('Resolution rate proxy')],
                ] as $kpi)
                    <div class="rounded-lg border border-slate-200 p-3">
                        <p class="text-xs uppercase tracking-wide text-slate-500">{{ $kpi['label'] }}</p>
                        <p class="mt-1 text-xl font-semibold">{{ $kpi['value'] }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $kpi['hint'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-admin.card>
    </div>

    @if ($review->strengths || $review->improvements || $review->manager_notes)
        <div class="mt-4 grid gap-4 lg:grid-cols-3">
            @if ($review->strengths)
                <x-admin.card :title="__('Strengths')"><p class="text-sm whitespace-pre-line">{{ $review->strengths }}</p></x-admin.card>
            @endif
            @if ($review->improvements)
                <x-admin.card :title="__('Areas for Improvement')"><p class="text-sm whitespace-pre-line">{{ $review->improvements }}</p></x-admin.card>
            @endif
            @if ($review->manager_notes)
                <x-admin.card :title="__('Manager Notes')"><p class="text-sm whitespace-pre-line">{{ $review->manager_notes }}</p></x-admin.card>
            @endif
        </div>
    @endif
</x-admin-layout>
