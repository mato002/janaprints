<x-admin.modal-form
    :title="__('Create Write-Off')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Write-Offs'), 'url' => route('admin.assets.finance.write-offs.index')],
        ['label' => __('Create')],
    ]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.assets.finance.write-offs.store')">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="erp-label" for="fixed_asset_id">{{ __('Asset') }}</label>
                <select id="fixed_asset_id" name="fixed_asset_id" class="erp-select w-full" required>
                    <option value="">{{ __('Select asset…') }}</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->asset_number }} — {{ $asset->asset_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="reason">{{ __('Reason') }}</label>
                <select id="reason" name="reason" class="erp-select w-full" required>
                    @foreach ($reasons as $reason)
                        <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="write_off_date">{{ __('Date') }}</label>
                <input type="date" id="write_off_date" name="write_off_date" value="{{ now()->toDateString() }}" class="erp-input w-full" required>
            </div>
        </div>
        <x-admin.form-modal-actions>
            <button type="submit" class="erp-btn-primary">{{ __('Create Write-Off') }}</button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
