<x-admin.modal-form
    :title="__('New Application')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Recruitment'), 'url' => route('admin.hr.recruitment.dashboard')],
        ['label' => __('Applications'), 'url' => route('admin.hr.recruitment.applications.index')],
        ['label' => __('New')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.hr.recruitment.applications.store')">
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

        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Submit application') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
