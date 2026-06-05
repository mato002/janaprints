@php
    $selectedModules = old('modules', $delegation->modules ?? []);
    $selectedApprovalTypes = old('approval_types', $delegation->approval_types ?? []);
@endphp

<div class="grid gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Delegation Parties') }}</h3>

        <div>
            <label class="erp-label" for="delegator_user_id">{{ __('Delegator') }}</label>
            <select id="delegator_user_id" name="delegator_user_id" class="erp-input w-full" required>
                <option value="">{{ __('Select delegator') }}</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((int) old('delegator_user_id', $delegation->delegator_user_id ?? 0) === $user->id)>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
            @error('delegator_user_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="erp-label" for="delegate_user_id">{{ __('Delegate') }}</label>
            <select id="delegate_user_id" name="delegate_user_id" class="erp-input w-full" required>
                <option value="">{{ __('Select delegate') }}</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((int) old('delegate_user_id', $delegation->delegate_user_id ?? 0) === $user->id)>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="erp-label" for="reason">{{ __('Reason') }}</label>
            <select id="reason" name="reason" class="erp-input w-full" required>
                @foreach ($reasons as $value => $label)
                    <option value="{{ $value }}" @selected(old('reason', $delegation->reason?->value ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Coverage & Period') }}</h3>

        <div>
            <label class="erp-label">{{ __('Modules') }}</label>
            <p class="mb-2 text-xs text-slate-500">{{ __('Leave unchecked to delegate all modules.') }}</p>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ($modules as $value => $label)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="modules[]" value="{{ $value }}" class="rounded border-erp-border text-erp-accent" @checked(in_array($value, $selectedModules, true))>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <label class="erp-label">{{ __('Approval Types') }}</label>
            <p class="mb-2 text-xs text-slate-500">{{ __('Leave unchecked to delegate all approval types.') }}</p>
            <div class="grid gap-2">
                @foreach ($approvalTypes as $value => $label)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="approval_types[]" value="{{ $value }}" class="rounded border-erp-border text-erp-accent" @checked(in_array($value, $selectedApprovalTypes, true))>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="erp-label" for="start_date">{{ __('Start Date') }}</label>
                <input type="date" id="start_date" name="start_date" value="{{ old('start_date', optional($delegation->start_date)->format('Y-m-d')) }}" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label" for="end_date">{{ __('End Date') }}</label>
                <input type="date" id="end_date" name="end_date" value="{{ old('end_date', optional($delegation->end_date)->format('Y-m-d')) }}" class="erp-input w-full" required>
            </div>
        </div>

        <div>
            <label class="erp-label" for="notes">{{ __('Notes') }}</label>
            <textarea id="notes" name="notes" rows="3" class="erp-input w-full">{{ old('notes', $delegation->notes ?? '') }}</textarea>
        </div>
    </div>
</div>
