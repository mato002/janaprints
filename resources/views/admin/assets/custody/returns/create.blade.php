<x-admin.modal-form
    :title="__('Record Return')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Returns'), 'url' => route('admin.assets.custody.returns.index')],
        ['label' => __('Record Return')],
    ]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.assets.custody.returns.store')">
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
                <label class="erp-label" for="return_date">{{ __('Return Date') }}</label>
                <input type="date" id="return_date" name="return_date" value="{{ now()->toDateString() }}" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label" for="condition">{{ __('Condition') }}</label>
                <select id="condition" name="condition" class="erp-select w-full" required>
                    @foreach ($conditions as $condition)
                        <option value="{{ $condition->value }}">{{ $condition->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="erp-label" for="notes">{{ __('Notes') }}</label>
                <textarea id="notes" name="notes" class="erp-input w-full" rows="3"></textarea>
            </div>
        </div>
        <x-admin.form-modal-actions>
            <button type="submit" class="erp-btn-primary">{{ __('Record Return') }}</button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
