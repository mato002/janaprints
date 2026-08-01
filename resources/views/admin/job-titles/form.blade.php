<x-admin-layout :title="$jobTitle ? __('Edit job title') : __('Create job title')" :breadcrumbs="[['label' => __('Job Titles'), 'url' => route('admin.job-titles.index')], ['label' => $jobTitle ? __('Edit') : __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ $action }}">@csrf @if($method !== 'POST') @method($method) @endif
            @if (auth()->user()->hasRole('Super Admin'))
                <x-admin.lookup-company-select :companies="$companies" :value="old('company_id', $jobTitle?->company_id)" class="mb-4" />
            @else<input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">@endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin.entity-code-input :record="$jobTitle" />
                <div><x-input-label for="title" :value="__('Title')" /><x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title', $jobTitle?->title)" required /></div>
                <x-admin.lookup-select
                    name="department_id"
                    :label="__('Department')"
                    :options="$departments"
                    :value="old('department_id', $jobTitle?->department_id)"
                    create-route="admin.departments.quick-create"
                    refresh-route="admin.lookups.departments"
                    permission="departments.manage"
                    :modal-title="__('Create department')"
                    option-label-key="name"
                    select-class="erp-select mt-1 w-full"
                    scope-company-field="company_id"
                    :placeholder="__('None')"
                />
                <div><x-input-label for="level" :value="__('Level')" />
                    <select name="level" class="erp-select mt-1 w-full" required>
                        @foreach ($levels as $level)
                            <option value="{{ $level->value }}" @selected(old('level', $jobTitle?->level?->value) === $level->value)>{{ $level->label() }}</option>
                        @endforeach
                    </select></div>
                <div><x-input-label for="reports_to_job_title_id" :value="__('Reports To')" />
                    <select name="reports_to_job_title_id" class="erp-select mt-1 w-full">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($reportsToOptions as $option)
                            <option value="{{ $option->id }}" @selected(old('reports_to_job_title_id', $jobTitle?->reports_to_job_title_id) == $option->id)>{{ $option->title }}</option>
                        @endforeach
                    </select></div>
                <div><x-input-label for="sort_order" :value="__('Sort Order')" /><x-text-input id="sort_order" name="sort_order" type="number" min="0" class="block mt-1 w-full" :value="old('sort_order', $jobTitle?->sort_order ?? 100)" /></div>
                <div class="md:col-span-2"><x-input-label for="approval_authority" :value="__('Approval Authority')" />
                    <select name="approval_authority" class="erp-select mt-1 w-full">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($approvalRoles as $role)
                            <option value="{{ $role }}" @selected(old('approval_authority', $jobTitle?->approval_authority) === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Default security role used for approval chains and delegations.') }}</p>
                </div>
                <div class="md:col-span-2"><x-input-label for="description" :value="__('Description')" /><textarea name="description" class="erp-input mt-1 w-full" rows="3">{{ old('description', $jobTitle?->description) }}</textarea></div>
            </div>

            <label class="flex gap-2 mt-4"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $jobTitle?->is_active ?? true))> {{ __('Active') }}</label>
            <div class="mt-6"><x-primary-button>{{ __('Save') }}</x-primary-button></div>
        </form>
    </div>
</x-admin-layout>
