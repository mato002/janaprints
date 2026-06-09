<x-admin-layout :title="__('Performance Reviews')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Performance'), 'url' => route('admin.hr.performance.dashboard')], ['label' => __('Reviews')]]">
    <x-admin.page-header :title="__('Performance Reviews')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.performance.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\PerformanceReview::class)
                <a href="{{ route('admin.hr.performance.create') }}" class="erp-btn-primary">{{ __('New appraisal') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.hr.performance.index')" :reset-url="route('admin.hr.performance.index')">
            <select name="employee_id" class="erp-toolbar-select" aria-label="{{ __('Employee') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($formData['employees'] as $employee)
                    <option value="{{ $employee->id }}" @selected((int) ($filters['employee_id'] ?? 0) === $employee->id)>{{ $employee->full_name }}</option>
                @endforeach
            </select>
            <select name="cycle" class="erp-toolbar-select" aria-label="{{ __('Cycle') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($formData['cycles'] as $cycle)
                    <option value="{{ $cycle->value }}" @selected(($filters['cycle'] ?? '') === $cycle->value)>{{ $cycle->label() }}</option>
                @endforeach
            </select>
            <select name="rating" class="erp-toolbar-select" aria-label="{{ __('Rating') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($formData['ratings'] as $rating)
                    <option value="{{ $rating->value }}" @selected(($filters['rating'] ?? '') === $rating->value)>{{ $rating->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($formData['statuses'] as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table :search-placeholder="__('Search reviews…')" export-filename="performance-reviews">
        <x-slot name="head">
            <tr>
                <th>{{ __('Reference') }}</th>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Cycle') }}</th>
                <th>{{ __('Period') }}</th>
                <th>{{ __('Score') }}</th>
                <th>{{ __('Rating') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($reviews as $review)
                <tr>
                    <td>
                        <a href="{{ route('admin.hr.performance.show', $review) }}" class="font-medium text-indigo-600 hover:underline">{{ $review->reference }}</a>
                    </td>
                    <td>{{ $review->employee->full_name }}</td>
                    <td>{{ $review->cycle->label() }}</td>
                    <td>{{ $review->period_start->format('Y-m-d') }} – {{ $review->period_end->format('Y-m-d') }}</td>
                    <td>{{ number_format($review->composite_score, 1) }}</td>
                    <td>{{ $review->rating?->label() ?? '—' }}</td>
                    <td>{{ $review->status->label() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-6 text-center text-slate-500">{{ __('No performance reviews found.') }}</td>
                </tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>

    <div class="mt-4">{{ $reviews->links() }}</div>
</x-admin-layout>
