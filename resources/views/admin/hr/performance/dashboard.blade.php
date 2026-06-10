<x-admin-layout :title="__('Performance')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Performance')]]">
    <x-admin.page-header :title="__('Performance Management')" :description="__('Employee KPIs, appraisals, and performance ratings.')">
        <x-slot name="actions">
            @can('create', App\Models\Hr\PerformanceReview::class)
                <a href="{{ route('admin.hr.performance.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New appraisal') }}</a>
            @endcan
            <a href="{{ route('admin.hr.performance.index') }}" class="erp-btn-secondary">{{ __('All reviews') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Reviews This Year'), 'value' => $stats['reviews_this_year'], 'icon' => 'badge-check'],
            ['label' => __('Submitted'), 'value' => $stats['submitted'], 'icon' => 'check-circle'],
            ['label' => __('Excellent Ratings'), 'value' => $stats['excellent_count'], 'icon' => 'star'],
            ['label' => __('Average Score'), 'value' => $stats['average_score'], 'icon' => 'chart-pie'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card class="mt-6" :title="__('Recent Appraisals')">
        @if ($recentReviews->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No performance reviews yet.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500">
                            <th class="py-2 pr-3">{{ __('Reference') }}</th>
                            <th class="py-2 pr-3">{{ __('Employee') }}</th>
                            <th class="py-2 pr-3">{{ __('Cycle') }}</th>
                            <th class="py-2 pr-3">{{ __('Score') }}</th>
                            <th class="py-2 pr-3">{{ __('Rating') }}</th>
                            <th class="py-2">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentReviews as $review)
                            <tr class="border-b border-slate-100">
                                <td class="py-2 pr-3">
                                    <a href="{{ route('admin.hr.performance.show', $review) }}" class="text-indigo-600 hover:underline">{{ $review->reference }}</a>
                                </td>
                                <td class="py-2 pr-3">{{ $review->employee->full_name }}</td>
                                <td class="py-2 pr-3">{{ $review->cycle->label() }}</td>
                                <td class="py-2 pr-3">{{ number_format($review->composite_score, 1) }}</td>
                                <td class="py-2 pr-3">{{ $review->rating?->label() ?? '—' }}</td>
                                <td class="py-2">{{ $review->status->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.card>
</x-admin-layout>
