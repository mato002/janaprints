<div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="erp-label" for="title">{{ __('Title') }}</label>
        <input id="title" type="text" name="title" value="{{ old('title') }}" class="erp-input w-full" required>
    </div>
    <div>
        <label class="erp-label" for="department_id">{{ __('Department') }}</label>
        <select id="department_id" name="department_id" class="erp-input w-full">
            <option value="">{{ __('Select') }}</option>
            @foreach ($formData['departments'] as $department)
                <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="erp-label" for="job_title_id">{{ __('Job Title') }}</label>
        <select id="job_title_id" name="job_title_id" class="erp-input w-full">
            <option value="">{{ __('Select') }}</option>
            @foreach ($formData['jobTitles'] as $jobTitle)
                <option value="{{ $jobTitle->id }}" @selected(old('job_title_id') == $jobTitle->id)>{{ $jobTitle->title }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="erp-label" for="headcount">{{ __('Headcount') }}</label>
        <input id="headcount" type="number" name="headcount" value="{{ old('headcount', 1) }}" min="1" class="erp-input w-full">
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="description">{{ __('Description') }}</label>
        <textarea id="description" name="description" rows="3" class="erp-input w-full">{{ old('description') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="justification">{{ __('Justification') }}</label>
        <textarea id="justification" name="justification" rows="2" class="erp-input w-full">{{ old('justification') }}</textarea>
    </div>
</div>
