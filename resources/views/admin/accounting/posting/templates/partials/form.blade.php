<x-admin.card>
    <div class="grid gap-3 sm:grid-cols-2">
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600">{{ __('Code') }}</span>
            <input name="code" value="{{ old('code', $template?->code) }}" class="erp-input w-full font-mono" @disabled($template) required>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600">{{ __('Name') }}</span>
            <input name="name" value="{{ old('name', $template?->name) }}" class="erp-input w-full" required>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600">{{ __('Module') }}</span>
            <select name="module" class="erp-input w-full" required>
                @foreach ($modules as $module)
                    <option value="{{ $module->value }}" @selected(old('module', $template?->module?->value) === $module->value)>{{ $module->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600">{{ __('Active') }}</span>
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="erp-checkbox" @checked(old('is_active', $template?->is_active ?? true))>
        </label>
        <label class="block text-sm sm:col-span-2">
            <span class="mb-1 block text-slate-600">{{ __('Description') }}</span>
            <textarea name="description" rows="2" class="erp-input w-full">{{ old('description', $template?->description) }}</textarea>
        </label>
    </div>
</x-admin.card>

<x-admin.card>
    <h2 class="erp-card-title mb-3">{{ __('Template lines') }}</h2>
    <div class="space-y-3" x-data="{ lines: @js($oldLines), add() { this.lines.push({ entry_side: 'debit', account_resolver: 'account_key', amount_source: 'total_amount', line_description: ':description' }) } }">
        <template x-for="(line, index) in lines" :key="index">
            <div class="grid gap-2 rounded border border-erp-border p-3 sm:grid-cols-3 lg:grid-cols-6">
                <label class="text-xs">
                    {{ __('Side') }}
                    <select class="erp-input w-full" :name="`lines[${index}][entry_side]`" x-model="line.entry_side">
                        @foreach ($sides as $side)
                            <option value="{{ $side->value }}">{{ ucfirst($side->value) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs">
                    {{ __('Resolver') }}
                    <select class="erp-input w-full" :name="`lines[${index}][account_resolver]`" x-model="line.account_resolver">
                        @foreach ($resolvers as $resolver)
                            <option value="{{ $resolver->value }}">{{ $resolver->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs" x-show="line.account_resolver === 'fixed_account'">
                    {{ __('GL account') }}
                    <select class="erp-input w-full" :name="`lines[${index}][gl_account_id]`" x-model="line.gl_account_id">
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} — {{ $account->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs" x-show="line.account_resolver === 'account_key'">
                    {{ __('Account key') }}
                    <select class="erp-input w-full" :name="`lines[${index}][account_key]`" x-model="line.account_key">
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($accountKeys as $key)
                            <option value="{{ $key }}">{{ $key }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs" x-show="line.account_resolver === 'context_account'">
                    {{ __('Context field') }}
                    <input class="erp-input w-full" :name="`lines[${index}][context_account_field]`" x-model="line.context_account_field">
                </label>
                <label class="text-xs">
                    {{ __('Amount') }}
                    <select class="erp-input w-full" :name="`lines[${index}][amount_source]`" x-model="line.amount_source">
                        @foreach ($amountSources as $source)
                            <option value="{{ $source->value }}">{{ $source->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs" x-show="line.amount_source === 'context_field'">
                    {{ __('Amount field') }}
                    <input class="erp-input w-full" :name="`lines[${index}][amount_field]`" x-model="line.amount_field">
                </label>
                <label class="text-xs sm:col-span-2">
                    {{ __('Description') }}
                    <input class="erp-input w-full" :name="`lines[${index}][line_description]`" x-model="line.line_description">
                </label>
                <div class="flex items-end">
                    <button type="button" class="erp-btn-secondary text-xs" @click="lines.splice(index, 1)" x-show="lines.length > 1">{{ __('Remove') }}</button>
                </div>
            </div>
        </template>
        <button type="button" class="erp-btn-secondary" @click="add()">{{ __('Add line') }}</button>
    </div>
</x-admin.card>
