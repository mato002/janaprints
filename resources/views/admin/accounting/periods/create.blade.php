<x-admin-layout :title="__('Generate fiscal year')" :breadcrumbs="[['label' => __('Accounting Periods'), 'url' => route('admin.accounting.periods.index')], ['label' => __('Generate')]]">
    <x-admin.page-header :title="__('Generate fiscal year')" :description="__('Creates 12 monthly periods using fiscal year start month :month', ['month' => \Carbon\Carbon::create(null, $startMonth, 1)->format('F')])" />

    <x-admin.card class="max-w-lg">
        <form method="POST" action="{{ route('admin.accounting.periods.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <x-input-label for="start_year" :value="__('Fiscal year start (calendar year)')" />
                    <x-text-input id="start_year" name="start_year" type="number" min="2000" max="2100" class="mt-1 w-full" :value="old('start_year', $suggestedYear)" required />
                    <p class="mt-1 text-[11px] text-slate-500">{{ __('The fiscal year begins in :month :year.', ['month' => \Carbon\Carbon::create(null, $startMonth, 1)->format('F'), 'year' => old('start_year', $suggestedYear)]) }}</p>
                </div>
                <div>
                    <x-input-label for="notes" :value="__('Notes (optional)')" />
                    <textarea name="notes" id="notes" rows="2" class="erp-input mt-1 w-full">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-2">
                <x-primary-button>{{ __('Generate') }}</x-primary-button>
                <a href="{{ route('admin.accounting.periods.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
