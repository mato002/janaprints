<x-admin-layout :title="__('New Vacancy')">
    <x-admin.page-header :title="__('New Vacancy')" />

    <form method="POST" action="{{ route('admin.hr.recruitment.vacancies.store') }}" class="erp-card max-w-3xl">
        @csrf
        @include('admin.hr.recruitment.partials.vacancy-form', ['formData' => $formData])
        <div class="mt-6 flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Create vacancy') }}</button>
            <a href="{{ route('admin.hr.recruitment.vacancies.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
