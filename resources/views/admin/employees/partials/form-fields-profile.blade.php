<div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
    @include('admin.employees.partials.form-section-heading', [
        'title' => __('Personal & identity'),
        'description' => __('Core identity details used across HR records.'),
    ])

    <div>
        <x-input-label for="gender" :value="__('Gender')" />
        <select id="gender" name="gender" class="erp-select mt-1 w-full">
            <option value="">{{ __('Select gender') }}</option>
            @foreach ($genders as $gender)
                <option value="{{ $gender->value }}" @selected(old('gender', $employee?->gender?->value) === $gender->value)>{{ ucfirst($gender->value) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="date_of_birth" :value="__('Date of birth')" />
        <input
            id="date_of_birth"
            name="date_of_birth"
            type="date"
            class="erp-input mt-1 w-full"
            value="{{ old('date_of_birth', $employee?->date_of_birth?->toDateString()) }}"
        />
    </div>

    <div>
        <x-input-label for="national_id" :value="__('National ID / passport')" />
        <x-text-input id="national_id" name="national_id" class="block mt-1 w-full" :value="old('national_id', $employee?->national_id)" />
    </div>

    @include('admin.employees.partials.form-section-heading', [
        'title' => __('Contact & address'),
        'description' => __('How HR and payroll teams can reach the employee.'),
    ])

    <div class="md:col-span-2">
        <x-input-label for="address" :value="__('Residential address')" />
        <textarea id="address" name="address" rows="2" class="erp-input mt-1 w-full">{{ old('address', $employee?->address) }}</textarea>
    </div>

    <div>
        <x-input-label for="emergency_contact_name" :value="__('Emergency contact name')" />
        <x-text-input id="emergency_contact_name" name="emergency_contact_name" class="block mt-1 w-full" :value="old('emergency_contact_name', $employee?->emergency_contact_name)" />
    </div>

    <div>
        <x-input-label for="emergency_contact_phone" :value="__('Emergency contact phone')" />
        <x-text-input id="emergency_contact_phone" name="emergency_contact_phone" class="block mt-1 w-full" :value="old('emergency_contact_phone', $employee?->emergency_contact_phone)" />
    </div>

    @include('admin.employees.partials.form-section-heading', [
        'title' => __('Next of kin'),
        'description' => __('Used for emergency and benefits correspondence.'),
    ])

    <div>
        <x-input-label for="next_of_kin_name" :value="__('Next of kin name')" />
        <x-text-input id="next_of_kin_name" name="next_of_kin_name" class="block mt-1 w-full" :value="old('next_of_kin_name', $employee?->next_of_kin_name)" />
    </div>

    <div>
        <x-input-label for="next_of_kin_phone" :value="__('Next of kin phone')" />
        <x-text-input id="next_of_kin_phone" name="next_of_kin_phone" class="block mt-1 w-full" :value="old('next_of_kin_phone', $employee?->next_of_kin_phone)" />
    </div>

    <div>
        <x-input-label for="next_of_kin_relationship" :value="__('Next of kin relationship')" />
        <x-text-input id="next_of_kin_relationship" name="next_of_kin_relationship" class="block mt-1 w-full" :value="old('next_of_kin_relationship', $employee?->next_of_kin_relationship)" />
    </div>

    @include('admin.employees.partials.form-section-heading', [
        'title' => __('Statutory & tax'),
        'description' => __('Required for payroll compliance, statutory filings, and payslips.'),
    ])

    <div>
        <x-input-label for="kra_pin" :value="__('KRA PIN')" />
        <x-text-input id="kra_pin" name="kra_pin" class="block mt-1 w-full uppercase" :value="old('kra_pin', $employee?->kra_pin)" />
    </div>

    <div>
        <x-input-label for="nssf_number" :value="__('NSSF number')" />
        <x-text-input id="nssf_number" name="nssf_number" class="block mt-1 w-full" :value="old('nssf_number', $employee?->nssf_number)" />
    </div>

    <div>
        <x-input-label for="nhif_number" :value="__('SHIF / NHIF number')" />
        <x-text-input id="nhif_number" name="nhif_number" class="block mt-1 w-full" :value="old('nhif_number', $employee?->nhif_number)" />
        <p class="mt-1 text-xs text-slate-500">{{ __('Social health insurance membership number.') }}</p>
    </div>

    @include('admin.employees.partials.form-section-heading', [
        'title' => __('Bank & salary payment'),
        'description' => __('Used for salary disbursement and payroll review.'),
    ])

    <div>
        <x-input-label for="bank_name" :value="__('Bank name')" />
        <x-text-input id="bank_name" name="bank_name" class="block mt-1 w-full" :value="old('bank_name', $employee?->bank_name)" />
    </div>

    <div>
        <x-input-label for="bank_account_number" :value="__('Bank account number')" />
        <x-text-input id="bank_account_number" name="bank_account_number" class="block mt-1 w-full" :value="old('bank_account_number', $employee?->bank_account_number)" />
    </div>

    <div>
        <x-input-label for="bank_branch_code" :value="__('Bank branch code')" />
        <x-text-input id="bank_branch_code" name="bank_branch_code" class="block mt-1 w-full" :value="old('bank_branch_code', $employee?->bank_branch_code)" />
    </div>
</div>
