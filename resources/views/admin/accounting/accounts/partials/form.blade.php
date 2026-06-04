@php
    $account = $account ?? null;
    $isEdit = $account !== null;
@endphp

<div class="grid gap-4">
    <div>
        <x-input-label for="gl_account_type_id" :value="__('Account type')" />
        <select name="gl_account_type_id" id="gl_account_type_id" class="erp-input mt-1 w-full" required @disabled($isEdit)>
            @foreach ($types as $type)
                <option value="{{ $type->id }}" data-normal="{{ $type->normal_balance->value }}" @selected(old('gl_account_type_id', $selectedTypeId) == $type->id)>
                    {{ $type->name }} ({{ $type->normal_balance->label() }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="gl_account_group_id" :value="__('Account group')" />
        <select name="gl_account_group_id" id="gl_account_group_id" class="erp-input mt-1 w-full">
            <option value="">{{ __('— None —') }}</option>
            @foreach ($groups as $group)
                <option value="{{ $group->id }}" @selected(old('gl_account_group_id', $account?->gl_account_group_id) == $group->id)>
                    {{ $group->code }} — {{ $group->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="parent_id" :value="__('Parent account')" />
        <select name="parent_id" id="parent_id" class="erp-input mt-1 w-full">
            <option value="">{{ __('— Top level —') }}</option>
            @foreach ($parentAccounts as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $selectedParentId) == $parent->id)>
                    {{ $parent->code }} — {{ $parent->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="branch_id" :value="__('Branch scope')" />
        <select name="branch_id" id="branch_id" class="erp-input mt-1 w-full">
            <option value="">{{ __('Company-wide (all branches)') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id', $account?->branch_id) == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-[11px] text-slate-500">{{ __('Leave empty for accounts shared across all branches.') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="code" :value="__('Account code')" />
            <x-text-input id="code" name="code" class="mt-1 w-full font-mono" :value="old('code', $account?->code)" required />
        </div>
        <div>
            <x-input-label for="name" :value="__('Account name')" />
            <x-text-input id="name" name="name" class="mt-1 w-full" :value="old('name', $account?->name)" required />
        </div>
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea name="description" id="description" rows="2" class="erp-input mt-1 w-full">{{ old('description', $account?->description) }}</textarea>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="normal_balance" :value="__('Normal balance')" />
            <select name="normal_balance" id="normal_balance" class="erp-input mt-1 w-full">
                @foreach ($normalBalances as $balance)
                    <option value="{{ $balance->value }}" @selected(old('normal_balance', $account?->normal_balance?->value) == $balance->value)>
                        {{ $balance->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        @if ($isEdit)
            <div>
                <x-input-label for="status" :value="__('Status')" />
                <select name="status" id="status" class="erp-input mt-1 w-full">
                    @foreach ($statuses as $status)
                        @if ($status !== App\Enums\GlAccountStatus::Locked)
                            <option value="{{ $status->value }}" @selected(old('status', $account->status->value) == $status->value)>{{ $status->label() }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <label class="flex items-center gap-2 text-sm">
            <input type="hidden" name="is_postable" value="0">
            <input type="checkbox" name="is_postable" value="1" @checked(old('is_postable', $account?->is_postable ?? true))>
            {{ __('Postable account') }}
        </label>
        <div>
            <x-input-label for="sort_order" :value="__('Sort order')" />
            <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 w-full" :value="old('sort_order', $account?->sort_order ?? 0)" />
        </div>
    </div>
</div>
