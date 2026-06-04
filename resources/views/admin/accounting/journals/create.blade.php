<x-admin-layout :title="__('New journal')" :breadcrumbs="[['label' => __('Journals'), 'url' => route('admin.accounting.journals.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New journal entry')" />

    <x-admin.card class="max-w-5xl">
        <form method="POST" action="{{ route('admin.accounting.journals.store') }}" class="space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="accounting_period_id" :value="__('Accounting period')" />
                    <select name="accounting_period_id" id="accounting_period_id" class="erp-input mt-1 w-full" required>
                        @foreach ($periods as $period)
                            <option value="{{ $period->id }}" @selected(old('accounting_period_id', $currentPeriodId) == $period->id)>
                                {{ $period->code }} — {{ $period->name }} ({{ $period->status->label() }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="journal_date" :value="__('Journal date')" />
                    <x-text-input type="date" name="journal_date" id="journal_date" class="mt-1 w-full" :value="old('journal_date', $defaultDate)" required />
                </div>
                <div>
                    <x-input-label for="reference" :value="__('Reference')" />
                    <x-text-input name="reference" id="reference" class="mt-1 w-full" :value="old('reference')" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea name="description" id="description" rows="2" class="erp-input mt-1 w-full">{{ old('description') }}</textarea>
                </div>
            </div>
            @include('admin.accounting.journals.partials.lines-form', ['journal' => null])
            <div class="flex gap-2">
                <x-primary-button>{{ __('Save draft') }}</x-primary-button>
                <a href="{{ route('admin.accounting.journals.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
