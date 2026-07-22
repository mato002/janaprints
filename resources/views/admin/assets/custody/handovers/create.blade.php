<x-admin.modal-form
    :title="__('New handover')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Custody'), 'url' => route('admin.assets.custody.dashboard', ['tab' => 'handovers'])],
        ['label' => __('New')],
    ]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.assets.custody.handovers.store')">
        <div class="erp-form-grid">
            <x-admin.select name="fixed_asset_id" :label="__('Asset')" :required="true" class="md:col-span-2">
                <option value="">{{ __('Select asset…') }}</option>
                @foreach ($assets as $asset)
                    <option value="{{ $asset->id }}" @selected(old('fixed_asset_id') == $asset->id)>
                        {{ $asset->asset_number }} — {{ $asset->asset_name }}
                    </option>
                @endforeach
            </x-admin.select>

            <x-admin.select name="from_employee_id" :label="__('From employee')">
                <option value="">{{ __('None') }}</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('from_employee_id') == $employee->id)>{{ $employee->full_name }}</option>
                @endforeach
            </x-admin.select>

            <x-admin.select name="to_employee_id" :label="__('To employee')">
                <option value="">{{ __('None') }}</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('to_employee_id') == $employee->id)>{{ $employee->full_name }}</option>
                @endforeach
            </x-admin.select>

            <x-admin.select name="from_branch_id" :label="__('From branch')">
                <option value="">{{ __('None') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(old('from_branch_id') == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </x-admin.select>

            <x-admin.select name="to_branch_id" :label="__('To branch')">
                <option value="">{{ __('None') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(old('to_branch_id') == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </x-admin.select>

            <x-admin.input name="handover_date" type="date" :label="__('Handover date')" :required="true" :value="old('handover_date', now()->toDateString())" />

            <x-admin.select name="condition" :label="__('Condition')">
                @foreach ($conditions as $condition)
                    <option value="{{ $condition->value }}" @selected(old('condition') === $condition->value)>{{ $condition->label() }}</option>
                @endforeach
            </x-admin.select>

            <x-admin.textarea name="condition_notes" :label="__('Condition notes')" :value="old('condition_notes')" class="md:col-span-2" rows="2" />
            <x-admin.textarea name="remarks" :label="__('Remarks')" :value="old('remarks')" class="md:col-span-2" rows="2" />
        </div>

        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Create handover') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
