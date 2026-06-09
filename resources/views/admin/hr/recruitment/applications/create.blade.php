<x-admin-layout :title="__('New Application')">
    <x-admin.page-header :title="__('Record Application')" />

    <form method="POST" action="{{ route('admin.hr.recruitment.applications.store') }}" class="erp-card max-w-3xl">
        @csrf
        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="erp-label" for="vacancy_id">{{ __('Vacancy') }}</label>
                <select id="vacancy_id" name="vacancy_id" class="erp-input w-full" required>
                    <option value="">{{ __('Select open vacancy') }}</option>
                    @foreach ($openVacancies as $vacancy)
                        <option value="{{ $vacancy->id }}" @selected(old('vacancy_id') == $vacancy->id)>{{ $vacancy->reference }} — {{ $vacancy->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="first_name">{{ __('First Name') }}</label>
                <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label" for="last_name">{{ __('Last Name') }}</label>
                <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label" for="email">{{ __('Email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="erp-input w-full">
            </div>
            <div>
                <label class="erp-label" for="phone">{{ __('Phone') }}</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="erp-input w-full">
            </div>
            <div class="md:col-span-2">
                <label class="erp-label" for="source">{{ __('Source') }}</label>
                <input id="source" type="text" name="source" value="{{ old('source') }}" class="erp-input w-full" placeholder="{{ __('e.g. Referral, Job Board') }}">
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Submit application') }}</button>
            <a href="{{ route('admin.hr.recruitment.applications.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
